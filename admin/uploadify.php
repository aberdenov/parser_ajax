<?php	
	ob_start("ob_gzhandler", 9);
	
	require_once("./includes/common.php");
	require_once("./includes/db_init.php");
	
	# VARS ##################################################################################
	
	$fileTypes = array('jpg', 'jpeg', 'gif', 'png'); 
	
	# FUNCTIONS #############################################################################
	
	function getFilename($fname, $ext = '') {
		if ($ext == '') $extension = getFileExt($fname); else $extension = $ext;
		$i = 1;
		$newFileName = $i.".".$extension;
		
		while (is_file(IMAGES_PATH.$i.".".strtolower($extension)) || is_file(IMAGES_PATH.$i.".".strtoupper($extension))) {
			$i++;
			$newFileName = $i.".".$extension;
		}
		return $newFileName;
	}
	
	function getFileExt($fname) {
		$path_parts = pathinfo($fname);
		if (is_array($path_parts)) {
			return $path_parts["extension"];
		}
	}
	
	# MAIN ##################################################################################
	
	if ($_POST['group_id'] > 0) $group_id = $_POST['group_id'];
		else $group_id = 1;
		
	$verifyToken = md5('unique_salt' . $_POST['timestamp']);
	
	if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
		
		// Проверяем допустимый тип файла и присваиваем правильное расширение
		$fileParts = pathinfo($_FILES['Filedata']['name']);
		if (in_array($fileParts['extension'], $fileTypes)) {
			$filename = $_FILES['Filedata']['name'];
			$tmp_filename = $_FILES['Filedata']['tmp_name'];
			$newFileName = getFilename($filename, $fileParts['extension']);
			
			$ret = copy($tmp_filename, IMAGES_PATH.strip_slashes($newFileName));
			
			$info = getimagesize($tmp_filename);
			$sourceWidth = $info[0];
			$sourceHeight = $info[1];
			
			db_query("INSERT INTO images SET group_id = ".$group_id.",
											 type = '".$fileParts['extension']."',
											 title = '".add_slashes($filename)."',
											 url = '".add_slashes($newFileName)."',
											 width = ".abs($sourceWidth).",
											 height = ".abs($sourceHeight).",
											 thumb_width = 0,
											 thumb_height = 0");
		} else {
			echo 'Invalid file type.';
		}
	}
?>