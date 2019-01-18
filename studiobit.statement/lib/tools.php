<?
namespace Studiobit\Statement;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Tools
{
	public static function RenderProgressControl($arParams)
	{
		if(!is_array($arParams))
		{
			return '';
		}

		\CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/progress_control.js');

		$entityTypeName = isset($arParams['ENTITY_TYPE_NAME']) ? $arParams['ENTITY_TYPE_NAME'] : '';

		$infos = isset($arParams['INFOS']) ? $arParams['INFOS'] : null;

		$enableCustomColors = false;

		if(!is_array($infos) || empty($infos))
		{
			return '';
		}

		$registerSettings = isset($arParams['REGISTER_SETTINGS']) && is_bool($arParams['REGISTER_SETTINGS'])
			? $arParams['REGISTER_SETTINGS'] : false;

		$registrationScript = '';

		$finalID = isset($arParams['FINAL_ID']) ? $arParams['FINAL_ID'] : '';
		if($finalID === '')
		{
			$finalID = 'WON';
		}

		$finalUrl = isset($arParams['FINAL_URL']) ? $arParams['FINAL_URL'] : '';
		if($finalUrl === '')
		{
			$arParams['FINAL_URL'] = '';
		}

		$currentInfo = null;
		$currentID = isset($arParams['CURRENT_ID']) ? $arParams['CURRENT_ID'] : '';
		if($currentID !== '' && isset($infos[$currentID]))
		{
			$currentInfo = $infos[$currentID];
		}
		$currentSort = is_array($currentInfo) && isset($currentInfo['SORT']) ? intval($currentInfo['SORT']) : -1;

		$finalInfo = null;
		if($finalID !== '' && isset($infos[$finalID]))
		{
			$finalInfo = $infos[$finalID];
		}
		$finalSort = is_array($finalInfo) && isset($finalInfo['SORT']) ? intval($finalInfo['SORT']) : -1;

		$isSuccessful = $currentSort === $finalSort;
		$isFailed = $currentSort > $finalSort;

		$defaultProcessColor = \CCrmViewHelper::PROCESS_COLOR;
		$defaultSuccessColor = \CCrmViewHelper::SUCCESS_COLOR;
		$defaultFailureColor = \CCrmViewHelper::FAILURE_COLOR;

		$stepHtml = '';
		$color = isset($currentInfo['COLOR']) ? $currentInfo['COLOR'] : '';
		if($color === '')
		{
			$color = $defaultProcessColor;
			if($isSuccessful)
			{
				$color = $defaultSuccessColor;
			}
			elseif($isFailed)
			{
				$color = $defaultFailureColor;
			}
		}

		$finalColor = isset($finalInfo['COLOR']) ? $finalInfo['COLOR'] : '';
		if($finalColor === '')
		{
			$finalColor = $isSuccessful ? $defaultSuccessColor : $defaultFailureColor;
		}

		foreach($infos as $info)
		{
			$ID = isset($info['STATUS_ID']) ? $info['STATUS_ID'] : '';
			$sort = isset($info['SORT']) ? (int)$info['SORT'] : 0;

			if($sort > $finalSort)
			{
				break;
			}

			if($enableCustomColors)
			{
				$stepHtml .= '<td class="crm-list-stage-bar-part"';
				if($sort <= $currentSort)
				{
					$stepHtml .= ' style="background:'.$color.'"';
				}
				$stepHtml .= '>';
			}
			else
			{
				$stepHtml .= '<td class="crm-list-stage-bar-part';
				if($sort <= $currentSort)
				{
					$stepHtml .= ' crm-list-stage-passed';
				}
				$stepHtml .= '">';
			}

			$stepHtml .= '<div class="crm-list-stage-bar-block  crm-stage-'.htmlspecialcharsbx(strtolower($ID)).'"><div class="crm-list-stage-bar-btn"></div></div></td>';
		}

		$wrapperStyle = '';
		$wrapperClass = '';
		if($enableCustomColors)
		{
			if($isSuccessful || $isFailed)
			{
				$wrapperStyle = 'style="background:'.$finalColor.'"';
			}
		}
		else
		{
			if($isSuccessful)
			{
				$wrapperClass = ' crm-list-stage-end-good';
			}
			elseif($isFailed)
			{
				$wrapperClass =' crm-list-stage-end-bad';
			}
		}

		$prefix = isset($arParams['PREFIX']) ? $arParams['PREFIX'] : '';
		$entityID = isset($arParams['ENTITY_ID']) ? intval($arParams['ENTITY_ID']) : 0;
		$controlID = isset($arParams['CONTROL_ID']) ? $arParams['CONTROL_ID'] : '';

		if($controlID === '')
		{
			$controlID = $entityTypeName !== '' && $entityID > 0 ? "{$prefix}{$entityTypeName}_{$entityID}" : uniqid($prefix);
		}

		$isReadOnly = isset($arParams['READ_ONLY']) ? (bool)$arParams['READ_ONLY'] : false;
		$legendContainerID = isset($arParams['LEGEND_CONTAINER_ID']) ? $arParams['LEGEND_CONTAINER_ID'] : '';
		$displayLegend = $legendContainerID === '' && (!isset($arParams['DISPLAY_LEGEND']) || $arParams['DISPLAY_LEGEND']);
		$legendHtml = '';
		if($displayLegend)
		{
			$legendHtml = '<div class="crm-list-stage-bar-title">'.htmlspecialcharsbx(isset($infos[$currentID]) && isset($infos[$currentID]['NAME']) ? $infos[$currentID]['NAME'] : $currentID).'</div>';
		}

		$conversionScheme = null;
		if(isset($arParams['CONVERSION_SCHEME']) && is_array($arParams['CONVERSION_SCHEME']))
		{
			$conversionScheme = array();
			if(isset($arParams['CONVERSION_SCHEME']['ORIGIN_URL']))
			{
				$conversionScheme['originUrl'] = $arParams['CONVERSION_SCHEME']['ORIGIN_URL'];
			}
			if(isset($arParams['CONVERSION_SCHEME']['SCHEME_NAME']))
			{
				$conversionScheme['schemeName'] =  $arParams['CONVERSION_SCHEME']['SCHEME_NAME'];
			}
			if(isset($arParams['CONVERSION_SCHEME']['SCHEME_CAPTION']))
			{
				$conversionScheme['schemeCaption'] =  $arParams['CONVERSION_SCHEME']['SCHEME_CAPTION'];
			}
			if(isset($arParams['CONVERSION_SCHEME']['SCHEME_DESCRIPTION']))
			{
				$conversionScheme['schemeDescription'] =  $arParams['CONVERSION_SCHEME']['SCHEME_DESCRIPTION'];
			}
		}
		$conversionTypeID = isset($arParams['CONVERSION_TYPE_ID']) ? (int)$arParams['CONVERSION_TYPE_ID'] : 0;

		return $registrationScript.'<div class="crm-list-stage-bar'.$wrapperClass.'" '.$wrapperStyle.' id="'.htmlspecialcharsbx($controlID).'"><table class="crm-list-stage-bar-table"><tr>'
			.$stepHtml
			.'</tr></table>'
			.'</div>'.$legendHtml;
	}
}
