<?php
	# INCLUDES #########################################################################
	
	require_once("./includes/auth.php");
	require_once("./includes/common.php");
	require_once("./includes/class.ModuleSettings.php");
	require_once("./includes/db_init.php");
	require_once(FASTTEMPLATES_PATH . "template.php");

	# DEFINES ###########################################################################
	
	$tpl = new FastTemplate(TEMPLATES_PATH);
	
	$tpl->define(array(
			"dialog_pages_properties" => "dialog_pages_properties.tpl"
		));
	
	# VARS ##############################################################################
	
	$startPageChecked = '';	   // Стартовая страница 
	$visibleChecked   = '';	   // Страница видима   
	$authChecked      = '';	   // Страница доступна после авторизации   
	$list_templ       = '';	   // Список темплейтов  
	$page_id          =  0;
	$editable_type 	  = '';
	
	if (isset($_GET['page_id'])) $page_id = $_GET['page_id'];
	
	# MAIN #################################################################################
	
	$result = db_query("SELECT * FROM pages WHERE id = ".$page_id." LIMIT 1");
	$row = db_fetch_object($result);
	
	// Список типов страниц 
	$list_modules = '';
	if ($modulesList = getDirFiles(MODULES_PATH)){
		asort($modulesList);
		foreach ($modulesList as $i => $name) {
			if ($row->type == $name) {
				$selected  = 'selected'; 
				$page_type = $row->type;
				if (db_table_exists("module_".$page_type)) {
					$result2 = db_query("SELECT id FROM module_".$page_type." WHERE page_id=".$page_id);
					if (db_num_rows($result2) > 0) {
						$editable_type = 'disabled';
					}
				}
			} else {
				$selected = '';
			}
			$list_modules.= "<option ".$selected.">".$name."</option>";
		}
	}
	
	$page_sort_by    = $row->sort_by;
	$page_sort_order = $row->sort_order;

	// Список групп 
	$valueGroup = '<option value="0"></option>';
	$result2 = db_query("SELECT * FROM page_groups ORDER BY name");
	while ($row2 = db_fetch_object($result2)) {
		if ($row->group_id == $row2->id) $selected = 'selected';
			else $selected = '';
		
		$valueGroup .= "<option ".$selected." value='".$row2->id."'>".add_quotes($row2->name)."</option>";
	}
	
	// Флажки
	if ($row->startpage) $startPageChecked = 'checked';
	if ($row->visible) $visibleChecked = 'checked';
	if ($row->auth) $authChecked = 'checked';
	
	// Список темплейтов
	$list_templ = '';
	if ($modulesList = getDirFiles(PAGE_TEMPLATES_PATH)){
		asort($modulesList);
		foreach ($modulesList as $i => $name) {
			
			$tpl_desc = '';
			$meta_content = get_meta_tags(PAGE_TEMPLATES_PATH.$name);
			if (isset($meta_content['template']) && $meta_content['template'] != '') {
				$meta_content = explode("|", $meta_content['template']);
				if (count($meta_content) > 0) $tpl_desc = ' - '.$meta_content[$_SESSION['lang_id']-1];
			}
			
			if ($row->template == $name) $selected = ' selected'; else $selected = '';
			$list_templ .= '<option value="'.$name.'"'.$selected.'>'.$name.$tpl_desc.'</option>';
		}
	}
	
	// Список модулей обработчиков 
	$user_defined = true;
	$list_content = '<option value=""> - определено пользователем - </option>';
	if ($modulesList = getDirFiles(PAGE_MODULES_PATH, true)) {
		asort($modulesList);
		foreach ($modulesList as $i => $name) {
				$_module_name = '{'.strtoupper($name).'}';
				if ($_module_name == $row->content) $user_defined = false;
				
				$settings_file = PAGE_MODULES_PATH.$name.'/settings.xml';
				$_module_desc = ModuleSettings::getModuleDescription($settings_file, $_SESSION['lang_id']);
				
				if ($_module_desc)	{
					if ($row->content == $_module_name) $selected = 'selected'; else $selected = '';
					$list_content .= '<option value="'.$_module_name.'" '.$selected.'>'.$_module_desc.' - '.$name.'</option>';
				}
		}
	}
	
	if ($user_defined) {
		$tpl->assign("USER_DEF_VALUE", $row->content);
		$tpl->assign("USER_DEF_DISPLAY", '');
	} else {
		$tpl->assign("USER_DEF_DISPLAY", 'none');
		$tpl->assign("USER_DEF_VALUE", '');
	}
	
	// Список порядка сортировки 
	$list_sorting = '<option value="">-</option>';
	$filename = "./modules/".$page_type."/fields.php";
	
	if (file_exists($filename)) {
		require_once($filename);
		
		if (isset($fields)) {
			foreach($fields as $fieldkey => $fieldval) {
				if (isset($fieldval['Sortfield']) && $fieldval['Sortfield'] == true) {
					if ($page_sort_by == $fieldkey) $sel = 'selected'; else $sel = '';
					$list_sorting.= '<option value="'.$fieldkey.'" '.$sel.'>'.$fieldval['Title'][$_SESSION['lang_id']].'</option>';
				}
			}
		}
	}
	
	assignList("SORTORDER_LIST", array("по возрастанию", "по убыванию"), $page_sort_order);
	
	// Выключаем поле description, если страница является системной
	if ($row->description != '' && substr($row->description, 0, 1) == '@') $description_locked = 'readonly';
		else $description_locked = '';
	
	$tpl->assign(array(
			"VALUE_ID"       => $page_id,
			"VALUE_TYPE"     => $list_modules,
			"EDITABLE_TYPE"  => $editable_type,
			"VALUE_GROUP"    => $valueGroup,
			"VALUE_MOD_REWRITE"    => add_quotes($row->mod_rewrite),
			"VALUE_TITLE"    => add_quotes($row->title),
			"VALUE_DESCRIPTION" => add_quotes($row->description),
			"VALUE_KURS" => add_quotes($row->kurs),
			"VALUE_ICON"        => add_quotes($row->icon),
			"SESSION_ID" 		=> session_id(SESSION_ID),
			"VALUE_LINK"        => add_quotes($row->external_link),
			"PAGE_ID"   		=> $row->id,
			"PAGE_PARENT_ID"    => $row->parent_id,
			"OLD_TITLE"         => add_quotes($row->title),
			"TEMPLATE_LIST"     => $list_templ,
			"VALUE_CONTENT"     => add_quotes($row->content),
			"VALUE_START_PAGE"  => $startPageChecked,
			"VALUE_VISIBLE"     => $visibleChecked,
			"CONTENT_LIST"      => $list_content,
			"SORTBY_LIST"       => $list_sorting,
			"DESCRIPTION_LOCKED" => $description_locked,
			"VALUE_AUTH"     	=> $authChecked,
			"DELETED"			=> intval($row->deleted),
		));
	
	$tpl->parse("FINAL", "dialog_pages_properties");
	$tpl->FastPrint();
?>