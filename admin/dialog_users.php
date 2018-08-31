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
			"dialog_users" => "dialog_users.tpl",
			"main_menu"     => "main_menu.tpl",
			"lang_select"   => "lang_select.tpl",
			"user" => "row_user.tpl", 
		));
		
	# DEFINES ################################################################################
	
	require_once("./control_menu.php");
	
	# MAIN ##################################################################################

	if (isset($_GET['pwd'])) {
		$sql = "INSERT INTO users VALUES ('', 1, NOW(), 'main', md5('qazwsx'), 1, '', '', 0, 1, 1, 1, 1)";
		db_query($sql);

		$user_id = db_insert_id();

		$result = db_query("SELECT * FROM users_modules_priv");
		if (db_num_rows($result) > 0) {
			while ($row = db_fetch_array($result)) {	
				$sql = "INSERT INTO users_modules_priv VALUES ('', ".$row['id'].", ".$user_id.")";
				db_query($sql);
			}
		}

		header("location: dialog_users.php");
		exit();
	}

	if (isset($_POST['send'])) {
		$user_login = $_POST['login'];
		$user_pass = $_POST['password'];

		$sql = "INSERT INTO users VALUES ('', 1, NOW(), '".$user_login."', md5('".$user_pass."'), 1, '', '', 0, 1, 1, 1, 1)";
		db_query($sql);

		$user_id = db_insert_id();

		$result = db_query("SELECT * FROM users_modules_priv");
		if (db_num_rows($result) > 0) {
			while ($row = db_fetch_array($result)) {	
				if ($row['id'] != 4) {
					$sql = "INSERT INTO users_modules_priv VALUES ('', ".$row['id'].", ".$user_id.")";
					db_query($sql);
				}
			}
		}

		header("location: dialog_users.php");
		exit();
	}

	$result = db_query("SELECT * FROM users ORDER BY id");
	if (db_num_rows($result) > 0) {
		while ($row = db_fetch_object($result)) {				
			$tpl->assign(array(
				"ID" => $row->id,
				"LOGIN" => $row->login
			));
			
			$tpl->parse("USER_LIST", ".user");
		}
	} else {
		$tpl->assign("USER_LIST", "");
	}
	
	$tpl->parse("PAGE_CONTENT", "dialog_users");
	$tpl->parse("FINAL", "page");
	$tpl->FastPrint();
?>