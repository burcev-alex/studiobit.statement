<?php
namespace Studiobit\Statement;

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Loader;

/**
 * Базовый каталог модуля
 */
const BASE_DIR = __DIR__;

/**
 * Имя модуля
**/

const MODULE_ID = 'studiobit.statement';

IncludeModuleLangFile(__FILE__);

$arClassBase = array(
	'\Studiobit\Statement\Event' => 'lib/event.php',
    '\Studiobit\Statement\Handlers\Base' => 'lib/handlers/base.php',
    'Studiobit\Statement\Entity\Templates' => 'lib/entity/templates.php',
    '\Studiobit\Statement\Handlers\Crm' => 'lib/handlers/crm.php',
    '\Studiobit\Statement\Handlers\Statement' => 'lib/handlers/statement.php',
    '\Studiobit\Statement\Entity\Statement' => 'lib/entity/statement.php',
    '\Studiobit\Statement\Tools' => 'lib/tools.php',
	'Studiobit\Statement\Permission\Statement' => 'lib/permission/statement.php',
);

$arClassLib = array(
);

Loader::registerAutoLoadClasses(
	'studiobit.statement',
	array_merge($arClassBase, $arClassLib)

);

\CJSCore::RegisterExt(
    "studiobit_statement",
    array(
        "js" => array(
        	"/local/static/js/studiobit.statement/core.js",
        	"/local/static/js/studiobit.statement/bizproc.js",
        ),
        "rel" => Array("jquery2", "utils")
    )
);

