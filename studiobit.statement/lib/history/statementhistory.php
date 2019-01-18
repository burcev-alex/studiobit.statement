<?
namespace Studiobit\Statement\History;

use Studiobit\Base;
use Studiobit\Statement;
use Studiobit\Base\History;
use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\DB;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * История изменения пользовательских полей сущности "Заявления"
 */
class StatementHistory extends History\UserfieldsHistory
{
    public static function includeModules()
    {
        \Bitrix\Main\Loader::includeModule('highloadblock');
    }

    public function setEntity($entityType, $entity)
    {
        if(is_object($entity))
        {
            $cache['entities'][$entityType] = $entity;
        }
    }
    
    public function getEntity($entityType)
    {
        if(!isset($cache['entities']))
            $cache['entities'] = [];

        if(!isset($cache['entities'][$entityType]))
        {
	        $cache['entities'][$entityType] = new Statement\Entity\StatementTable();
        }
        
        return $cache['entities'][$entityType];
    }

    /**
     * метод который нужно вызвать до изменения сущности
     * @param $fields - изменяемые поля
     * @param string $entityType - тип сущности
     */
    public function before($fields, $entityType = 'HLBLOCK_29')
    {
        $this->entityType = $entityType;

        $arSelect = [];
        foreach($fields as $name => $value){
            if(strpos($name, 'UF_') !== false){
                $arSelect[] = $name;
            }
        }

        if(!empty($arSelect))
        {
            $entity = $this->getEntity($this->entityType);
            if($entity)
            {
                $arSelect[] = 'ID';
                $rsItem = $entity->getList(['filter'=>['ID' => $fields['ID']], 'select' => $arSelect]);
                if ($arItem = $rsItem->Fetch())
                {
                    $this->arBeforeItem = $arItem;
                }
            }
        }
    }

    /**
     *Метод который нужно вызвать после изменения сущности
     */
    public function after()
    {
        if(!empty($this->arBeforeItem) && !empty($this->entityType))
        {
            $entity = $this->getEntity($this->entityType);
            if($entity)
            {
                $rsItem = $entity->getList(['filter'=>['ID' => $this->arBeforeItem['ID']], 'select'=>array_keys($this->arBeforeItem)]);
                if ($arItem = $rsItem->Fetch())
                {
                    foreach($arItem as $key => $value){
                        if($value !== $this->arBeforeItem[$key]){
                            $this->add([
                                'ENTITY_TYPE' => $this->entityType,
                                'UF_ENTITY_TYPE' => $this->entityType,
                                'ENTITY_ID' => $arItem['ID'],
                                'FIELD_NAME' => $key,
                                'VALUE' => $value,
                                'BEFORE_VALUE' => $this->arBeforeItem[$key]
                            ]);
                        }
                    }
                }
            }
        }
    }

    public function add($params)
    {
        parent::add([
            'ENTITY_TYPE'=> $params['ENTITY_TYPE'],
            'ENTITY_ID' => $params['ENTITY_ID'],
            'EVENT_NAME' => 'Поле "'.$this->getFieldName($params).'"',
            'EVENT_TEXT_1' => $this->getFieldHtml([
                'VALUE' => $params['BEFORE_VALUE'],
                'ENTITY_TYPE' => $params['ENTITY_TYPE'],
                'UF_ENTITY_TYPE' => $params['UF_ENTITY_TYPE'],
                'FIELD_NAME' => $params['FIELD_NAME']
            ]),
            'EVENT_TEXT_2' => $this->getFieldHtml([
                'VALUE' => $params['VALUE'],
                'ENTITY_TYPE' => $params['ENTITY_TYPE'],
                'UF_ENTITY_TYPE' => $params['UF_ENTITY_TYPE'],
                'FIELD_NAME' => $params['FIELD_NAME']
            ])
        ]);
    }
}