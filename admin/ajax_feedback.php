<?php
	define("IN_SITEDRIVE", 1);
	
	require_once("./includes/auth.php");
	require_once("./includes/db_init.php");
	require_once("./includes/common.php");
	require_once("./includes/class.ModuleSettings.php");
	require_once(FASTTEMPLATES_PATH . "template.php"); 
	
	# VARS ##################################################################################

	$res = 0;

	# MAIN ##################################################################################
	
	if (isset($_POST['type'])) {
		if ($_POST['type'] == "html-request") {
			// получаем список содержимого раздела
			if ($_POST['action'] == 1) {
				$out = '<table id="grid-selection" class="table table-condensed table-hover table-striped">';
				
				$page_id = intval($_POST['page_id']);
				$moduleType = db_get_data("SELECT type FROM pages WHERE id = ".$page_id." LIMIT 1", "type");
				$tableName = "module_".$moduleType;
				$moduleName = $moduleType;
				
				if (is_file(MODULES_PATH . $moduleName . "/fields.php")) {
					$i = 1;
					require_once(MODULES_PATH . $moduleName . "/fields.php");
					
					$out .= '<thead><tr>';
					foreach ($fields as $fieldName => $fieldParams) {
						if ($fieldParams["Display in grid"]) {
							$out .= '<th data-column-id="'.$fieldName.'">'.$fieldParams['Title'][$_SESSION['lang_id']].'</th>';	
						}
					}
					$out .= '</tr></thead>';
					
					// проверяем на наличие таблицы в базе, если ее нет, то создаем
					if (!isTable($tableName)) {
						$filename = MODULES_PATH . $moduleName . "/sql.php";
						$file = fopen($filename, 'r');
						$sql = fread($file, filesize($filename));
						fclose($file);
						db_query($sql);
					}
					
					// выводим данные
					$sql = "SELECT * FROM ".$tableName." WHERE page_id = ".$page_id." AND lang_id = ".$_SESSION['lang_id'];
					$result = db_query($sql);
					if (db_num_rows($result) > 0) {
						while ($row = db_fetch_array($result)) {
							$out .= '<tbody><tr>';
							
							foreach ($fields as $fieldName => $fieldParams) {
								if ($fieldParams["Display in grid"]) {
							
									if ($fieldParams['Field type'] == 'date') {
										$str = $row[$fieldName];
									} else {
										if ($fieldParams['Field type'] == 'checkbox') {
											if ($row[$fieldName] == 1) $str = '<span class="center"><img src="http://'.$_SERVER['HTTP_HOST'].'/admin/images/checkbox_on.gif" width="13" height="11"></span>';
												else $str = '<span class="center"><img src="http://'.$_SERVER['HTTP_HOST'].'/admin/images/checkbox_off.gif" width="11" height="11"></span>';
										} else {
											$str = strip_tags($row[$fieldName]);
											$encoding = mb_detect_encoding($str);
											if (mb_strlen($str, $encoding) > 200) {
												$str = mb_substr($str, 0, 200, $encoding);
												$str.= "...";
											}
										}
									}
									
									$str = removeBreaks($str);
									
									$out .= '<td style="cursor: pointer;" onmouseover="selectGridId(\''.$row['id'].'\', \''.$tableName.'\');" ondblclick="editContent(\''.$moduleName.'\', \''.$tableName.'\', \''.$row['id'].'\');">'.$str.'</td>';
								}
							}
							
							$out .= '</tr></tbody>';
						}						
					}
				}
				
				$out .= '</table>';
				
				print_r($out);
			}	
			
			// создаем страницу
			if ($_POST['action'] == 2) {
				parse_str($_POST['form'], $data);
				
				$data['type'] = add_slashes($data['type']);
				$data['mod_rewrite'] = add_slashes($data['mod_rewrite']);
				$data['title'] = add_slashes($data['title']);
				$data['description'] = add_slashes($data['description']);
				$data['kurs'] = add_slashes($data['kurs']);
				$data['content'] = add_slashes($data['content']);
				$data['external_link'] = add_slashes($data['external_link']);
				
				$visible = 1;
				// $visible = (integer)isset($data['visible']);
				$startpage = (integer)isset($data['start_page']);
				
				if (isset($data['start_page'])) {
					db_query("UPDATE pages SET startpage = 0 WHERE lang_id = ".$_SESSION['lang_id']);
				}

				$data['type'] = db_get_data("SELECT type FROM pages WHERE id = ".$data['parent_id']." LIMIT 1", "type");
				//if ($data['type'] == "") $data['type'] = 'catalog';
				
				$query = " INSERT INTO pages VALUES (
					'".$data['id']."',
					".$data['parent_id'].",
					".$_SESSION['lang_id'].",
					'".$data['type']."',
					0,
					'".$data['mod_rewrite']."',
					'".$data['title']."',
					'".$data['description']."',
					'".$data['kurs']."',
					'".$data['content']."',
					'".$data['template']."',
					0,
					'".$data['external_link']."',
					0,
					".$visible.",
					".intval($data['deleted']).",
					".$startpage.",
					'',
					0,
					0
					)";
				
				db_query($query);
				$insert_id = db_insert_id();
				
				$query = "UPDATE pages SET sortfield = id WHERE id = ".$insert_id;
				db_query($query);
				
				// Добавляем настройки модуля из XML файла
				$module_name = strtolower($_POST['content']);
				$module_name = str_replace("{", '', $module_name);
				$module_name = str_replace("}", '', $module_name);
				
				$settings_file = PAGE_MODULES_PATH.$module_name.'/settings.xml';
				if (ModuleSettings::loadXMLConfig($settings_file)) {
					ModuleSettings::addSettings($insert_id, $_SESSION['lang_id']);
				}
				
				// Создаем связанную с модулем таблицу если необходимо
				$table = "module_".$data['type'];
				if (!db_table_exists($table)) {
					$sql_file = MODULES_PATH.$data['type']."/sql.php";
					if (file_exists($sql_file)) {
						$sql = file_get_contents($sql_file);
						db_query($sql);
					}
				}
				
				// Создаем дополнительную таблицу если есть в fields.php флаг $linkField
				if (is_file(MODULES_PATH . $_POST['type'] . "/fields.php")) {
					require_once(MODULES_PATH . $_POST['type'] . "/fields.php");
					if (isset($linkField)) {
						for ($i = 0; $i <= count($linkField); $i++) {
							$link_table = "module_".$linkField[$i];
							if (!db_table_exists($link_table)) {
								$sql_link_file = MODULES_PATH.$linkField[$i]."/sql.php";
								if (file_exists($sql_link_file)) {
									$sql = file_get_contents($sql_link_file);
									db_query($sql);
								}
							}
						}
					}
				}
				
				db_query("INSERT INTO users_pages_priv VALUES ('', ".$_SESSION['user_ID'].", ".$insert_id.", 1, 1, 1)");
				
				print_r($insert_id);
			}
			
			// редактируем страницу
			if ($_POST['action'] == 3) {
				parse_str($_POST['form'], $data);
				
				$data['group'] = add_slashes($data['group']);
				$data['mod_rewrite'] = add_slashes($data['mod_rewrite']);
				$data['title'] = add_slashes($data['title']);
				$data['description'] = add_slashes($data['description']);
				$data['kurs'] = add_slashes($data['kurs']);
				$data['content'] = add_slashes($data['content']);
				
				if (isset($data['start_page'])) {
					db_query("UPDATE pages SET startpage = 0 WHERE lang_id = ".$_SESSION['lang_id']);
				}
				
				//if ($data['type'] == "") $data['type'] = 'catalog';

				$query = "UPDATE pages SET group_id = ".$data['group'];
				
				// if (isset($data['type'])) $query.= ", type = '".$data['type']."'";
				
				$query .= ", mod_rewrite = '".$data['mod_rewrite']."'
						  , title = '".$data['title']."'
						  , description = '".$data['description']."'
						  , kurs = '".$data['kurs']."'
						  , content = '".$data['content']."'
						  , template = '".$data['template']."'
						  , icon = '".$data['icon']."'
						  , external_link = '".$data['external_link']."'
						  , startpage = '".(integer)isset($data['start_page'])."'
						  , visible = '".(integer)isset($data['visible'])."'
						  , deleted = '".intval($data['deleted'])."'
						  , sort_by = '".$data['sort_by']."'
						  , sort_order = '".intval($data['sort_order'])."'
						  , auth = '".(integer)isset($data['auth'])."'
						  WHERE id = ".$data['page_id'];
						 
				db_query($query);
				
				// Добавляем настройки модуля из XML файла
				$module_name = strtolower($data['content']);
				$module_name = str_replace("{", '', $module_name);
				$module_name = str_replace("}", '', $module_name);
				
				$settings_file = PAGE_MODULES_PATH.$module_name.'/settings.xml';
				if (ModuleSettings::loadXMLConfig($settings_file)) {
					ModuleSettings::addSettings($page_id, $_SESSION['lang_id']);
				}
			}
			
			// Редактируем информацию в разделе
			if ($_POST['action'] == 4) {
				parse_str($_POST['form'], $data);
				
				include("modules/".$data['type']."/fields.php");
				
				if (isset($data['id'])) $row_id = $data['id']; else $row_id = $data['id'];
				
				if ($data['id'] == 0) {
					db_query("INSERT INTO ".$data['tablename']." SET page_id = ".$data['page_id'].", lang_id = ".$_SESSION['lang_id']);
					if (db_affected_rows() > 0) {
						$row_id = db_insert_id();
					}
				}
				
				if ($row_id > 0) {
					$query = "UPDATE ".$data['tablename']." SET ";
					$result = db_query("SELECT * FROM ".$data['tablename']." WHERE id = ".$row_id);
					$row = db_fetch_array($result);
					
					if (isset($row['page_id'])) {
						$page_id = $row['page_id'];
					}
					
					$i = 0;
					foreach ($fields as $fieldName => $fieldParams) {
						if ($fieldParams['Field type'] == 'checkbox') {
							if (isset($data[$fieldName])) $data[$fieldName] = 1; else $data[$fieldName] = 0;
						}
										
						if (isset($data[$fieldName]) && $fieldName != 'id') {
							if ($i > 0) $query.= ", ";
							
							switch($fieldName) {
								case 'name':  $title_field = 'name'; break;
								case 'value': $title_field = 'value'; break;
								case 'title': $title_field = 'title'; break;
							}
							
							$query.= $fieldName." = '".add_slashes($data[$fieldName])."'";
							$i++;
						}
					}
					
					$query.= " WHERE id = ".$row_id;
					db_query($query);
				}
				
				print_r($_COOKIE['sd_content_edit_value']);
			}
			
			// Получаем информацию о разделе
			if ($_POST['action'] == 5) {
				$info = db_get_data("SELECT * FROM pages WHERE id = ".$_POST['id']);
				
				print_r($info['type'].'#module_'.$info['type'].'#'.$info['id']);
			}
			
			// Добавить группу изображений
			if ($_POST['action'] == 6) {
				$result = db_query("INSERT INTO image_groups VALUES ('', '".add_slashes($_POST['var_value'])."')");
				
				print_r($_POST['menu_id']);
			}
			
			// Удалить группу изображений
			if ($_POST['action'] == 7) {
				db_query("DELETE FROM image_groups WHERE id = ".$_POST['var_value']." LIMIT 1");
				
				print_r($_POST['menu_id']);
			}
			
			// Удалить изображение
			if ($_POST['action'] == 8) {
				$result = db_query("SELECT id, url FROM images WHERE id = ".$_POST['image_id']);
				$row = db_fetch_object($result);
				unlink(IMAGES_PATH.$row->url);
				unlink(PREVIEWS_PATH.$row->url);
				db_query("DELETE FROM images WHERE id = ".$_POST['image_id']." LIMIT 1");
				
				print_r($_POST['menu_id']);
			}
			
			// Переименовать группу изображений
			if ($_POST['action'] == 9) {
				db_query("UPDATE image_groups SET name='".add_slashes($_POST['var_value'])."' WHERE id = ".$_POST['group_id']." LIMIT 1");
				
				print_r($_POST['menu_id']);
			}
			
			// Удалить элемент
			if ($_POST['action'] == 10) {
				db_query("DELETE FROM ".$_POST['table']." WHERE id = ".$_POST['id']." LIMIT 1");
				
				print_r($_POST['dialog_type']."#".$_POST['dialog_table']."#".$_POST['dialog_id']);
			}
			
			// Изображения в группе
			if ($_POST['action'] == 11) {
				$out = '<table cellpadding="5" cellspacing="0" border="0" width="600" align="center">
						<tr>
							<td class="sd_outset" style="border-left: 1px solid #808080; border-top: 1px solid #A6A6A6;"><b>ID</b></td>
							<td class="sd_outset" style="border-top: 1px solid #A6A6A6;"><b>Название</b></td>
							<td class="sd_outset" style="border-top: 1px solid #A6A6A6;"><b>Файл</b></td>
							<td class="sd_outset" style="border-top: 1px solid #A6A6A6;" width="30">&nbsp;</td>
						</tr>';
				
				$result = db_query("SELECT * FROM images WHERE group_id = ".$_POST['group_id']." ORDER BY id DESC");
				if (db_num_rows($result) > 0) {
					while ($row = db_fetch_array($result)) {				
						$out .= '<tr>
									<td style="border-left: 1px solid #808080; border-bottom: solid 1px #808080; background-color: #F8F8F8; padding: 5px"><b>'.$row['id'].'</b></td>
									<td style="border-bottom: solid 1px #808080; padding: 5px">'.$row['title'].'</td>
									<td style="border-bottom: solid 1px #808080; padding: 5px"><b>'.$row['url'].'</b></td>
									<td style="border-right: solid 1px #808080; border-bottom: solid 1px #808080; background-color: #F8F8F8; padding: 5px">
										<img src="images/gallery/insert_max.gif" width="16" height="16" hspace="2" border="0" onclick="selectImageId(\''.$row['id'].'\');" style="cursor: hand;" alt="Вставить изображение">
									</td>
								</tr>';
					}
				} 
				
				$out.= '</table>';
				
				print_r($out);
			}
			
			// Переместить страницу выше
			if ($_POST['action'] == 12) {
				$page_1 = db_get_data("SELECT title, parent_id, sortfield FROM pages WHERE id = ".$_POST['id']." LIMIT 1");
				$id_1 = $_POST['id'];
				
				$page_2 = db_get_data("SELECT id, sortfield FROM pages WHERE parent_id = ".$page_1['parent_id']." AND sortfield < ".$page_1['sortfield']." AND lang_id = ".$_SESSION['lang_id']." ORDER BY sortfield DESC LIMIT 1");
				$id_2 = $page_2['id'];
				
				db_query("UPDATE pages SET sortfield = ".$page_2['sortfield']." WHERE id = ".$id_1." LIMIT 1");
				db_query("UPDATE pages SET sortfield = ".$page_1['sortfield']." WHERE id = ".$id_2." LIMIT 1");
			}
			
			// Переместить страницу ниже
			if ($_POST['action'] == 13) {
				$page_1 = db_get_data("SELECT title, parent_id, sortfield FROM pages WHERE id = ".$_POST['id']." LIMIT 1");
				$id_1 = $_POST['id'];
					
				$page_2 = db_get_data("SELECT id, sortfield FROM pages WHERE parent_id = ".$page_1['parent_id']." AND sortfield > ".$page_1['sortfield']." AND lang_id = ".$_SESSION['lang_id']." ORDER BY sortfield LIMIT 1");
				$id_2 = $page_2['id'];
					
				db_query("UPDATE pages SET sortfield = ".$page_2['sortfield']." WHERE id = ".$id_1." LIMIT 1");
				db_query("UPDATE pages SET sortfield = ".$page_1['sortfield']." WHERE id = ".$id_2." LIMIT 1");
			}
			
			// Удалить контент
			if ($_POST['action'] == 14) {
				db_query("DELETE FROM ".$_POST['tablename']." WHERE id = ".$_POST['id']);
			}

			// Удалить пользователя
			if ($_POST['action'] == 15) {
				db_query("DELETE FROM users WHERE id = ".$_POST['id']);
				db_query("DELETE FROM users_modules_priv WHERE user_id = ".$_POST['id']);
			}

			// Смена пароль
			if ($_POST['action'] == 16) {
				db_query("UPDATE users SET password = md5('".$_POST['password']."') WHERE id = ".$_POST['id']);
			}

			// Удаление страницы
			if ($_POST['action'] == 17) {
				db_query("UPDATE pages SET deleted = 1 WHERE id = ".$_POST['id']);
			}

			// Копирование контента
			if ($_POST['action'] == 18) {
				$_SESSION['copyed_id'] = $_POST['id'];
				$_SESSION['copyed_page_id'] = $_POST['page_id'];
				$_SESSION['copyed_status'] = 'copy';
 			}

 			// Вырезание контента
			if ($_POST['action'] == 19) {
				$_SESSION['copyed_id'] = $_POST['id'];
				$_SESSION['copyed_page_id'] = $_POST['page_id'];
				$_SESSION['copyed_status'] = 'cut';
 			}

 			// Вставка контента
			if ($_POST['action'] == 20) {
				// Источник
				$sql = "SELECT lang_id, type FROM pages WHERE id = ".$_SESSION['copyed_page_id'];
				$result = db_query($sql);
				$row = db_fetch_array($result);  
				$source_type = "module_".$row['type'];		
				$source_lang = $row['lang_id'];
				db_free_result($result);
				
				// Назначение
				$sql = "SELECT lang_id, type FROM pages WHERE id = ".$_POST['page_id'];
				$result = db_query($sql);
				$row = db_fetch_array($result); 
				$dist_type = "module_".$row['type'];			 
				$dist_lang = $row['lang_id'];
				db_free_result($result);

				if ($_SESSION['copyed_status'] == "copy") {
					if ($source_type == $dist_type) {
						$result = db_query("SELECT * FROM ".$source_type." WHERE id = ".$_SESSION['copyed_id']);
						if (db_num_rows($result) > 0) {
							$fields = db_num_fields($result);
							
							while ($row = db_fetch_array($result)) {
								for ($i = 0; $i < $fields; $i++) {
									if ($i == 0) {
										$sql = "INSERT INTO ".$dist_type." SET ";
										$show_sql = $sql;
									}

									$field_name = db_field_name($result, $i);
												
									switch ($field_name) {
										case 'id':
											break;
										case 'date':
											$sql = 'NOW()';
											$show_sql .= $field_name." = NOW()";
											break;
										case 'page_id':
											$sql .= "'".$_POST['page_id']."'";
											$show_sql .= $field_name." = '".$_POST['page_id']."'";
											break;
										case 'lang_id':
											$sql .= "'".$dist_lang."'";
											$show_sql .= $field_name." = '".$dist_lang."'";
											break;
										case 'sortfield':	
											$sql .= "''";
											$show_sql .= $field_name." = ''";
											break;
										default:
											$sql .= "'".addslashes($row[$i])."'";
											$show_sql.= $field_name."='".addslashes($row[$i])."'";
									}
												
									if ($i < ($fields - 1) && $i != 0) $show_sql.= ", ";
								}
											
								db_query($show_sql);
							}
						}	
					}
				}

				if ($_SESSION['copyed_status'] == "cut") {
					if ($source_type == $dist_type) {
						$sql = "UPDATE ".$dist_type." SET page_id = ".$_POST['page_id']." WHERE id = ".$_SESSION['copyed_id']." LIMIT 1";
						db_query($sql);
					} 
				}

				print_r($_POST['page_id']);
 			}

 			// Копировать страницу
 			if ($_POST['action'] == 21) {
 				$_SESSION['copyed_id'] = $_POST['id'];
				$_SESSION['copyed_lang'] = $_SESSION['lang_id'];
				$_SESSION['action_paste'] = "page_copy";
 			}

 			// Вырезать страницу
 			if ($_POST['action'] == 22) {
 				$_SESSION['copyed_id'] = $_POST['id'];
				$_SESSION['copyed_lang'] = $_SESSION['lang_id'];
				$_SESSION['action_paste'] = "page_cut";
 			}

 			// Вставить страницу
 			if ($_POST['action'] == 23) {
 				$in_charset = db_get_data("SELECT encoding FROM languages WHERE id = ".$_SESSION['copyed_lang']." LIMIT 1", "encoding");
 				$out_charset = $_SESSION['lang_encoding'];

				if ($_SESSION['action_paste'] == "page_copy") {
					if (!in_chapter($_SESSION['copyed_id'], $_POST['id'])) {
						copyStruct($_SESSION['copyed_id'], $_POST['id'], $_SESSION['copyed_lang'], $_SESSION['lang_id'], 1, $in_charset, $out_charset);
					} 
					
					$_SESSION['action_paste'] = "";
				}

				if ($_SESSION['action_paste'] == "page_cut") {
					if ($_SESSION['copyed_id'] != $_POST['id'] ) {
						if ($_SESSION['copyed_lang'] == $_SESSION['lang_id']) {
							if (!in_chapter($_SESSION['copyed_id'], $_POST['id'])) {
								$sql = "UPDATE pages SET parent_id = ".$_POST['id']." WHERE id = ".$_SESSION['copyed_id']." AND lang_id = ".$_SESSION['lang_id'];
								db_query($sql);
							} 
						}
					}

					$_SESSION['action_paste'] = "";
				}
 			}
		}
	}
?>