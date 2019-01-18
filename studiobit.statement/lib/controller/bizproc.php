<?php

namespace Studiobit\Statement\Controller;
use Bitrix\Main\Loader;
use Bitrix\Main;
use Studiobit\Base\View;
use Studiobit\Statement;
use Studiobit\Base;

/**
 * Контроллер договора. Управление бизнес-процессом
 */

class Bizproc extends Prototype
{
	/**
	 * Запуск БП на согласование договора
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function startAction() {
		Loader::includeModule('bizproc');

		$this->view = new View\Json();
		$this->returnAsIs = true;

		$statementId = $this->getParam("entity_id");
		$responsibleId = $this->getParam("responsible");

		$objEntity = new Statement\Entity\StatementTable();

		$return = [
			'statementId' => $statementId,
			'status' => 'ok'
		];

		// данные по договору
		$return["ENTITY"] = $objEntity->getList([
			'select' => ['*'],
			'filter' => ['ID' => $statementId],
		])->fetch();

		// если есть бизнес-процесс, тогда запускаем его
		if(Base\Tools::getIDInUFPropEnumByXml("UF_REQUIRED_DC", "IS_REQUIRED_DC_NO") == $return["ENTITY"]["UF_REQUIRED_DC"]) {
			// короткий процесс
			$bizprocId = (int)Main\Config\Option::get('studiobit.statement', 'business_approval_process_short');
		}
		else if(Base\Tools::getIDInUFPropEnumByXml("UF_REQUIRED_DC", "IS_REQUIRED_DC_YES") == $return["ENTITY"]["UF_REQUIRED_DC"]) {
			// длинный БП , с созданием ДС
			$bizprocId = (int)Main\Config\Option::get('studiobit.statement', 'business_approval_process_long');
		}

		if(IntVal($bizprocId) > 0){
			$arParams = array(
				'RESPONS_STAGE' => $responsibleId
			);
			$arErrorsTmp = array();
			$return['bizproc_id'] = \CBPDocument::StartWorkflow(
				$bizprocId,
				Statement\BizProc\StatementDocument::getComplexDocumentId($return["ENTITY"]["ID"]),
				$arParams,
				$arErrorsTmp
			);

			// при успешном запуске, нужно изменить статус договора
			$arFields = [
				'UF_WORKFLOW_ID' => $return['bizproc_id'],
				'UF_STAGE_ID' => Base\Tools::getIDInUFPropEnumByXml("UF_STAGE_ID", "STATEMENT_HEAD"),
				'UF_STATUS' => Base\Tools::getIDInUFPropEnumByXml("UF_STATUS", "STATEMENT_PROCESS")
			];
			$return['FIELDS'] = $arFields;
			$resSave = $objEntity->update($statementId, $arFields);

			$return['error'] = $resSave->getErrorMessages();
		}

		return $return;
	}

	/**
	 * Остановка процесса согласования заявления
	 *
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function stopAction() {
		Loader::includeModule('bizproc');

		$this->view = new View\Json();
		$this->returnAsIs = true;

		$statementId = $this->getParam("entity_id");

		$objEntity = new Statement\Entity\StatementTable();

		$return = [
			'statementId' => $statementId,
			'status' => 'ok'
		];

		// данные по договору
		$return["ENTITY"] = $objEntity->getList([
			'select' => ['*'],
			'filter' => ['ID' => $statementId],
		])->fetch();

		$bizprocId = $return["ENTITY"]["UF_WORKFLOW_ID"];

		if(strlen($bizprocId) > 0) {
			// убить процесс
			\CBPDocument::killWorkflow($bizprocId);

			// делаем сброс до статуса - Заявка на договор
			$arFields = [
				'UF_STAGE_ID' => Base\Tools::getIDInUFPropEnumByXml("UF_STAGE_ID", "STATEMENT_HUNTER"),
				'UF_STATUS' => Base\Tools::getIDInUFPropEnumByXml("UF_STATUS", "STATEMENT_DRAFT")
			];
			$return['FIELDS'] = $arFields;
			$resSave = $objEntity->update($statementId, $arFields);

			$return['error'] = $resSave->getErrorMessages();

			// записать в историю остановку процесса согласования
			$crmEvent = new \CCrmEvent();
			$crmEvent->Add([
				'ENTITY_TYPE'=> "STATEMENT",
				'ENTITY_ID' => $statementId,
				'EVENT_TYPE' => 1,
				'EVENT_NAME' => "Бизнес-процесс",
				'EVENT_TEXT_1' => "Остановка процесса согласования",
				'EVENT_TEXT_2' => "",
				'USER_ID' => (int)$GLOBALS['USER']->GetID()
			]);

            // закрыть все задачи по БП
            $arFilter = [
                'UF_CRM_TASK' => \COption::GetOptionString('studiobit.bigroup', 'task_sys_user'),
                '!STATUS' => array(
                    \CTasks::STATE_COMPLETED,
                    \CTasks::STATE_SUPPOSEDLY_COMPLETED
                ),
                'ZOMBIE' => 'N'
            ];

            $dbResultTask = \CTasks::GetList(
                ["ID" => "ASC"],
                $arFilter,
                ['TITLE', 'ID', 'RESPONSIBLE_ID', 'CREATED_BY']
            );
            while($arTask = $dbResultTask->Fetch()) {
                if(strpos($arTask['TITLE'], 'Заявление №'.$statementId) !== false) {
                    // complete
                    $oTask = \CTaskItem::getInstance(IntVal($arTask["ID"]), $arTask["CREATED_BY"]);
                    try {
                        $oTask->complete();
                    } catch (\TasksException $e) {
                        AddMessage2Log("error close task #" . $arTask["ID"] . ": " . $e->getMessage());
                    }
                }
            }
		}

		return $return;
	}

	public function infoAction() {
		Loader::includeModule('bizproc');

		$this->view = new View\Json();
		$this->returnAsIs = true;

		$statementId = $this->getParam("entity_id");

		$objEntity = new Statement\Entity\StatementTable();

		$return = [
			'statementId' => $statementId,
			'status' => 'ok'
		];

		if(IntVal($statementId) > 0) {
			// данные по договору
			$return["ENTITY"] = $objEntity->getList([
				'select' => ['*'],
				'filter' => ['ID' => $statementId],
			])->fetch();

			if (IntVal($return["ENTITY"]["UF_WORKFLOW_ID"]) > 0) {
				$trackingService = new \CBPTrackingService();

				$arReport = $trackingService->LoadReport($return["ENTITY"]["UF_WORKFLOW_ID"]);
				foreach ($arReport as $value) {
					if (substr_count($value["ACTION_NOTE"], "|") > 0) {
						// разбираем сообщение и вытягиваем автора
						$arrMessage = explode('|', $value["ACTION_NOTE"]);

						$return["ENTITY"]["ACTION_NOTE"] = $value["~ACTION_NOTE"] = trim($arrMessage[1]);
						$userId = IntVal(str_replace("user_", "", trim($arrMessage[0])));

						if(IntVal($userId) == 0){
							$userId = 1;
						}
						$return["ENTITY"]["AUTHOR"] = $this->getUserInfo($userId)." [".$userId."]";
						$return["ENTITY"]["AUTHOR_ID"] = $userId;
					}
				}
			}
		}
		else{
			$return['status'] = 'error';
		}

		return $return;
	}

	/**
	 * Данные пользователя
	 * @param $id
	 *
	 * @return mixed|string
	 */
	private function getUserInfo($id){
		$data = '';

		$cache = new Base\Cache("user_".$id, __CLASS__, 36000);
		if ($cache->start())
		{
			$rsUser = \CUser::GetList($by="ID", $order="ASC", ['ID' => $id], ['FIELDS'=>['NAME', 'LAST_NAME', 'ID']]);
			if ($arUser = $rsUser->Fetch())
			{
				$data = $arUser["LAST_NAME"]." ".$arUser["NAME"];
			}
			$cache->end($data);
		}
		else
		{
			$data = $cache->getVars();
		}

		return $data;
	}
}
?>