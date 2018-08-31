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
			"dialog_delete_screenshots" => "dialog_delete_screenshots.tpl",
			"main_menu"     => "main_menu.tpl",
			"lang_select"   => "lang_select.tpl",
		));
		
	# DEFINES ################################################################################
	
	require_once("./control_menu.php");

	$dir = './../photos/';
	
	# MAIN ##################################################################################

	if (isset($_POST['send'])) {
		$time = date("Y-m-d", strtotime("-".$_POST['time']." days"));
		$today = date("Y-m-d");

		if (is_dir($dir)) {
			if ($od = opendir($dir)) { 
				while (($file = readdir($od)) !== false) {
					if ($file != "." && $file != "..") {
						$file_time = date("Y-m-d", filectime($dir.$file));
						
						if ($file_time < $today && $file_time >= $time) {
							unlink($dir.$file);
						}
						
					}
				}
			}

			closedir($od);
		}

		header("location: dialog_delete_screenshots.php?result=1");
		exit();
	}

	if (isset($_GET['result'])) {
		if ($_GET['result'] == 1) $tpl->assign("RESULT_MESSAGE", '<div class="result_success">Файлы успешно удалены</div>');
	} else {
		$tpl->assign("RESULT_MESSAGE", "");
	}
	
	$tpl->parse("PAGE_CONTENT", "dialog_delete_screenshots");
	$tpl->parse("FINAL", "page");
	$tpl->FastPrint();
?>