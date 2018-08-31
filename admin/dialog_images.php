<?php
	# DEFINES ############################################################################
	
	ob_start("ob_gzhandler", 9);
	
	require_once("./includes/auth.php");
	require_once("./includes/common.php");
	require_once("./includes/db_init.php");
	require_once("../class.Pages.php");
	require_once(FASTTEMPLATES_PATH . "template.php");
	
	$tpl = new FastTemplate(TEMPLATES_PATH);
	
	$tpl->define(array(
			"page" => "page.tpl",
			"dialog_images" => "dialog_images.tpl",
			"main_menu"     => "main_menu.tpl",
			"lang_select"   => "lang_select.tpl",
			"image" => "row_image.tpl", 
		));
		
	# DEFINES ################################################################################
	
	require_once("./control_menu.php");
	
	// Инициализация
	$selimg = intval($_GET['selimg']);
	$selimg_id = intval($_GET['selimg_id']);
	$check_type = true;
	
	if (isset($_GET['group_id'])) $group_id = $_GET['group_id'];
		else $group_id = db_get_data("SELECT id FROM image_groups LIMIT 1", "id");
	
	if (isset($_GET['selimg_id'])) {
		$result = db_get_data("SELECT group_id FROM images WHERE id = ".$selimg_id." LIMIT 1", "group_id");
		if ($result > 0) $group_id = $result;
	}
	
	# MAIN ##################################################################################

	/////////////////////////////////////////////////////////////////////////////////////////
	// Список групп изображений                                                              
	/////////////////////////////////////////////////////////////////////////////////////////

	$groups = '';
	$result = db_query("SELECT * FROM image_groups ORDER BY id");
	while ($row = db_fetch_object($result)) {
		if ($group_id == $row->id) $selected = 'selected'; else $selected = '';
		$groups .= "<option value=".$row->id." ".$selected.">".$row->id." - ".$row->name."</option>";
	}
	
	/////////////////////////////////////////////////////////////////////////////////////////
	// Список изображений в группе                                                           
	/////////////////////////////////////////////////////////////////////////////////////////

	if ($group_id > 0) {
		$pages = new Pages(15, 10);
		$pages->rowsPerPage = 10;
		$pages->pagesRegion = 15;
		$pages->numRows = db_table_count('images', 'group_id = '.$group_id);
		
		// Список изображений в группе
		$result = db_query("SELECT * FROM images WHERE group_id = ".$group_id." ORDER BY id DESC LIMIT ".$pages->getLimit());
		if (db_num_rows($result) > 0) {
			while ($row = db_fetch_object($result)) {				
				$tpl->assign(array(
					"ID" => $row->id,
					"TITLE" => $row->title,
					"FILE" => $row->url,
				));
				
				$tpl->parse("UPLOADED_IMAGES_LIST", ".image");
			}
		} else {
			$tpl->assign("UPLOADED_IMAGES_LIST", "");
		}
		
		// Разбивка по страницам
		if ($pages->getPagesCount() > 1) {
			$pageUrl = 'dialog_images.php?group_id='.$group_id.'&selimg='.$selimg.'&page={PAGE}';
			$pageView = $pages->getPageLinks($pageUrl, 'pages');
			$tpl->assign("GALLERY_PAGES", "Страницы: ".$pageView);
		} else { $tpl->assign("GALLERY_PAGES", ""); }
	} else {
		$tpl->assign("UPLOADED_IMAGES_LIST", "");
		$tpl->assign("GALLERY_PAGES", "");
	}

	// Всего изображений в группе
	$total = db_get_data("SELECT COUNT(*) AS num FROM images WHERE group_id = ".$group_id, "num");
	$total = "Все изображений: ".$total;
	
	// Генерация страницы
	$tpl->assign(array(
			"GROUPS" => $groups,
			"MAX_FILE_SIZE" => CONFIG_UPLOAD_FILESIZE,
			"TIMESTAMP" => time(),
			"SALT" => md5('unique_salt'.time()),
			"TOTAL_IMAGES" => $total,
			"MENU_ID" => $_GET['menu_id'],
			"GROUP_ID" => $group_id,
			"SEL_IMG" => $selimg,
		));
	
	$tpl->parse("PAGE_CONTENT", "dialog_images");
	$tpl->parse("FINAL", "page");
	$tpl->FastPrint();
?>