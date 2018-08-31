<?php
	# DEFINES #######################################################################################
	define("IN_SITEDRIVE", 1);
	
	ob_start("ob_gzhandler", 9);
	
	require_once("./includes/auth.php");
	require_once("./includes/db_init.php");
	require_once("./includes/common.php");
	require_once(FASTTEMPLATES_PATH . "template.php");

	$tpl = new FastTemplate(TEMPLATES_PATH);

	$tpl->define(array(
			"page"          => "page.tpl",
			"main_content"  => "main_content.tpl",
			"main_menu"     => "main_menu.tpl",
			"lang_select"   => "lang_select.tpl",
//			"extended_menu" => "extended_menu.tpl",
		));

	# FUNCTIONS ##############################################################################

	/*function getMainMenuItem($i, $j) {
		$mainMenu = $GLOBALS['mainMenu'];
		$mainMenuN = $GLOBALS['mainMenuN'];
		return $mainMenu[($i-1)*$mainMenuN + $j - 1];
	}*/
	
	function build_tree($cats, $parent_id){
		global $tree;
		
		if (is_array($cats) and isset($cats[$parent_id])) {
			
			foreach($cats[$parent_id] as $cat) {
				$icon = '<img src=\"modules/'.$cat['type'].'/icon_small.gif\" width=\"16\" height=\"16\" class=\"im1\">';
				
                $tree .= '{';
				$tree .= 'text: "'.$icon.$cat['title'].' ['.$cat['id'].']",';
				$tree .= 'id: "'.$cat['id'].'",';
				$tree .= 'nodes: [';
				$tree .=  build_tree($cats, $cat['id']);				
				$tree .= '],';
				$tree .= 'state: {checked: false, disabled: false, expanded: false, selected: false},';
                $tree .= '},';
            }
		} 
	}

	# VARS ###################################################################################
	
	require_once("./control_menu.php");
	
	$tree = '';
	$languages = '';
	$mainMenuN = 5;
	$item_show = false;
	$moduls    = array();
	/*
	
	
	$head      = "<script language='JavaScript' src='./includes/script.js'></script>";
	$script    = "var tp = window;
				  var aboutResource = '".LANG_ABOUT_DIALOG_TEXT."';";
*/
	# IMPLEMENTATION #########################################################################
	
	// Выходим их системы
	if (isset($_GET['logout'])) {
		session_name('SID');
		session_start();
		session_unset();
		session_destroy();
		
		header("Location: login.php");
	}
	
	// Дерево разделов	
	$pages_array = '';
	$pages_priv_array = db_get_array("SELECT page_id FROM users_pages_priv WHERE user_id = ".$_SESSION['user_ID'], "page_id");

	$result = db_query("SELECT id, parent_id, title, type, sortfield, startpage, visible FROM pages WHERE lang_id = ".$_SESSION['lang_id']." AND deleted = 0 ORDER BY sortfield");
	if (db_num_rows($result) > 0) {
		while ($row = db_fetch_array($result)) {
		//	$page_title = strip_tags($row['title']);
		//	$page_title = choose($row['startpage']+1, $page_title, '<strong>'.$page_title.'</strong>');
		//	$page_title = choose($row['visible']+1, '<font color="#959595">'.$page_title.'</font>', $page_title);
			
			if ($row['type'] == 'catalog') {
				// if (in_array($row['id'], $pages_priv_array)) {
					$pages_array[$row['parent_id']][$row['id']] = $row;
				// }
			} else {
				$pages_array[$row['parent_id']][$row['id']] = $row;
			}
			
		}	
	}
	
	build_tree($pages_array, 0);
	$tpl->assign("MENU_LIST", $tree);
	
	
	//getPagesTree(0, $_SESSION['lang_id']);
	//echo $menu;
	if (isset($_POST['language'])) {
			$result = db_query("SELECT * FROM languages WHERE id = ".$_POST['language']." LIMIT 1");
			$row = db_fetch_object($result);
			$_SESSION['lang_id'] = $row->id;
			$_SESSION['lang_name'] = $row->name;
			$_SESSION['lang_file'] = $row->file;
			$_SESSION['lang_encoding'] = $row->encoding;
			header('Location: control.php');
	}
	
	$tpl->assign(array(
			"PAGE_TITLE" => "Панель управления",
			"PAGE_ENCODING" => $_SESSION['lang_encoding'],
			"LANG_ID" => $_SESSION['lang_id'],
			"WINDOW_STATUS" => "Language: " . $_SESSION['lang_name'],
			"LOGOUT_BUTTON" => $logout,
			"SITE_BUTTON" => $site,
			"SITE_LINK" => $_SERVER['HTTP_HOST'],
		));
	
	$tpl->parse("PAGE_CONTENT", "main_content");
	$tpl->parse("FINAL", "page");
	$tpl->FastPrint();
?>