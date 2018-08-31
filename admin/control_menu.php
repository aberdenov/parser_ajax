<?php
	$MenuN = 1;
	
	// Main Menu Languages Select =======================
	$result = db_query("SELECT * FROM languages ORDER BY id");
	while ($row = db_fetch_object($result)) {
		if ($row->id == $_SESSION['lang_id']) $sel = 'selected'; else $sel = '';
		if ($row->main == 1) $name = '&#149; '.$row->name; else $name = '&nbsp;&nbsp;'.$row->name;
		if ($row->blocked == 1) $color = '#CCCCCC'; else $color = '#FFFFFF';
		
		$languages.= '<option value="'.$row->id.'" '.$sel.' style="background-color: '.$color.'">'.$name.'</option>';
	}
	
	if ($_SESSION['user_groupID'] == 1) $enable = ''; else $enable = 'disabled="disabled"';
	
	$tpl->assign("LANG_SELECT_ENABLE", $enable);
	$tpl->assign("OPTIONS", $languages);
	$tpl->parse("MAIN_MENU", "lang_select");
	
	// Main Menu Buttons ==============================
	$result = db_query("SELECT * FROM users_modules_priv WHERE user_id = ".$_SESSION['user_ID']);
	while ($row = db_fetch_array($result)) {
		$moduls[] = $row['module'];
	}
	
	$result = db_query("SELECT * FROM users_modules WHERE menu_id = ".$MenuN);
	while ($row = db_fetch_array($result)) {
		if ($row['active'] == 1) {
			switch ($row['priv']) {
			case 2:
				$result2 = db_query("SELECT group_id FROM users WHERE id = ".$_SESSION['user_ID']);
				$row2 = db_fetch_array($result2);
				if ($row2['group_id'] == 1) {
					$item_show = true;
				}
				break;
			case 1:
				if (in_array($row['id'], $moduls)) {
					$item_show = true;
				}
				break;
			case 0:
				$item_show = true;
				break;
			}
			
			if ($item_show == true) {
				$tpl->assign("MENU_ID", $row['id']);
				$tpl->assign("URL", $row['dialog_name']);
				$tpl->assign("IMG_SRC", "images/".$row['image']);				
				$tpl->parse("MAIN_MENU", ".main_menu");
				$item_show = false;
			}
		}
	}
	
	$logout = '<img src="images/logout.gif" width="32" height="32" border="0" alt="Выйти" style="cursor: hand;" onclick="window.location = \'?logout\'">';
	$site = '<img src="images/site.gif" width="32" height="32" border="0" alt="Перейти на сайт" style="cursor: hand;" onclick="window.location = \'http://'.$_SERVER['HTTP_HOST'].'\'">';
	
	// Генерация страницы
	$tpl->assign(array(
			"PAGE_TITLE" => "Панель управления",
			"PAGE_ENCODING" => $_SESSION['lang_encoding'],
			"WINDOW_STATUS" => "Language: " . $_SESSION['lang_name'],
			"LOGOUT_BUTTON" => $logout,
			"SITE_BUTTON" => $site,
			"SITE_LINK" => $_SERVER['HTTP_HOST'],
		));
?>