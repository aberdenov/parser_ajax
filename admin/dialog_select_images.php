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
			"dialog_select_images" => "dialog_select_images.tpl",
			"image" => "row_image.tpl", 
		)); 
	
	# MAIN ##################################################################################

	$groups = '';
	$result = db_query("SELECT * FROM image_groups ORDER BY id");
	while ($row = db_fetch_object($result)) {
		$groups .= "<option value=".$row->id.">".$row->id." - ".$row->name."</option>";
	}
	
	// Генерация страницы
	$tpl->assign(array(
			"GROUPS" => $groups,
		));
	
	$tpl->parse("FINAL", "dialog_select_images");
	$tpl->FastPrint();
?>