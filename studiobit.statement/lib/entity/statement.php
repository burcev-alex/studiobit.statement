<?php
namespace Studiobit\Statement\Entity;

use \Bitrix\Crm\DealTable;
use Bitrix\Main\Loader;
use Studiobit\Base;
use Studiobit\Matrix\Entity\Object;

\Studiobit\Base\Entity\HighloadBlockTable::compileBaseClass('Statement');

/**
 сущность для храненения заявлений
 * @method int getEntityID()
 * @mixin \Bitrix\Highloadblock\DataManager
 */

class StatementTable extends \StatementBaseTable
{
    /**
     * @param \Bitrix\Main\Entity\Base $entity
     *
     * @throws \Bitrix\Main\LoaderException
     */
    public static function prepareEntity(&$entity)
    {
        global $USER_FIELD_MANAGER;
        $userFields = $USER_FIELD_MANAGER->GetUserFields('HLBLOCK_'.static::getEntityID());
        foreach ($userFields as $field) {
            if ($field['USER_TYPE_ID'] === 'enumeration') {
                $name = 'ENUM_' . substr($field['FIELD_NAME'], 3);
                $entity->addField(new \Bitrix\Main\Entity\ReferenceField($name,
                    '\Studiobit\Base\Entity\UserFieldEnumTable',
                    [
                        '=this.' . $field['FIELD_NAME'] => 'ref.ID',
                        'ref.USER_FIELD_ID' => new \Bitrix\Main\DB\SqlExpression('?i', $field['ID']),
                    ],
                    ['join_type' => 'left']
                ));
            }
        }
    }

    /**
	 * Список согласованных заявлений отфильтрованных по клиенту или по сделке
	 *
	 * @param $clientId
	 * @param $dealId
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getAgreedByClient($clientId, $dealId){
		$result = [];

		$result[] = 'нет';

		if(IntVal($clientId) > 0){

		}
		else {
			if (IntVal($dealId) > 0) {
				$arDeal = DealTable::getList([
					'select' => ['ID', 'COMPANY_ID', 'CONTACT_ID'],
					'filter' => ['=ID' => $dealId],
				])->fetch();

				if (IntVal($arDeal['CONTACT_ID']) > 0) {
					$clientId = "C_".IntVal($arDeal['CONTACT_ID']);
				}
				else if (IntVal($arDeal['COMPANY_ID']) > 0) {
					$clientId = "CO_".IntVal($arDeal['COMPANY_ID']);
				}
			}
		}

		// данные по договору
		$res = $this->getList([
			'select' => ['ID', 'UF_NUMBER', 'UF_VIEW', 'UF_OBJECT_ID'],
			'filter' => [
				'UF_CLIENT' => $clientId,
				'UF_STATUS' => Base\Tools::getIDInUFPropEnumByXml(
					'UF_STATUS',
					'STATEMENT_WON',
					0,
					'HLBLOCK_' . self::getEntityID()
				)
			],
		]);
		while($item = $res->fetch()){
            $title = '';
            if($item['UF_OBJECT_ID'] > 0){
                if(Loader::includeModule('studiobit.matrix')){
                    if($object = Object::getObjectByID($item['UF_OBJECT_ID'])){
                        $title = ' - '.$object->getFullName();
                    }
                }
            }
			$result[$item['ID']] = ($item['UF_NUMBER']?$item['UF_NUMBER']:"-").' ('.Base\Tools::getValueInUFPropEnumID("UF_VIEW", $item['UF_VIEW']).')'.$title;
		}

		return $result;

	}
}
?>