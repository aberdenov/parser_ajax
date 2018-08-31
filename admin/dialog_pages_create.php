<?php
	# DEFINES ############################################################################

	require_once("./includes/auth.php");
	require_once("./includes/common.php");
	require_once("./includes/class.ModuleSettings.php");

	# MAIN ###############################################################################
	
	require_once(FASTTEMPLATES_PATH . "template.php");

	$tpl = new FastTemplate(TEMPLATES_PATH);
	
	$tpl->define(array(
			"dialog_pages_create" => "dialog_pages_create.tpl"
		));

	// Список модулей конструкторов
	$def_type = db_get_data("SELECT type FROM pages WHERE id = ".$_GET['page_id'], "type");

	$list = '';
	if ($modulesList = getDirFiles(MODULES_PATH)) {
		asort($modulesList);
		foreach ($modulesList as $i => $name) {
			if ($def_type == $name) $sel = 'selected="selected"';
				else $sel = '';

			$list .= "<option ".$sel.">".$name."</option>";
		}
	}
	
	// Список темплейтов
	$list_templ = '';
	$selected   = '';
	if ($modulesList = getDirFiles(PAGE_TEMPLATES_PATH)){
		asort($modulesList);
		foreach ($modulesList as $i => $name) {
			
			$tpl_desc = '';
			$meta_content = get_meta_tags(PAGE_TEMPLATES_PATH.$name);
			if (isset($meta_content['template']) && $meta_content['template'] != '') {
				$meta_content = explode("|", $meta_content['template']);
				if (count($meta_content) > 0) $tpl_desc = ' - '.$meta_content[$_SESSION['lang_id']-1];
			}
			
			$list_templ .= '<option value="'.$name.'"'.$selected.'>'.$name.$tpl_desc.'</option>';
		}
	}
	
	// Список модулей обработчиков
	$list_content = '<option value=""> - определено пользователем - </option>';
	if ($modulesList = getDirFiles(PAGE_MODULES_PATH, true)) {
		asort($modulesList);
		foreach ($modulesList as $i => $name) {
				$settings_file = PAGE_MODULES_PATH.$name.'/settings.xml';
				$_module_desc = ModuleSettings::getModuleDescription($settings_file, $_SESSION['lang_id']);
				
				if ($_module_desc)	{
					$list_content .= '<option value="{'.strtoupper($name).'}">'.$_module_desc.' - '.$name.'</option>';
				}
		}
	}
	
	$tpl->assign(array(
			"MODULES_LIST" => $list,
			"TEMPLATE_LIST" => $list_templ,
			"CONTENT_LIST" => $list_content,
			"PARENT_ID" => $_GET['page_id'], 
		));
	
	$tpl->parse("FINAL", "dialog_pages_create");
	$tpl->FastPrint();
?>