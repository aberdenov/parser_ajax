<?php
	define('SESSION_ID', 'SID');
	if (isset($_GET['SID'])) session_id($_GET['SID']);
	
	session_name(SESSION_ID);
	session_start('SID');
	
	require_once ("./includes/db_init.php");
	
	function checkAuth() {
		if (!isset($_SESSION['login']) || !isset($_SESSION['password'])) return false;
		
		$result = db_query("SELECT * FROM users WHERE login = '".$_SESSION['login']."' AND password = MD5('".$_SESSION['password']."') LIMIT 1");
		if (db_num_rows($result) > 0) return true;
		
		return false;
	}
	
	if (!checkAuth()) {
		header("location: login.php?auth=1");
        exit;
	}
?>