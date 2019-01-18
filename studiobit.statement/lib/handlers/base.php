<?
namespace Studiobit\Statement\Handlers;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Base
{
    static function onRegisterNamespaceForRouter()
	{
		return array(
			'ROUTE' => 'statement',
			'NAMESPACE' => 'Studiobit\Statement\Controller'
		);
	}
}
