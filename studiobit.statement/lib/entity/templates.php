<?

namespace Studiobit\Statement\Entity;

use Bitrix\Main\Error;
use Studiobit\Base as Base;
use Studiobit\Agreement;
use Bitrix\Main\Entity;
use Bitrix\Main\DB;
use Bitrix\Iblock;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
\Bitrix\Main\Loader::includeModule('iblock');
/**
 * Шаблоны заявлений
 */
class Templates
{
	protected $iblock_id;
	private $data;
	private $entity;

    public function __construct($dataAgreement)
    {
    	$this->data = $dataAgreement;
    	$this->entity = new \CIBlockElement();
    }

	/**
	 * Инфоблок с шаблонами договоров
	 *
	 * @return bool|int
	 */
	public function getIBlockID(){
		if(!$this->iblock_id){
			$rsIBlock = $rsIBlock = \CIBlock::GetList([], ['CODE' => 'statement_templates']);
			if($arIBlock = $rsIBlock->Fetch()){
				$this->iblock_id = $arIBlock['ID'];
			}
		}

		return $this->iblock_id;
	}

	/**
	 * Список статусов заявления
	 *
	 * @return array
	 */
	private function getStages(){
		$result = [];

		$rsStage = Base\Tools::getUFPropEnum("UF_STAGE_ID", ['ENTITY_ID' =>'HLBLOCK_'.Agreement\Entity\AgreeTable::getEntityID()]);

		foreach ($rsStage as $arStages) {
			$result[$arStages['ID']] = $arStages['XML_ID'];
		}

		return $result;
	}

	/**
	 * Код типа по ID
	 * @param $id
	 *
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getCodeType($id){

		// найти XML_ID по ID свойствао
		$rsView = Base\Tools::getUFPropEnum("UF_VIEW", ['ENTITY_ID' =>'HLBLOCK_'.Agreement\Entity\AgreeTable::getEntityID()]);

		$xmlView = "";
		foreach ($rsView as $arView) {
			if($id == $arView['ID']) {
				$xmlView = $arView['XML_ID'];
			}
		}

		$return = Base\Tools::getIdPropertyEnumByXml(
			$this->getIBlockID(),
			'TYPE',
			$xmlView
		);

		return $return;
	}

	/**
	 * Список шаблонов, доступных на текущей стадии договора
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function listDocs(){
		$result = [];

		// список статусов
		$objStage = $this->getStages();

		$arParams = [
			'select' => [
				'ID',
				'NAME'
			],
			'filter' => [
				'PROPERTY_TYPE' => $this->getCodeType($this->data['UF_VIEW']),
				[
					'LOGIC' => 'OR',
					[
						'PROPERTY_STAGE' => ''
					],
					[
						'PROPERTY_STAGE' => $objStage[$this->data['UF_STAGE_ID']]?$objStage[$this->data['UF_STAGE_ID']]:"0"
					]
				]
			]
		];

		$result = $this->searchTemplate($arParams);
		return $result;
	}

    protected function searchTemplate(array $parameters = [])
    {
    	$result = [];

        if(!isset($parameters['filter']))
            $parameters['filter'] = [];

        $parameters['filter']['IBLOCK_ID'] = $this->getIBlockID();
        $rs = $this->entity->getList($parameters['order']?$parameters['order']:[], $parameters['filter'], false, false, $parameters['select']);

        while($arItem = $rs->fetch()){
	        $result[] = $arItem;
        }

        return $result;
    }

	/**
	 * Данные по шаблону
	 *
	 * @param $id
	 *
	 * @return array
	 */
    public function getRow($id)
    {
    	$result = [];

    	$arFilter = ['ID'=>$id, 'IBLOCK_ID'=>$this->getIBlockID()];
	    $arSelect = ['ID', 'NAME', 'PROPERTY_TEMPLATE'];

        $rs = $this->entity->getList([], $arFilter, false, false, $arSelect);

        while($arItem = $rs->fetch()){
	        $result = $arItem;
        }

        return $result;
    }


}