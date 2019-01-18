<?
namespace Studiobit\Statement;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Event
{
    public static function onPageStart()
    {
        self::setupEventHandlers();
    }

    /**
     * Добавляет обработчики событий
     *
     * @return void
     */
    protected static function setupEventHandlers()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();

        //studiobit.base
        $eventManager->addEventHandler('studiobit.base', 'onRegisterNamespaceForRouter', array('\Studiobit\Statement\Handlers\Base', 'onRegisterNamespaceForRouter'));

        // crm
        $eventManager->addEventHandler('crm', 'OnAfterCrmControlPanelBuild', array('\Studiobit\Statement\Handlers\Crm', 'onAfterCrmControlPanelBuild'));

        // события HL-блока заявления
	    $eventManager->addEventHandler('', 'StatementOnBeforeAdd', array('\Studiobit\Statement\Handlers\Statement', 'onBeforeAdd'));
        $eventManager->addEventHandler('', 'StatementOnBeforeUpdate', array('\Studiobit\Statement\Handlers\Statement', 'onBeforeUpdate'));
        $eventManager->addEventHandler('', 'StatementOnAfterUpdate', array('\Studiobit\Statement\Handlers\Statement', 'onAfterUpdate'));

    }
}
