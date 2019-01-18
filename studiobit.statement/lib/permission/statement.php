<?php

namespace Studiobit\Statement\Permission;

use \Bitrix\Main\Loader;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

/**
 * Права доступа к заявлениям
 *
 * Class Statement
 * @package Studiobit\Statement\Permission
 */
class Statement
{
	protected static $TYPE_NAME = 'STATEMENT';

	public static function CheckCreatePermission($userPermissions = null)
	{
		return \CCrmAuthorizationHelper::CheckCreatePermission(self::$TYPE_NAME, $userPermissions);
	}

	public static function CheckUpdatePermission($ID, $userPermissions = null)
	{
		return \CCrmAuthorizationHelper::CheckUpdatePermission(self::$TYPE_NAME, $ID, $userPermissions);
	}

	public static function CheckDeletePermission($ID, $userPermissions = null)
	{
		return \CCrmAuthorizationHelper::CheckDeletePermission(self::$TYPE_NAME, $ID, $userPermissions);
	}

	public static function CheckReadPermission($ID = 0, $userPermissions = null)
	{
		return \CCrmAuthorizationHelper::CheckReadPermission(self::$TYPE_NAME, $ID, $userPermissions);
	}

	public static function updatePermission($id, $responsibleId)
	{
		$responsibleId = (int)$responsibleId;
		if($responsibleId <= 0)
		{
			return;
		}

		$entityAttrs = self::buildEntityAttr($responsibleId);
		\CCrmPerms::UpdateEntityAttr(self::$TYPE_NAME, $id, $entityAttrs);
	}

	/**
	 * @param $userId
	 * @param array $attributes
	 *
	 * @return array
	 */
	public static function buildEntityAttr($userId, $attributes = array())
	{
		$userId = (int)$userId;
		$result = array("U{$userId}");

		$userAttributes = \CCrmPerms::BuildUserEntityAttr($userId);
		return array_merge($result, $userAttributes['INTRANET']);
	}

	/**
	 * @param array $ids
	 *
	 * @return array
	 */
	public static function getPermissionAttributes(array $ids)
	{
		return \CCrmPerms::GetEntityAttr(self::$TYPE_NAME, $ids);
	}
}
