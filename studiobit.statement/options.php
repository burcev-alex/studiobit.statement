<?
global $USER, $APPLICATION;
$module_id = "studiobit.statement";
$RIGHT = $APPLICATION->GetGroupRight($module_id);
if($RIGHT >= "W") :

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

CModule::IncludeModule($module_id);
CJSCore::Init(array("jquery"));

$arAllOptions =
	array(
		array("business_approval_process_short", "business_approval_process_short", "N", array("text", 10)),
		array("business_approval_process_long", "business_approval_process_long", "N", array("text", 10)),
	);

$aTabs = array();

$aTabs[] = array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_OTHER"), "ICON" => "cstudiobit_other", "TITLE" => GetMessage("MAIN_TAB_OTHER"));

$aTabs[] = array("DIV" => "edit7", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "cstudiobit_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS"));

$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($REQUEST_METHOD=="POST" && strlen($Update.$Apply.$RestoreDefaults) > 0 && $RIGHT=="W" && check_bitrix_sessid())
{
	require_once(__DIR__."/prolog.php");

	if(strlen($RestoreDefaults)>0)
	{
		COption::RemoveOption("studiobit.statement");
	}
	else
	{
		foreach($arAllOptions as $arOption)
		{
			$name=$arOption[0];
			$val=$_REQUEST[$name];
			if(is_array($val)) {
				foreach ($val as $keys => $v1) {
					if (strlen($v1) == 0) unset($val[$keys]);
				}
			}
			if(is_array($val)) $val = implode("|", $val);

			if($arOption[2][0]=="checkbox" && $val!="Y") $val="N";
			COption::SetOptionString("studiobit.statement", $name, $val, $arOption[1]);
		}
	}

	ob_start();
	$Update = $Update.$Apply;
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
	ob_end_clean();

	if(strlen($_REQUEST["back_url_settings"]) > 0)
	{
		if((strlen($Apply) > 0) || (strlen($RestoreDefaults) > 0))
			LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($module_id)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
		else
			LocalRedirect($_REQUEST["back_url_settings"]);
	}
	else
	{
		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($module_id)."&lang=".urlencode(LANGUAGE_ID)."&".$tabControl->ActiveTabParam());
	}
}

?>
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($module_id)?>&amp;lang=<?=LANGUAGE_ID?>">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>

    <tr class="heading">
        <td colspan="2"><b><?=GetMessage('MAIN_SECTION_BP')?></b></td>
    </tr>
	<tr>
		<td valign="top" width="20%">
			Бизнес-процесс согласования заявления (без ДС):
		<td valign="top" width="80%">
			<input type="text" size="5" value="<?=COption::GetOptionString("studiobit.statement", "business_approval_process_short");?>" name="business_approval_process_short">
		</td>
	</tr>
	<tr>
		<td valign="top" width="20%">
			Бизнес-процесс согласования заявления (с ДС):
		<td valign="top" width="80%">
			<input type="text" size="5" value="<?=COption::GetOptionString("studiobit.statement", "business_approval_process_long");?>" name="business_approval_process_long">
		</td>
	</tr>
	<?$tabControl->BeginNextTab();?>
        <?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
    <?$tabControl->Buttons();?>
	<input <?if ($RIGHT<"W") echo "disabled" ?> type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>">
	<input <?if ($RIGHT<"W") echo "disabled" ?> type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
	<?if(strlen($_REQUEST["back_url_settings"])>0):?>
		<input <?if ($RIGHT<"W") echo "disabled" ?> type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialchars(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialchars($_REQUEST["back_url_settings"])?>">
	<?endif?>
	<input type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
	<?=bitrix_sessid_post();?>
<?$tabControl->End();?>
</form>
<?endif;?>
