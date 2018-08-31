<?php	
	ob_start("ob_gzhandler", 9);
	
	require_once("./includes/common.php");
	require_once("./includes/db_init.php");
	
	# MAIN ##################################################################################
	
	$data = '';
	$result = db_query("SELECT title, url FROM images ORDER BY id DESC");
	if (db_num_rows($result) > 0) {
		while ($row = db_fetch_array($result)) {
			$data[] = array('title' => $row['title'], 'value' => 'http://'.SITE_HOST.'/admin/'.IMAGES_PATH.$row['url']);
		}
	}
	
	print_r(json_encode($data));
?>