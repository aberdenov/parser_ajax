<?php
	define("IN_SITEDRIVE", 1);
	$ip = 0;

	require_once ("./includes/common.php");
	require_once("./includes/db_init.php");

	# POST ####################################################################################

	if (isset($_POST['password'])) {
		$usrname = substr($_POST['login'], 0, 15);
		$usrpass = substr($_POST['password'], 0, 30);
		
		if (preg_match("/[^(\w)|(\x7F-\xFF)|(\s)]/", $usrname)) $usrname = "";
		if (preg_match("/[^(\w)|(\x7F-\xFF)|(\s)]/", $usrpass)) $usrpass = "";
		
		$result = db_query("SELECT * FROM users WHERE login = '".$usrname."' AND password = MD5('".$usrpass."') LIMIT 1");
		
		if (db_num_rows($result)) {
			
			$row = db_fetch_object($result);
			
			if ($row->active == 1) {
				$ip = get_ip();
				
				if ($ip == long2ip($row->ip) || empty($row->ip)) {
					session_name('SID');
					session_start();
				
					$_SESSION['login'] = $usrname;
					$_SESSION['password'] = $usrpass;
					$_SESSION['user_ID'] = $row->id;
					$_SESSION['user_groupID'] = $row->group_id;
					
					if ($row->lang_id > 0) $sql = "SELECT * FROM languages WHERE id = ".$row->lang_id." LIMIT 1";
						else $sql = "SELECT * FROM languages WHERE main = 1 LIMIT 1";
					
					if (!$result = db_query($sql)) {
						exit;
					}
					
					$row = db_fetch_object($result);
					
					$_SESSION['lang_id'] = $row->id;
					$_SESSION['lang_name'] = $row->name;
					$_SESSION['lang_file'] = $row->file;
					$_SESSION['lang_encoding'] = $row->encoding;
					
					// Сохраняем логин и пароль в куках, удаляем если не отметили "запомнить"
					if (isset($_POST['chk_save'])) {
						$cookie_value = $usrname."|".$usrpass."|".$_SERVER['HTTP_HOST'];
						$cookie_value = crypt_string($cookie_value);
						setcookie("sd_auth", $cookie_value, time()+60*60*24*30, "", $_SERVER['HTTP_HOST']);
					} else {
						if (isset($_COOKIE['sd_auth'])) {
							$cookie_value = "";
							setcookie("sd_auth", $cookie_value, 0, "", $_SERVER['HTTP_HOST']);
						}
					}				
					
					header("Location: control.php");
				}
			} else {
				showMsg('Учетная запись пользователя блокирована.', 'login.php');
			}
		} else {
			showMsg('Неверное сочетание логина и пароля', 'login.php');
		}
	}

	# MAIN #######################################################################################

	require_once (FASTTEMPLATES_PATH."template.php");

	$tpl = new FastTemplate(TEMPLATES_PATH);
	
	$tpl->define(array(
			"page" => "page.tpl",
			"form" => "login_form.tpl"
		));

	// Поднимаем логин и пароль из кук
	$usr_login = '';
	$usr_passw = '';
	$save      = '';
	
	if (isset($_COOKIE['sd_auth'])) {
		$str = crypt_string($_COOKIE['sd_auth'], false);
		
		$login_info = explode("|", $str);
		if (is_array($login_info)) {
			$host = $login_info[2];
			if ($host == $_SERVER['HTTP_HOST']) {
				$usr_login = $login_info[0];
				$usr_passw = $login_info[1];
				$save = 'checked';
			}
		}
	}
	
	$msg = '';
	
	if (isset($_GET['complete']) && file_exists("install.php")) {
		$msg = '<div id="warning"><b>Внимание!!!</b> Удалите инсталляционный файл <b></i>install.php</b></div>';
	}
	
	if (isset($_GET['auth']) == 1){
		$msg.= '<div id="warning"><b>Истекло время ожидания. Авторизируйтесь снова.</b></div>';
	}
	
	$tpl->assign(array(
			"USR_LOGIN" => $usr_login,
			"USR_PASSW" => $usr_passw,
			"SAVE"      => $save,
		));
	
	$tpl->parse("PAGE_CONTENT", "form");
	
	$tpl->assign(array(
			"PAGE_TITLE"    => "Вход в систему",
			"SCRIPT"        => "var tp = window",
			"PAGE_ENCODING" => 'utf-8',
			"WINDOW_STATUS" => "",
			"MAIN_MENU"     => "",
			"LOGOUT_BUTTON" => "",
			"SITE_BUTTON" => "",
			"LANG_ON_SITE"  => "",
			"MSG"     		=> $msg
		));
	
	$tpl->parse("FINAL", "page");
	$tpl->FastPrint();
?>
