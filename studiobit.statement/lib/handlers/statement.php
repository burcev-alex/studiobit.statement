<?
namespace Studiobit\Statement\Handlers;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;
use Studiobit\Base;

Loc::loadMessages(__FILE__);

class Statement
{
	public static function onBeforeAdd(Entity\Event $event)
	{
		global $USER;
		$result = new Entity\EventResult;
		$data = $event->getParameter("fields");

		// автор по умолчанию
		// установка статуса "Черновик" по умолчанию
		/*$result->modifyFields(array(
			"UF_RESPONSIBLE" => $USER->GetID(),
			"UF_DATE_CREATE" => date("d.m.Y H:i:s"),
			"UF_STATUS" => Base\Tools::getIDInUFPropEnumByXml("UF_STATUS", "STATEMENT_DRAFT"),
			"UF_STAGE_ID" => Base\Tools::getIDInUFPropEnumByXml("UF_STAGE_ID", "STATEMENT_HUNTER"),
		));

		if (IntVal($data['UF_CLIENT']) == 0) {
			$result->addError(new Entity\FieldError(
				$event->getEntity()->getField('UF_CLIENT'),
				'Поле "Клиент" обязательно к заполнению'
			));
		}

		if (IntVal($data['UF_REQUIRED_DC']) == 248 && (strlen($data['UF_DATE_DEADLINE_DC']) == 0)) {
			$result->addError(new Entity\FieldError(
				$event->getEntity()->getField('UF_DATE_DEADLINE_DC'),
				'Поле "Срок подготовки ДС" обязательно к заполнению'
			));
		}*/

		return $result;
	}

    public static function onBeforeUpdate(Entity\Event $event)
    {
        $data = $event->getParameter("fields");

        $result = new Entity\EventResult;

        $id = $event->getParameter("id");

        if(is_array($id))
            $id = $id['ID'];

        $data['ID'] = $id;

        Base\History\HLBlockHistory::getInstance()->setEntityType('STATEMENT');
        Base\History\HLBlockHistory::getInstance()->before($data, '\Studiobit\Statement\Entity\StatementTable');

        return $result;
    }

    public static function onAfterUpdate(Entity\Event $event)
    {
        //сохранение в историю
        Base\History\HLBlockHistory::getInstance()->after('\Studiobit\Statement\Entity\StatementTable');
    }
}
