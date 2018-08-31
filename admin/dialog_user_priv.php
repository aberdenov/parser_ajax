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
			"dialog_user_priv" => "dialog_user_priv.tpl",
			"main_menu"     => "main_menu.tpl",
			"lang_select"   => "lang_select.tpl",
			"priv_item" => "priv_item.tpl", 
			"priv_sub_item" => "priv_sub_item.tpl", 
		));
		
	# DEFINES ################################################################################
	
	require_once("./control_menu.php");
	
	$user_id = intval($_GET['id']);

	# MAIN ##################################################################################

	if (isset($_POST['send'])) {
		// очистка прав доступа
		db_query("DELETE FROM users_pages_priv WHERE user_id = ".$user_id);

		db_query("INSERT INTO users_pages_priv VALUES ('', ".$user_id.", 4, 1, 1, 1)");

		foreach ($_POST['pages'] as $key => $value) {
			db_query("INSERT INTO users_pages_priv VALUES ('', ".$user_id.", ".$value.", 1, 1, 1)");	
		}

		// header("location: dialog_user_priv.php?id=".$user_id);
		// exit();
	}

	$pages_array = db_get_array("SELECT page_id FROM users_pages_priv WHERE user_id = ".$user_id, "page_id");

	$result = db_query("SELECT * FROM pages WHERE parent_id = 4 AND visible = 1 AND deleted = 0 ORDER BY sortfield");
	if (db_num_rows($result) > 0) {
		while ($row = db_fetch_array($result)) {	
			$tpl->clear("PAGES_SUB_LIST");

			$result2 = db_query("SELECT * FROM pages WHERE parent_id = ".$row['id']." AND visible = 1 AND deleted = 0 ORDER BY sortfield");
			if (db_num_rows($result2) > 0) {
				while ($row2 = db_fetch_array($result2)) {
					if (in_array($row2['id'], $pages_array)) $sel2 = 'checked="checked"';
						else $sel2 = '';

					$tpl->assign("SUB_ID", $row2['id']);
					$tpl->assign("SUB_TITLE", $row2['title']);
					$tpl->assign("SUB_SEL", $sel2);
					$tpl->parse("PAGES_SUB_LIST", ".priv_sub_item");
				}
			} else {
				$tpl->assign("PAGES_SUB_LIST", "");
			}		

			if (in_array($row['id'], $pages_array)) $sel = 'checked="checked"';
				else $sel = '';
			
			$tpl->assign("ID", $row['id']);
			$tpl->assign("TITLE", $row['title']);
			$tpl->assign("SEL", $sel);
			$tpl->parse("PAGES_LIST", ".priv_item");
		}
	} else {
		$tpl->assign("PAGES_LIST", "");
	}
	
	$tpl->parse("PAGE_CONTENT", "dialog_user_priv");
	$tpl->parse("FINAL", "page");
	$tpl->FastPrint();
?>