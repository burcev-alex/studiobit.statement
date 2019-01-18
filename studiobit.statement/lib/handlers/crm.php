<?
namespace Studiobit\Statement\Handlers;

use Bitrix\Crm\Integration\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Studiobit\Statement;

Loc::loadMessages(__FILE__);

class Crm
{
	public static function onAfterCrmControlPanelBuild(&$items){
		$userPerms = \CCrmPerms::GetCurrentUserPermissions();

		// проверить, что есть права на доступ к разделу
		if(Statement\Permission\Statement::CheckReadPermission(0, $userPerms)) {
			$items[] = array(
				"NAME" => "Заявления",
				"TITLE" => "Заявления",
				"ID" => "STATEMENT",
				"URL" => "/crm/statement/",
				"ICON" => "deal",
				"SORT" => 270
			);

			// сортировка
			$ar_sort = array();
			foreach ($items as $ar_item) {
				$ar_sort[] = $ar_item['SORT'];
			}
			array_multisort($ar_sort, SORT_ASC, $items);
		}
	}
}
