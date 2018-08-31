<?php
	# DEFINES ############################################################################
	
	ob_start("ob_gzhandler", 9);
	
	require_once("./includes/auth.php");
	require_once("./includes/common.php");
	require_once("./includes/db_init.php");
	require_once(FASTTEMPLATES_PATH . "template.php");

	include("modules/".$_GET['type']."/fields.php");
	
	$tpl = new FastTemplate(TEMPLATES_PATH);
	
	$tpl->define(array(
			"dialog_content_edit" => "dialog_content_edit.tpl",
			"field" => "content_fields.tpl",
			"html_editor" => "html_editor.tpl",
		));

	# VARS ###############################################################################

	$page_id = 0;
	
	if ($_GET['btn_type'] == 0) {
		$cookie_value = "editContent('".$_GET['type']."', '".$_GET['tablename']."', '".$_GET['id']."');";
		setcookie("sd_content_edit_value", $cookie_value, time()+60*60*24*30, "", $_SERVER['HTTP_HOST']);
	} 
	
	# FUNCTIONS ##########################################################################

	function resizeProportional($srcW, $srcH, $dstW, $dstH) {
		$d = max($srcW/$dstW, $srcH/$dstH);
		$result[] = round($srcW/$d);
		$result[] = round($srcH/$d);
		return $result;
	}

	function displayTextbox($fieldName, $fieldValue, $params = '') {
		$input = "<input type='text' class=sd_textbox name='".$fieldName."' value='".$fieldValue."' ".$params.">";
		$GLOBALS['tpl']->assign("INPUT", $input);
	}

	function displayTextarea($fieldName, $fieldValue, $params = '') {
		$input = "<textarea rows='10' class=sd_txtarea name='".$fieldName."' ".$params.">".$fieldValue."</textarea>";
		$GLOBALS['tpl']->assign("INPUT", $input);
	}

	function displayHTMLEditor($fieldName, $fieldValue, $menuButtons) {
		global $tpl;
		$button_id = 1;
		
		$j = 1;
		
		$tpl->assign("FIELD_NAME", $fieldName);
		$tpl->assign("INNER_TEXT", modifyImagesPath(strip_quotes($fieldValue))); 
		
		$tpl->parse("INPUT", "html_editor");
	}

	function displayDateAndTime($fieldName, $fieldValue, $params = '') {
		if (empty($fieldValue)) $fieldValue = date("Y-m-d G:i:s");
		displayTextbox($fieldName, $fieldValue, $params);
	}

	function displayFile($fieldName, $fieldValue) {
		$input = "
		<table>
		<tr><td><img src='images/preview.gif' style='cursor:pointer' onClick=\"showPreview('".$fieldName."')\"></td>
			<td><input type='text' class=sd_textbox style='width:250px; margin: 0px 5px 0px 5px' name='".$fieldName."' value='".$fieldValue."' onChange='formChange()'></td>
			<td><input type=button value='...' class=sd_button style='width:30px' onClick=\"openDocWindow('".$fieldName."')\"></td>
		</tr>
		</table>
		";
		$GLOBALS['tpl']->assign("INPUT", $input);
	}

	function displayImage($fieldName, $fieldValue) {
		$input = '
		<table>
		<tr>
			<td><input type="text" class="sd_textbox" style="width:100px; margin: 0px 5px 0px 0px" name="'.$fieldName.'" value="'.$fieldValue.'" onChange="formChange()" onkeydown="return onlyDigits(window)" maxlength="5"></td>
			<td><input type="button" value="..." title="'.LANG_HINT_SELECTIMAGE.'" class="sd_button" style="width:30px" onClick="button_image(\''.$fieldName.'\', \'id\', '.$fieldName.'.value)"></td>
			<td style="padding-left: 5px;"><img src="images/preview.gif" style="cursor:pointer" alt="'.LANG_HINT_PREVIEW.'" onClick="showPopupImage(\''.session_id(SESSION_ID).'\', '.$fieldName.'.value)"></td>
		</tr>
		</table>
		';
		$GLOBALS['tpl']->assign("INPUT", $input);
	}
  
	function displayImagePreview($fieldName, $fieldValue) {
		if (!empty($fieldValue)) $info = getImage($fieldValue);	else $info = array();
		
		$input = '
		<table width="100%" cellpadding="0" cellspacing="0"><tr>
		<td width="165" valign="top">
			<table cellpadding="2" cellspacing="0"><tr>
				<td><input type="text" class="sd_textbox" style="width:100px; margin: 0px 5px 0px 0px" name="'.$fieldName.'" value="'.$fieldValue.'" id="'.$fieldName.'" maxlength="5"></td>
				<td><input type="button" title="'.LANG_HINT_SELECTIMAGE.'" value="..." class="sd_button" style="width:30px" onClick="selectImage(\''.$fieldName.'\');"></td>
				<td style="padding-left: 5px;"><img src="images/preview.gif" style="cursor:pointer;" alt="'.LANG_HINT_PREVIEW.'" onClick="showPopupImage(\''.session_id(SESSION_ID).'\', '.$fieldName.'.value)"></td>
			</tr></table>
		</td>';
		
		if ($info['url'] != '') {
			$filename = PREVIEWS_PATH.$info['url'];
			if (!file_exists($filename)) {
				$filename = IMAGES_PATH.$info['url'];
				
				if ($info['width'] > $info['height']) {
					$width = 170;
					$height = 140;
				} else {
					$width = 140;
					$height = 170;
				}
				
				$sizes = resizeProportional($info['width'], $info['height'], $width, $height);
				$width = $sizes[0];
				$height = $sizes[1];
				
				$thumb = 'без названия';
			} else {
				$width = $info['thumb_width'];
				$height = $info['thumb_height'];
				$thumb = LANG_YES_TITLE;
			}
			
			$size = filesize(IMAGES_PATH.$info['url']);
			if ($size > 1024) {
				$size = $size / 1024;
				$size = number_format($size, 1, '.', '');
			}
			
			if (strtoupper($info['type']) != 'SWF') {
				$image = '<img src="'.$filename.'" width="'.$width.'" height="'.$height.'" style="cursor:pointer; border: 1px solid #316AC5;" alt="'.LANG_HINT_PREVIEW.'" onClick="showPopupImage(\''.session_id(SESSION_ID).'\', '.$fieldName.'.value)">';
			} else {
				$image = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="'.$width.'" height="'.$height.'">
				<param name="movie" value="'.$filename.'">
			    <param name="quality" value="high">
		    	<param name="menu" value="false">
			    <embed src="'.$filename.'" menu="false" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="'.$width.'" height="'.$height.'"></embed>
				</object>';
			}
			
			$input.= '<td align="right" valign="top">{CONTENT}</td>
			<td align="left" valign="top" width="100" style="padding-left: 10px;">
				<b>миниатюра:</b> '.$thumb.'<br>
				ширина: '.$info['width'].' px<br>
				высота: '.$info['height'].' px<br>
				размер: '.$size.' kb<br>
				тип: '.$info['type'].'
			</td>
			</tr></table>';
			
			$input = str_replace("{CONTENT}", $image, $input);
		} else {
			$input.= '</tr></table>';
		}
		
		$GLOBALS['tpl']->assign("INPUT", $input);
	}

	function displayExternalTable($fieldName, $fieldValue, $fieldParams) {
		$values = array();
		
		if ($fieldParams['External table'][3] != '') {
			$fieldParams['External table'][3] = 'where '.$fieldParams['External table'][3];
		} else {
			$fieldParams['External table'][3] = 'where lang_id = '.$_SESSION['lang_id'];
		}
		
		if (isset($fieldParams['External table'][4])) {
			$fieldParams['External table'][4] = 'order by '.$fieldParams['External table'][4];
		}
		
		if ($fieldParams['Read only'] == true) $disabled = "disabled"; else $disabled = "";
		
		$result = db_query(
			"SELECT 
				".$fieldParams['External table'][1].", 
				".$fieldParams['External table'][2]." 
			FROM 
				".$fieldParams['External table'][0]."
				".$fieldParams['External table'][3]."
				".$fieldParams['External table'][4]."
				");
		while ($row = db_fetch_array($result)) {
			$values[$row[0]] = $row[1];
		}

		$GLOBALS['tpl']->assign("INPUT", arrayToSelect($fieldName, $values, $fieldValue, $disabled));
	}

	function displayArray($fieldName, $fieldValue, $fieldParams) {
		if ($fieldParams['Read only'] == true) $disabled = "disabled"; else $disabled = "";
		$GLOBALS['tpl']->assign("INPUT", arrayToSelect($fieldName, $fieldParams['Values'], $fieldValue, $disabled));
	}
    
	function arrayToSelect($name, $array, $selected, $disabled = '') {
		$select = '<select name="'.$name.'" class="sd_textbox" '.$disabled.'>';
		$select.= '<option value="0"> - </option>';
		
		if (count($array)) {
			foreach ($array as $index => $value) {
				if ($index == $selected) $s = 'selected'; else $s = '';
				$select.= '<option value="'.$index.'" '.$s.'>'.$value.'</option>';
			}
		}
		$select.= "</select>";
		return $select;
	}

	function displayCheckbox($fieldName, $fieldValue, $disabled = false) {
		$value = abs(intval($fieldValue));
		if ($value == 0) $sel = ''; else $sel = 'checked';
		if ($disabled == true) $disabled = 'disabled'; else $disabled = '';
		$input = "<input type='checkbox' name='".$fieldName."' value='".$fieldValue."' ".$disabled." ".$sel.">";
		$GLOBALS['tpl']->assign("INPUT", $input);
	}
	
	function displayLinkTable($fieldName, $fieldValue, $fieldParams) {
		if ($fieldParams['External table'][0] != '') {
			$out = '<table id="grid-selection" class="table table-condensed table-hover table-striped">';
			
			if (is_file(MODULES_PATH . $fieldParams['External table'][1] . "/fields.php")) {
				require_once(MODULES_PATH . $fieldParams['External table'][1] . "/fields.php");
				
				$out .= '<thead><tr>';
				foreach ($fields as $linkfieldName => $linkfieldParams) {
					if ($linkfieldParams["Display in grid"]) {
						$out .= '<th data-column-id="'.$linkfieldName.'">'.$linkfieldParams['Title'][$_SESSION['lang_id']].'</th>';	
					}
				}
				$out .= '<th data-column-id="'.$linkfieldName.'"></th>';	
				$out .= '</tr></thead>';
				
				// проверяем на наличие таблицы в базе, если ее нет, то создаем
				if (!isTable($fieldParams['External table'][0])) {
					$filename = MODULES_PATH . $fieldParams['External table'][1] . "/sql.php";
					$file = fopen($filename, 'r');
					$sql = fread($file, filesize($filename));
					fclose($file);
					db_query($sql);
				}
				
				// выводим данные
				$sql = "SELECT * FROM ".$fieldParams['External table'][0]." WHERE page_id = ".$_GET['id']." AND lang_id = ".$_SESSION['lang_id'];
				$result = db_query($sql);
				if (db_num_rows($result) > 0) {
					while ($row = db_fetch_array($result)) {
						$out .= '<tbody><tr>';
						
						foreach ($fields as $linkfieldName => $linkfieldParams) {
							if ($linkfieldParams["Display in grid"]) {
						
								if ($linkfieldParams['Field type'] == 'date') {
									$str = $row[$linkfieldName];
								} else {
									if ($linkfieldParams['Field type'] == 'checkbox') {
										if ($row[$linkfieldName] == 1) $str = '<span class="center"><img src="images/checkbox_on.gif" width="13" height="11"></span>';
											else $str = '<span class="center"><img src="images/checkbox_off.gif" width="11" height="11" align="center"></span>';
									} else {
										$str = strip_tags($row[$linkfieldName]);
										$encoding = mb_detect_encoding($str);
										if (mb_strlen($str, $encoding) > 200) {
											$str = mb_substr($str, 0, 200, $encoding);
											$str.= "...";
										}
									}
								}
								
								$str = removeBreaks($str);
								
								$out .= '<td style="cursor: pointer;" ondblclick="editExternalContent(\''.$fieldParams['External table'][1].'\', \''.$fieldParams['External table'][0].'\', \''.$row['id'].'\');">'.$str.'</td>';
							}
						}
						
						$out .= '<td style="cursor: pointer;"><a href="javascript:void(0);" onclick="deleteLinkElement(\''.$fieldParams['External table'][0].'\', \''.$row['id'].'\', \''.$_GET['type'].'\', \''.$_GET['tablename'].'\', \''.$_GET['id'].'\');">Удалить</a></td>';
						$out .= '</tr></tbody>';
					}						
				}
			}
			
			$out .= '</table>';
			
			$out .= '<input type="button" class="sd_button" value="Добавить" style="width: 100px" onclick="createExternalContent(\''.$fieldParams['External table'][1].'\', \''.$fieldParams['External table'][0].'\', \''.$_GET['id'].'\');">';
			
			$GLOBALS['tpl']->assign("INPUT", $out);
		}
	}

	# MAIN #################################################################################
	
	$id = 0;
	if (isset($_GET['id'])) $id = $_GET['id'];
		else if (isset($row_id)) $id = $row_id;
	
	if ($id > 0) {
		$result = db_query("SELECT * FROM ".$_GET['tablename']." WHERE id = ".$id." LIMIT 1");
		if (db_num_rows($result) > 0) {
			$row = db_fetch_array($result);
		}
	}
	
	foreach ($fields as $fieldName => $fieldParams) {
		if ($fieldParams['Display in form'] == false) {
			continue;
		}
		
		$tpl->assign("LABEL", $fieldParams['Title'][$_SESSION['lang_id']]);
		
		if ($fieldParams['Read only'] == true) $params = 'readonly="true"';
			else $params = '';
		
		if (isset($row[$fieldName])) $fieldValue = $row[$fieldName];
			else $fieldValue = '';
		
		$content .= $fieldName ." => ".str_replace("\n", "", $fieldValue)."\n";
		
		switch ($fieldParams['Field type']) {
			case 'datetime':
				displayDateAndTime($fieldName, $fieldValue, $params);
				break;
			case 'textbox':
				displayTextbox($fieldName, $fieldValue, $params);
				break;
			case 'textarea':
				displayTextarea($fieldName, $fieldValue, $params);
				break;
			case 'html':
				displayHTMLEditor($fieldName, $fieldValue, $html_buttons);
				break;
			case 'file':
				displayFile($fieldName, $fieldValue);
				break;
			case 'image':
				displayImage($fieldName, $fieldValue);
				break;
			case 'image_preview':
				displayImagePreview($fieldName, $fieldValue);
				break;
			case 'external_table':
				displayExternalTable($fieldName, $fieldValue, $fieldParams);
				break;
			case 'array':
				displayArray($fieldName, $fieldValue, $fieldParams);
				break;
			case 'checkbox':
				displayCheckbox($fieldName, $fieldValue, $params);
				break;
			case 'linkfield':
				displayLinkTable($fieldName, $fieldValue, $fieldParams);
				break;
		}

		$tpl->assign("FIELD_NAME", $fieldName);
		$tpl->parse("FIELDS", ".field");
	}	
	
	$tpl->assign("ROW_ID", $id);
	$tpl->assign("ROW_PAGE_ID", $_GET['page_id']);
	$tpl->assign("ROW_TABLE_NAME", $_GET['tablename']);
	$tpl->assign("ROW_TYPE", $_GET['type']);
	$tpl->parse("FINAL", "dialog_content_edit");
	$tpl->FastPrint();
?>