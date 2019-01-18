<?php

namespace Studiobit\Statement\Controller;
use Bitrix\Main\Loader;
use Bitrix\Crm;
use Studiobit\Base\View;
use Studiobit\Statement;
use Studiobit\Agreement;
use Studiobit\Matrix;
use Studiobit\BiGroup;
use Studiobit\Base;
use PhpOffice\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Writer\PDF;

/**
 * Контроллер заявления. Генерация документов по шаблону
 */

class Document extends Prototype
{

	private $HLEntity;
	private $iblockId;
	protected $dataStatement;

	/**
	 * Запуск БП на генерации документа
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function createAction() {
		Loader::includeModule('iblock');
		Loader::includeModule('bizproc');

		$this->view = new View\Json();
		$this->returnAsIs = true;

		$templatesId = $this->getParam("templateId");
		$entityId = $this->getParam("entityId");

		$return = [
			'statement' => $entityId,
			'template' => $templatesId,
			'status' => 'ok'
		];

		// данные по заявлению
		$dataStatement = $this->_getStatement($entityId);

		// замена кодом пользовательских полей на человеко читабельные переменные
		$dataStatement = $this->replaceVariable($dataStatement);

		// вытянуть информацию по шаблону
		$arTemplates = $this->_getTemplate($dataStatement, $templatesId);

		$arVariables = [
			'StatementId' => $entityId,
			'DocumentDateCreate' => date('d.m.Y'),
			'DocumentCreateFormat' => "29 июня", // TODO вывод даты в нужном формате
			'DocumentCreateYear' => date('Y')
		];

		// данные по клиенту
		$dataClient = $this->_getClient($this->dataStatement["UF_CLIENT"]);

		$arVariables = array_merge($arVariables, $dataStatement, $dataClient);

		#p($arVariables);
		#exit();

		try {
			// файл-шаблон
			$fileTemplates = $_SERVER['DOCUMENT_ROOT'] . $arTemplates["PATH"];

			// имя нового файла
			$templateName = \CUtil::translit($arTemplates["NAME"], LANGUAGE_ID, array(
				"max_len" => 1024,
				"safe_chars" => ".",
				"replace_space" => '_',
				"change_case" => false,
			));
			$fileName = $entityId.'_'.$templatesId.'_'.$templateName;

			$WordDocument = new PhpWord\Template($fileTemplates);

			$cloneRowTable = [];
			foreach ($arVariables as $PropCode => $Value) {
				if (!is_array($Value)) {
					$Value = str_replace("&nbsp;", " ", $Value);
					$WordDocument->setValue($PropCode, htmlspecialchars_decode($Value));
				} else {
					$data = $arSubVariables = array();
					$arSubVariables = array();
					foreach ($Value as $key => $values) {
						if(is_array($values)) {
							foreach ($values as $fieldName => $arItem) {
								if (!is_array($arItem)) {
									if (!isset($data[$fieldName]))
										$data[$fieldName] = array();

									$data[$fieldName][$key] = $arItem;
								} else {
									$first = reset($arItem);
									foreach ($first as $fName => $val) {
										if (!isset($data[$fName]))
											$data[$fName] = array();

										$data[$fName][$key] = '${' . $fName . '#' . $key . '}';
									}

									$arSubVariables[$key] = array();
									foreach ($arItem as $i => $item) {
										$arSubVariables[$key][$i] = array();
										foreach ($item as $fName => $val) {
											$arSubVariables[$key][$i]['${' . $fName . '#' . $key . '}'] = $val;
										}
									}
								}
							}
						}
						else{
							$data[($key+1)] = '${' . $PropCode . '#' . ($key+1) . '}';
							$arSubVariables['${' . $PropCode . '#' . ($key+1) . '}'] = $values;
						}
					}

					if (!empty($data)) {
						try {
							$partRows = explode(".", $PropCode);
							$tableCode = $partRows[0];
							if(!in_array($tableCode, $cloneRowTable)) {
								$WordDocument->cloneRow($PropCode, count($data));

								$cloneRowTable[] = $tableCode;
							}

							if (!empty($arSubVariables)) {
								foreach ($arSubVariables as $rows=>$rowValue) {
									$WordDocument->setValue($rows, $rowValue);
								}
							}
						} catch (\Exception $e) {
							p2f($e->getMessage());
						}

					}
				}
			}

			$fileNewName = "/upload/statement/docx/".$fileName.".docx";
			$NewDocumentPath = $_SERVER['DOCUMENT_ROOT'] . $fileNewName;

			$WordDocument->saveAs($NewDocumentPath);

			$return['path_docx'] = $fileNewName;
			$return['path_pdf'] = $fileNewName;

			// конвертация docx в pdf
			$objConverter = new Base\Integration\Converter();

			$converter = $objConverter->create("unoconv");
			$download = $converter->query($fileNewName, $fileName.".docx")->download();
			if(strlen($download) > 0){
				$return['path_pdf'] = $download;
			}

			$preview = $converter->query($download, $fileName.".pdf", "pdf", "jpg")->download('jpg');
			if(strlen($preview) > 0){
				$return['path_preview'] = str_replace($_SERVER["DOCUMENT_ROOT"], "", $preview);
			}

		} catch (\Exception $e) {
			p2f("ERROR: " . $e->getMessage());
		}

		$objectId = 0;

		// поиск файла на диске, если он есть то будет загружена новая версия
		if(strlen($return['path_docx']) > 0) {
			$objFile = new Agreement\Entity\FilesTable();
			$params = [
				'filter' => [
					'UF_ENTITY_ID' => $entityId,
					'UF_ENTITY_TYPE' => 'STATEMENT',
					'UF_TEMPLATE_ID' => $templatesId
				],
				'select' => [
					'ID',
					'UF_FILE_DISK'
				]
			];
			$searchFile = $objFile->getList($params);
			if ($arDiskFile = $searchFile->fetch()) {
				$objectId = $arDiskFile['UF_FILE_DISK'];
			}

			// загрузка документов на диск
			$arParams = array(
				'AGREE_ID' => $entityId,
				'PATH_FILE' => $return['path_docx'],
				'FILE_NAME' => $fileName,
				'OBJECT_ID' => $objectId,
				'TEMPLATE_ID' => $templatesId,
			);
			$return['bizproc_id'] = \CBPDocument::StartWorkflow(
				37, // TODO замена магического числа на значение из опции модуля
				Statement\BizProc\StatementDocument::getComplexDocumentId($entityId),
				$arParams,
				$arErrorsTmp
			);
		}

		return $return;
	}

	/**
	 * Замена кодом пользовательских полей на человеко читабельные переменные
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	private function replaceVariable($data){

		foreach($data as $key=>$value){
			$keyFormat = ucwords(strtolower(str_replace(array("UF_", "_"), array("", " "), $key)));
			$keyFormat = str_replace(" ", "", $keyFormat);

			$data[$keyFormat] = $value;

			unset($data[$key]);
		}

		return $data;
	}

	/**
	 * данные по договору
	 *
	 * @param $id
	 *
	 * @return array|false
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function _getStatement($id){
		$this->HLEntity = new Statement\Entity\StatementTable();
		$dataStatement = $this->HLEntity->getList(['filter'=>['ID'=>$id], 'select'=>['*']])->fetch();

		$this->dataStatement = $dataStatement;

		$this->iblockId = Statement\Entity\StatementTable::getEntityID();

		// свойства основного блока
		$propertyBase = $this->getListProperty('HLBLOCK_' . $this->iblockId);

		$propertyDataBase = $this->prepareProperties($propertyBase, $dataStatement, true, false, 'BASE[#FIELD_NAME#]');

		$propertyDataParts = [];

		$dataStatement = array_merge($propertyDataBase, $propertyDataParts);

		return $dataStatement;
	}

	/**
	 * Данные по клиенту
	 *
	 * @param $clientId
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function _getClient($clientId){
		$result = [];

		if(Loader::includeModule("crm")) {
			if(substr_count($clientId, "C_") > 0) {
				$obContact = new Crm\ContactTable();

				$params = [
					'filter' => [
						'ID' => substr_count($clientId, "C_"),
					],
					'select' => ['*']
				];
				$rsContact = $obContact->getList($params);
				if ($arItem = $rsContact->fetch()) {
					foreach ($arItem as $field => $value) {
						if ($value instanceof \Bitrix\Main\Type\DateTime) {
							$arItem[$field] = $value->format('d.m.Y H:i:s');
						} else if ($value instanceof \Bitrix\Main\Type\Date) {
							$arItem[$field] = $value->format('d.m.Y');
						}
					}
				}

				$arItem = $this->replaceVariable($arItem);
				foreach ($arItem as $fields => $value) {
					$result["Client." . $fields] = $value;
				}
			}
			else if(substr_count($clientId, "CO_") > 0) {
				$obCompany = new Crm\CompanyTable();

				$params = [
					'filter' => [
						'ID' => substr_count($clientId, "CO_"),
					],
					'select' => ['*']
				];
				$rsCompany = $obCompany->getList($params);
				if ($arItem = $rsCompany->fetch()) {
					foreach ($arItem as $field => $value) {
						if ($value instanceof \Bitrix\Main\Type\DateTime) {
							$arItem[$field] = $value->format('d.m.Y H:i:s');
						} else if ($value instanceof \Bitrix\Main\Type\Date) {
							$arItem[$field] = $value->format('d.m.Y');
						}
					}
				}

				$arItem = $this->replaceVariable($arItem);
				foreach ($arItem as $fields => $value) {
					$result["Client." . $fields] = $value;
				}
			}
		}

		return $result;
	}

	protected function prepareProperties($properties, $arItem, $bShow = false, $bVarsFromForm = false, $fieldNameTemplate = '')
	{
		global $APPLICATION;

		$return = array();
		foreach ($properties as $FIELD_NAME => &$arUserField) {
			if (!isset($arUserField['ENTITY_VALUE_ID'])) {
				$arUserField['ENTITY_VALUE_ID'] = intval($arItem['ID']);
			}

			$viewMode = $bShow;

			$userTypeID = $arUserField['USER_TYPE']['USER_TYPE_ID'];

			if (isset($arItem[$FIELD_NAME])) {
				if(strlen($arUserField['VALUE']) == 0) {
					$arUserField['VALUE'] = $arItem[$FIELD_NAME];

					if (isset($arUserField['SETTINGS']) && isset($arUserField['SETTINGS']['DEFAULT_VALUE'])) {
						if (!is_array($arUserField['SETTINGS']['DEFAULT_VALUE'])) {
							$arUserField['SETTINGS']['DEFAULT_VALUE'] = $arItem[$FIELD_NAME];
						} else if (isset($arUserField['SETTINGS']['DEFAULT_VALUE']['VALUE'])) {
							$arUserField['SETTINGS']['DEFAULT_VALUE']['VALUE'] = $arItem[$FIELD_NAME];
						}
					}
				}
			}

			if ($userTypeID === 'employee') {
				if ($viewMode) {
					if (!is_array($arUserField['VALUE']))
						$arUserField['VALUE'] = array($arUserField['VALUE']);
					ob_start();
					foreach ($arUserField['VALUE'] as $k) {
						$APPLICATION->IncludeComponent('bitrix:main.user.link',
							'',
							array(
								'ID' => $k,
								'HTML_ID' => 'crm_' . $FIELD_NAME,
								'USE_THUMBNAIL_LIST' => 'Y',
								'SHOW_YEAR' => 'M',
								'CACHE_TYPE' => 'A',
								'CACHE_TIME' => '3600',
								'NAME_TEMPLATE' => '',//$arParams['NAME_TEMPLATE'],
								'SHOW_LOGIN' => 'Y',
							),
							false,
							array('HIDE_ICONS' => 'Y', 'ACTIVE_COMPONENT' => 'Y')
						);
					}
					$sVal = ob_get_contents();
					ob_end_clean();
				} else {
					$val = !$bVarsFromForm ? $arUserField['VALUE'] : (isset($GLOBALS[$FIELD_NAME]) ? $GLOBALS[$FIELD_NAME] : '');
					$val_string = '';
					if (is_array($val))
						foreach ($val as $_val) {
							if (empty($_val))
								continue;
							$rsUser = \CUser::GetByID($_val);
							$val_string .= \CUser::FormatName(\CSite::GetNameFormat(false) . ' [#ID#], ', $rsUser->Fetch(), true, false);
						}
					else if (!empty($val)) {
						$rsUser = \CUser::GetByID($val);
						$val_string .= \CUser::FormatName(\CSite::GetNameFormat(false) . ' [#ID#], ', $rsUser->Fetch(), true, false);
					}
					ob_start();
					$GLOBALS['APPLICATION']->IncludeComponent('bitrix:intranet.user.selector',
						'',
						array(
							'INPUT_NAME' => ($fieldNameTemplate !== '') ? str_replace('#FIELD_NAME#', $FIELD_NAME, $fieldNameTemplate) : $FIELD_NAME,
							'INPUT_VALUE' => $val,
							'INPUT_VALUE_STRING' => $val_string,
							'MULTIPLE' => $arUserField['MULTIPLE'],
						),
						false,
						array('HIDE_ICONS' => 'Y')
					);
					$sVal = ob_get_contents();
					ob_end_clean();
				}
			} else {
				$fileViewer = $fileUrlTemplate = null;
				if ($viewMode && $userTypeID === 'disk_file' && ($fileViewer || $fileUrlTemplate !== '')) {
					$fileIDs = isset($arUserField['VALUE'])
						? (is_array($arUserField['VALUE'])
							? $arUserField['VALUE']
							: array($arUserField['VALUE']))
						: array();

					$fieldUrlTemplate = "";

					ob_start();
					\CCrmViewHelper::RenderFiles($fileIDs, $fieldUrlTemplate, 100, 100);
					$sVal = ob_get_contents();
					ob_end_clean();
				} else {
					$fieldUrlTemplate = "";

					if ($fieldNameTemplate !== '') {
						$arUserField['FIELD_NAME'] = str_replace('#FIELD_NAME#', $FIELD_NAME, $fieldNameTemplate);
					}

					$additionalParams = array();

					ob_start();

					$APPLICATION->IncludeComponent(
						'bitrix:system.field.view',
						$userTypeID,
						array(
							'arUserField' => $arUserField,
							'bVarsFromForm' => $bVarsFromForm,
							'form_name' => 'form_CRM_AGREEMENT_LIST_V12',
							'FILE_MAX_HEIGHT' => 100,
							'FILE_MAX_WIDTH' => 100,
							'FILE_SHOW_POPUP' => true,
							'SHOW_FILE_PATH' => false,
							'SHOW_NO_VALUE' => true,
							'FILE_URL_TEMPLATE' => $fieldUrlTemplate,
						) + $additionalParams,
						false,
						array('HIDE_ICONS' => 'Y')
					);
					$sVal = ob_get_contents();
					ob_end_clean();
				}
			}

			$sVal = trim(strip_tags($sVal));
			$arExp = explode("BX.tooltip", $sVal);
			$sVal = trim($arExp[0]);

			$return[$FIELD_NAME] = $sVal;
		}

		return $return;
	}

	private function getListProperty($entity, $id = 0)
	{
		global $USER_FIELD_MANAGER;

		$arReturn = $USER_FIELD_MANAGER->GetUserFields($entity, $id, LANGUAGE_ID);

		return $arReturn;
	}

	private function getPropertyParts($typeId)
	{
		$arReturn = array();
		$subHLId = (int)$this->HLEntity->getServiceHLIdByServiceId((int)$typeId);

		if ($subHLId) {
			$arReturn = $this->getListProperty('HLBLOCK_' . $subHLId);
		}

		return $arReturn;
	}

	/**
	 * Данные по шаблону
	 *
	 * @param $agreeInfo
	 * @param $id
	 *
	 * @return array
	 */
	private function _getTemplate($agreeInfo, $id){
		$objTemplate = new Statement\Entity\Templates($agreeInfo);

		// вытянуть инофмацию по шаблону
		$arTemplates = $objTemplate->getRow($id);

		// путь к шаблону документа
		$arTemplates['PATH'] = \CFile::GetPath($arTemplates['PROPERTY_TEMPLATE_VALUE']);

		return $arTemplates;
	}

    public function hasAgreeAction(){
        $this->view = new View\Json();
        $this->returnAsIs = true;

        $clientId = $this->getParam("client");

        $return = [
            'method' => 'ok',
            'result' => false
        ];

        if(Loader::includeModule('studiobit.agreement')){
            $rs = Agreement\Entity\AgreeTable::getList([
                'filter' => [
                    'UF_TYPE' => 1,
                    'UF_CLIENT' => $clientId
                ],
                'limit' => 1,
                'select' => ['ID']
            ]);

            if($ar = $rs->fetch()){
                $return['result'] = true;
            }
        }

        return $return;
    }
}
?>