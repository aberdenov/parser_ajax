<?php
// SiteDrive Installation module /////////////////////////////////////////////////////////////

require_once('includes/common.php');
require_once("./includes/common.php");
require_once(FASTTEMPLATES_PATH."class.FastTemplate.php");

define("DB_CONFIG_FILE", "./includes/db_config.php");
define("SD_CONFIG_FILE", "./includes/config.php");

$tpl = new FastTemplate(TEMPLATES_PATH);

$tpl->define(array(
		"page" => "page.tpl",
		"content" => "install_content.tpl"
	  ));

$msg = '';
$button = '';
$db_type = '';
$db_host = '';
$db_name = '';
$db_login = '';
$db_password = '';
$admin_login = '';

# ТАБЛИЦЫ ПО УМОЛЧАНИЮ #######################################################################

	// Кэш страниц
	$sql[] = "CREATE TABLE cache (
 				id bigint(20) unsigned NOT NULL auto_increment,
  				page_id int(10) unsigned NOT NULL default '0',
  				lang_id int(10) unsigned NOT NULL default '0',
  				group_id int(10) unsigned NOT NULL default '0',
  				url varchar(255) NOT NULL default '',
  				content longtext NOT NULL,
  				compressed tinyint(1) unsigned NOT NULL default '0',
  				timestamp int(11) default NULL,
  				PRIMARY KEY (`id`) 
			 )";

	// Группы изображений
	$sql[] = "CREATE TABLE image_groups (
				id int(10) unsigned NOT NULL auto_increment,
				name varchar(255) NOT NULL default '',
				PRIMARY KEY (id) 
			 )";

	// Изображения
	$sql[] = "CREATE TABLE images (
				id int(10) unsigned not null auto_increment,
				group_id int(10) unsigned not null default '0',
				page_id int(10) not null default '0',
				title varchar(255) not null,
				type varchar(10) not null,
				url varchar(255) not null,
				width int(10) unsigned not null default '0',
				height int(10) unsigned not null default '0',
				thumb_width int(10) unsigned not null default '0',
				thumb_height int(10) unsigned not null default '0',
				PRIMARY KEY (id) 
			 )";

	// Языки
	$sql[] = "CREATE TABLE languages (
				id int(10) unsigned NOT NULL auto_increment,
				name varchar(15) NOT NULL default '',
				file varchar(30) NOT NULL default '',
				encoding varchar(30) NOT NULL default '',
				main int(1) NOT NULL default '0',
				blocked int(1) NOT NULL default '0',
				PRIMARY KEY (id) 
			 )";

	// Группы страниц
	$sql[] = "CREATE TABLE page_groups (
				id int(10) unsigned NOT NULL auto_increment,
				name varchar(255) NOT NULL default '',
				PRIMARY KEY (id) 
			 )";

	// Страницы
	$sql[] = "CREATE TABLE pages (
				id int(10) unsigned NOT NULL auto_increment,
				parent_id int(10) unsigned NOT NULL default '0',
				lang_id int(4) unsigned NOT NULL default '0',
				type varchar(15) NOT NULL default '',
				group_id int(10) unsigned NOT NULL default '0',
				title varchar(255) NOT NULL default '',
				description varchar(255) NOT NULL default '',
				content text NOT NULL,
				template varchar(30) NOT NULL default '',
				icon int(10) unsigned NOT NULL default '0',
				external_link varchar(255) NOT NULL default '',
				sortfield int(10) unsigned NOT NULL default '0',
				visible tinyint(4) unsigned NOT NULL default '1',
				deleted tinyint(4) unsigned NOT NULL default '0',
				startpage tinyint(4) unsigned NOT NULL default '0',
				sort_by varchar(50) NOT NULL default '',
				sort_order tinyint(4) unsigned NOT NULL default '0',
				auth tinyint(4) unsigned NOT NULL default '0',
				PRIMARY KEY (id),
				KEY lang (lang_id) 
			 )";

	// Пользователи - Справочник
	$sql[] = "CREATE TABLE users (
				id int(10) unsigned NOT NULL auto_increment,
			    lang_id int(10) unsigned NOT NULL default '0',
				date datetime NOT NULL default '0000-00-00 00:00:00',
				login varchar(20) NOT NULL default '',
				password varchar(50) NOT NULL default '',
   		        group_id int(10) unsigned NOT NULL default '0',
  				full_name varchar(255) NOT NULL default '',
  				description varchar(255) NOT NULL default '',
  				ip int(11) NOT NULL default '0',
  				active tinyint(1) unsigned NOT NULL default '0',
				allow_read tinyint(1) unsigned NOT NULL default '0',
  				allow_write tinyint(1) unsigned NOT NULL default '0',
  				allow_create tinyint(1) unsigned NOT NULL default '0',
				PRIMARY KEY (id) 
			 )";

	// Пользователи - Журнал событий
	$sql[] = "CREATE TABLE users_actions_log (
	  			id int(10) unsigned NOT NULL auto_increment,
  				user_id int(10) unsigned NOT NULL default '0',
  				lang_id int(10) unsigned NOT NULL default '0',
  				user_login varchar(15) NOT NULL default '',
  				date datetime NOT NULL default '0000-00-00 00:00:00',
				chapter_name tinytext NOT NULL,
  				action tinytext NOT NULL,
  				PRIMARY KEY  (id) 
			 )";

	// Пользователи - Группы
	$sql[] = "CREATE TABLE users_groups (
	  			id int(10) unsigned NOT NULL auto_increment,
  				group_name varchar(100) NOT NULL default '',
  				allow_read tinyint(1) unsigned NOT NULL default '0',
  				allow_write tinyint(1) unsigned NOT NULL default '0',
  				allow_create tinyint(1) unsigned NOT NULL default '0',
				PRIMARY KEY (id) 
			 )";

	// Пользователи - Модули
	$sql[] = "CREATE TABLE users_modules (
	  			id int(10) unsigned NOT NULL auto_increment,
  				menu_id int(10) unsigned NOT NULL default '0',
  				caption varchar(255) NOT NULL default '',
  				action varchar(255) NOT NULL default '',
  				image varchar(255) NOT NULL default '',
				image_over varchar(255) NOT NULL default '',
				dialog_name varchar(255) NOT NULL default '',
				win_name varchar(100) NOT NULL default '',
				win_height int(10) unsigned NOT NULL default '0',
				win_width int(10) unsigned NOT NULL default '0',
				scrollable tinyint(1) unsigned NOT NULL default '0',
				resizable tinyint(1) unsigned NOT NULL default '0',
				hint varchar(100) NOT NULL default '',
				priv tinyint(1) unsigned NOT NULL default '0',
				active tinyint(1) unsigned NOT NULL default '0',
				sortfield int(10) unsigned NOT NULL default '0',
				PRIMARY KEY (id) 
			 )";

	// Пользователи - Права на модули
	$sql[] = "CREATE TABLE users_modules_priv (
			  	id int(10) unsigned NOT NULL auto_increment,
				module int(10) NOT NULL default '0',
  				user_id int(10) NOT NULL default '0',
				PRIMARY KEY (id) 
			 )";

	// Пользователи - Права на страницы
	$sql[] = "CREATE TABLE users_pages_priv (
	  			id int(10) unsigned NOT NULL auto_increment,
  				user_id int(10) unsigned NOT NULL default '0',
  				page_id int(10) unsigned NOT NULL default '0',
  				allow_read tinyint(1) unsigned NOT NULL default '0',
  				allow_write tinyint(1) unsigned NOT NULL default '0',
  				allow_create tinyint(1) unsigned NOT NULL default '0',
				PRIMARY KEY (id) 
			 )";

	// Опросы - Справочник
	$sql[] = "CREATE TABLE polls (
	  			id int(10) unsigned NOT NULL auto_increment,
  				page_id int(10) unsigned NOT NULL default '0',
				lang_id int(10) unsigned NOT NULL default '0',
  				start_date datetime NOT NULL default '0000-00-00 00:00:00',
				end_date datetime NOT NULL default '0000-00-00 00:00:00',
				title varchar(255) NOT NULL default '',
				description varchar(255) NOT NULL default '',
				active tinyint(1) unsigned NOT NULL default '1',
				PRIMARY KEY (id) 
			 )";

	// Опросы - Варианты ответов
	$sql[] = "CREATE TABLE poll_variants (
	  			id int(10) unsigned NOT NULL auto_increment,
  				poll_id int(10) unsigned NOT NULL default '0',
				title varchar(255) NOT NULL default '',
				result int(10) unsigned NOT NULL default '0',
				PRIMARY KEY (id) 
			 )";

	// Статистика - Поисковые слова
	$sql[] = "CREATE TABLE stat_keywords (
				id int(10) unsigned NOT NULL auto_increment,
  				lang_id int(10) unsigned NOT NULL default '0',
  				date datetime NOT NULL default '0000-00-00 00:00:00',
				keyword varchar(255) NOT NULL default '',
  				count int(10) unsigned NOT NULL default '0',
				PRIMARY KEY (id) 
			 )";

	// Статистика -  Конфигурация
	$sql[] = "CREATE TABLE stat_config (
				allow_stat tinyint(1) unsigned NOT NULL default '1',
				allow_keywords tinyint(1) unsigned NOT NULL default '0',
  				allow_referers tinyint(1) unsigned NOT NULL default '0',
  				start_date date NOT NULL default '0000-00-00',
				expire_time int(10) unsigned NOT NULL default '0' 
			 )";

	// Статистика -  Счетчик посещений
	$sql[] = "CREATE TABLE stat_counter (
	  			total_hosts int(10) unsigned NOT NULL default '0',
  				total_hits int(10) unsigned NOT NULL default '0',
  				day_hosts int(10) unsigned NOT NULL default '0',
				day_hits int(10) unsigned NOT NULL default '0',
				day_stamp int(10) unsigned NOT NULL default '0' 
			 )";

	// Статистика -  Исключенные IP адреса
	$sql[] = "CREATE TABLE stat_exclude (
				id int(10) unsigned NOT NULL auto_increment,
  				ip int(10) NOT NULL default '0',
  				PRIMARY KEY (id) 
		     )";

	// Статистика -  Журнал посещений
	$sql[] = "CREATE TABLE stat_log (
	  			id bigint(20) unsigned NOT NULL auto_increment,
  				user_ip int(11) NOT NULL default '0',
				proxy_ip int(11) NOT NULL default '0',
  				date int(10) unsigned default '0',
  				page_id mediumint(8) unsigned NOT NULL default '0',
			  	lang_id tinyint(1) unsigned NOT NULL default '0',
  				country tinyint(4) unsigned NOT NULL default '0',
				uniq tinyint(1) unsigned NOT NULL default '0',
  				PRIMARY KEY (id) 
			 )";

	// Статистика -  Обратные ссылки
	$sql[] = "CREATE TABLE stat_referers (
				id int(10) unsigned NOT NULL auto_increment,
  				date int(10) unsigned NOT NULL default '0',
  				referer varchar(255) NOT NULL default '',
                                count int(10) unsigned default NULL,
                                self int(10) NOT NULL,
				PRIMARY KEY (id) 
			 )";

	// Настройки
	$sql[] = "CREATE TABLE module_values (
				id INT UNSIGNED NOT NULL AUTO_INCREMENT,
				page_id INT UNSIGNED NOT NULL,
				lang_id INT UNSIGNED NOT NULL,
				group_id INT UNSIGNED NOT NULL,
				name VARCHAR( 255 ) NOT NULL ,
				description VARCHAR( 255 ) ,
				value TEXT,
				sortfield int(10) unsigned NOT NULL default '0' ,
				PRIMARY KEY ( id )  
			 )";

##############################################################################################
# УСТАНОВКА 																				  
##############################################################################################

if (isset($HTTP_POST_VARS['start_install'])) {
	$db_type = $HTTP_POST_VARS['db_type'];
	$db_host = $HTTP_POST_VARS['db_host'];
	$db_name = $HTTP_POST_VARS['db_name'];
	$db_login = $HTTP_POST_VARS['db_login'];
	$db_password = $HTTP_POST_VARS['db_password'];
	$admin_login = $HTTP_POST_VARS['admin_login'];
	
	// Создаем файл конфигурации БД
	$file = fopen(DB_CONFIG_FILE, 'w+');
	fwrite($file, "<?php\n");
	fwrite($file, 'define("DB_TYPE", "'.$db_type."\");\n");
	fwrite($file, 'define("DB_HOST", "'.$db_host."\");\n");
	fwrite($file, 'define("DB_NAME", "'.$db_name."\");\n");
	fwrite($file, 'define("DB_LOGIN", "'.$db_login."\");\n");
	fwrite($file, 'define("DB_PASSWORD", "'.$db_password."\");\n");
	fwrite($file, "?>\n");
	fclose($file);
	
	require_once(DATABASES_PATH.$db_type);
	
	// Проверяем соединение с базой
	$result = db_check_connection($db_host, $db_login, $db_password, $db_name);
	if ($result == 0) {
		// Приступаем к установке
		require_once ("./includes/db_init.php");
		
		// Создаем таблицы
		$is_complete = true;
		//if ($_POST['lang_encoding'] != '') $charset = $_POST['lang_encoding']; else $charset = 'latin1';
		
		foreach($sql as $query) {
			
			$query.= ' ENGINE=MyISAM  DEFAULT CHARSET=utf8';
			
			if(!db_query_ex($query)) {
				$msg = '<div id="warning"><b>Ошибка в SQL запросе или попытка создания существующей таблицы.</b></div>';
				$is_complete = false;
				break;
			}
		}
		
		// Записываем настройки по умолчанию
		if ($is_complete) {
			$login = makeSafeString($HTTP_POST_VARS['admin_login'], 20);
			$password = makeSafeString($HTTP_POST_VARS['admin_password'], 50);
			$ip = ip2long(get_ip());
			
			// Создаем пользователя
			db_query("INSERT INTO users VALUES ('', 1, NOW(), '".$login."', MD5('".$password."'), 1, '', '', '0', 1, 1, 1, 1)");
			db_query("INSERT INTO users_groups VALUES 
						(1, 'Administrators', 1, 1, 1),
						(2, 'Power User', 1, 0, 0),
						(3, 'Users', 0, 0, 0)"
					);
			
			db_query("INSERT INTO users_modules VALUES 
						(1, 1, '', '', 'menu_users.gif', '', 'dialog_users.php', 'users', 600, 700, 0, 1, 'LANG_USERS', 2, 1, 0),
						(2, 1, '', '', 'menu_lang.gif', '', 'dialog_languages.php', 'languages', 600, 500, 1, 1, 'LANG_LANGUAGES', 2, 1, 0),
						(3, 1, '', '', 'menu_images.gif', '', 'dialog_images.php', 'images', 600, 770, 1, 0, 'LANG_IMAGES', 1, 1, 0),
						(4, 1, '', '', 'menu_poll.gif', '', 'dialog_poll.php', 'poll', 600, 770, 1, 0, 'LANG_POLL', 1, 1, 0),
						(5, 1, '', '', 'menu_statistic.gif', '', 'dialog_statistic.php', 'statistics', 600, 770, 1, 0, 'LANG_STATISTICS', 1, 1, 0),
						(6, 1, '', '', 'menu_download.gif', '', 'dialog_filemanager.php', 'filemanager', 600, 770, 0, 0, 'LANG_FILEMANAGER', 1, 1, 0),
						(7, 1, '', '', 'mail_send.gif', '', 'dialog_mail_send.php', 'mails', 600, 770, 1, 0, 'LANG_MAILER', 1, 1, 0),
						(8, 1, '', '', 'menu_control.gif', '', '', 'settings', 600, 770, 1, 0, 'LANG_SETTINGS', 2, 0, 0),
						(9, 1, 'LANG_HELP', '', 'menu_help.gif', '', '', 'help', 600, 770, 1, 0, 'LANG_HELP', 0, 0, 0),
						(10, 1, '', 'window.document.frmLangSwitch.uncache.value=1; window.document.frmLangSwitch.submit();', 'menu_uncache.gif', '', '', '', 0, 0, 0, 0, 'LANG_UNCACHE', 0, 1, 0),
						(11, 2, 'LANG_ADD', '', 'users/user_add.png', '', 'dialog_users_add.php', 'users', 640, 500, 1, 1, 'LANG_ADDUSER', 2, 1, 0),
						(12, 2, 'LANG_DELETE', 'tp.command_user_delete(tp.user_ID);', 'users/user_delete.png', '', '', '', 0, 0, 0, 0, 'LANG_DELETE_USER', 2, 1, 0),
						(13, 2, 'LANG_EDIT', 'if (tp.user_ID != 0) {\r\n tp.command_user_edit(tp.user_ID);\r\n }\r\n else \r\n {\r\n tp.users_add_open(); \r\n}', 'users/user_edit.png', '', '', '', 0, 0, 0, 0, 'LANG_EDIT_USER', 2, 1, 0),
						(14, 2, 'LANG_LOCK', 'if (tp.user_ID != 0) {\r\n tp.command_user_lock(tp.user_ID);\r\n alert(tp.lang_msg_lockUser); \r\n}', 'users/user_hold18x18.png', '', '', '', 0, 0, 0, 0, 'LANG_LOCK_USER', 2, 1, 0),
						(15, 2, 'LANG_UNLOCK', 'if (tp.user_ID != 0) {\r\n tp.command_user_unlock(tp.user_ID);\r\n alert(tp.lang_msg_unlockUser); \r\n}', 'users/user.png', '', '', '', 0, 0, 0, 0, 'LANG_UNLOCK_USER', 2, 1, 0),
						(16, 3, '', 'doSave();', 'save_btn18x18.gif', '', '', '', 0, 0, 0, 0, 'LANG_SAVE', 0, 1, 0),
						(17, 3, '', 'doFullscreen();', 'fullscreen_btn18x18.gif', '', '', '', 0, 0, 0, 0, 'LANG_FULLSCREEN', 0, 1, 0),
						(18, 3, '', 'self.showHelp(\'help/dialog_content_edit.html\');', 'help_btn18x18.gif', '', '', '', 0, 0, 0, 0, 'LANG_HELP', 0, 1, 0),
						(19, 1, '', 'showAbout();', 'menu_about.gif', '', '', '', 0, 0, 0, 0, 'LANG_ABOUT', 0, 0, 0),
						(20, 1, '', '', 'action_log.gif', '', 'dialog_action.php', 'action', '600', '770', 1, 0, 'LANG_ACTION_LOG', 1, 1, 0),
						(21, 1, '', '', 'menu_trash.gif', '', 'dialog_deleted.php', 'deleted', 600, 700, 1, 1, 'LANG_TRASH', 2, 1, 0),
						(22, 3, '', 'doRestore();', 'restore.gif', '', '', '', 0, 0, 1, 0, 'LANG_BUFFER', 0, 1, 0)"
					);
			
			db_query("INSERT INTO languages VALUES ('', '".$HTTP_POST_VARS['lang_name']."', '".$HTTP_POST_VARS['lang_pack']."', '".$HTTP_POST_VARS['lang_encoding']."', 1, 0)");
			db_query("INSERT INTO stat_config SET allow_stat = 0, allow_keywords = 0, allow_referers = 0, expire_time = 0");
			db_query("INSERT INTO stat_counter SET total_hosts = 0, total_hits = 0, day_hosts = 0, day_hits = 0, day_stamp = 0");
			db_query("INSERT INTO image_groups SET name = 'Main'");
			
			// Добавляем системную страницу - Настройки
			db_query("INSERT INTO pages SET lang_id = 1, type = 'settings', description = '@settings_folder', sortfield = 10000, visible = 1, title = 'РќР°СЃС‚СЂРѕР№РєРё'");
			$settings_id = db_insert_id();
			
			// Добавляем системные страницы с настройками
			if ($settings_id > 0) {
				db_query("INSERT INTO pages SET parent_id = ".$settings_id.", lang_id = 1, type = 'values', description = '@settings_global', sortfield = 10001, visible = 1, title = 'РћР±С‰РёРµ'");
				$insert_id = db_insert_id();
				
				db_query("INSERT INTO pages SET parent_id = ".$settings_id.", lang_id = 1, type = 'values', description = '@settings_labels', sortfield = 10002, visible = 1, title = 'РћС„РѕСЂРјР»РµРЅРёРµ'");
				
				// Добавляем стандартные настройки
				if ($insert_id > 0) {
					db_query("INSERT INTO module_values SET page_id = ".$insert_id.", lang_id = 1, name = 'SITE_WINDOW_TITLE', description = 'Р—Р°РіРѕР»РѕРІРѕРє РѕРєРЅР°'");
					db_query("INSERT INTO module_values SET page_id = ".$insert_id.", lang_id = 1, name = 'META_TAGS', description = 'РљР»СЋС‡РµРІС‹Рµ СЃР»РѕРІР° РґР»СЏ РїРѕРёСЃРєРѕРІС‹С… СЃРёСЃС‚РµРј'");
					db_query("INSERT INTO module_values SET page_id = ".$insert_id.", lang_id = 1, name = 'META_DESCRIPTION', description = 'РћРїРёСЃР°РЅРёРµ СЃР°Р№С‚Р° РІ РїРѕРёСЃРєРѕРІРѕР№ СЃРёСЃС‚РµРјРµ'");
					db_query("INSERT INTO module_values SET page_id = ".$insert_id.", lang_id = 1, name = 'COPYRIGHT_INFO', description = 'РљРѕРїРёСЂР°Р№С‚С‹'");
					db_query("INSERT INTO module_values SET page_id = ".$insert_id.", lang_id = 1, name = 'ADMIN_EMAIL', description = 'РџРѕС‡С‚РѕРІС‹Р№ Р°РґСЂРµСЃ Р°РґРјРёРЅРёСЃС‚СЂР°С‚РѕСЂР°'");
					db_query("INSERT INTO module_values SET page_id = ".$insert_id.", lang_id = 1, name = 'SITE_EMAIL_SUBJECT', description = 'РўРµРјР° РїРёСЃСЊРјР° СЃ СЃР°Р№С‚Р°'");
					db_query("INSERT INTO module_values SET page_id = ".$insert_id.", lang_id = 1, name = 'SITE_EMAIL_SIGNATURE', description = 'РџРѕРґРїРёСЃСЊ РїРёСЃСЊРјР° СЃ СЃР°Р№С‚Р°'");
				}
			}
			
			header("Location: login.php?complete");
		}
	} elseif ($result == -1) {
		$msg = '<div id="warning"><b>Ошибка!</b> Невозможно установить соединение с базой.</div>';
		$db_host = '';
		$db_password = '';
		$db_login = '';
	} elseif ($result == -2) {
		$msg = '<div id="warning"><b>Ошибка!</b> База данных не существует или недоступна.</div>';
		$db_name = '';
	}
}

##############################################################################################
# MAIN                                                                                        
##############################################################################################

// Формируем список файлов для работы с БД
$db_types = '';
if ($db_array = getDirFiles(DATABASES_PATH))
foreach ($db_array as $i => $name) {
	$db_types .= "<option>".$name."</option>";
}

// Формируем список языковых пакетов
$languages = '';
if ($lang_array = getDirFiles(LANGUAGES_PATH))
foreach ($lang_array as $i => $name) {
	if ($name == 'russian_utf-8.php') $selected = 'selected'; else $selected = '';
		$languages .= "<option ".$selected.">".$name."</option>";
	}

$tpl->assign(array(
		"DB_TYPES" => $db_types,
		"LANGUAGES" => $languages,
		"DB_HOST" => $_SERVER["SERVER_ADDR"]
));

// Проверяем возможна ли запись в папку includes
if (!is_writable("./includes/")) {
	$msg = '<div id="warning"><b>Внимание!</b> Для продолжения установки, папка <b>includes</b> должна быть доступной для записи.</div>';
	$button = 'disabled';
}

$tpl->parse("PAGE_CONTENT", "content");

$head = "<script language='JavaScript' src='./includes/script.js'></script>";

$tpl->assign(array(
			"PAGE_TITLE" => "Установка",
			"PAGE_HEAD" => $head,
			"SCRIPT" => "var tp = window",
			"PAGE_ENCODING" => "windows-1251",
			"WINDOW_STATUS" => "",
			"MAIN_MENU" => "",
			"LOGOUT_BUTTON" => "",
			"LANG_ON_SITE" => "",
			"BTN_STATE" => $button,
			"ERR_MSG" => $msg,
			"DB_HOST" => $db_host,
			"DB_LOGIN" => $db_login,
			"DB_PASSWORD" => $db_password,
			"DB_NAME" => $db_name,
			"ADMIN_LOGIN" => $admin_login,
			"SITE_BUTTON" => "",
		));

$tpl->parse("FINAL", "page");
$tpl->FastPrint();
?>

