<?php
	require_once ("./includes/common.php");
	require_once ("./includes/db_config.php");
	require_once (DATABASES_PATH . DB_TYPE);
	
	db_connect(DB_HOST, DB_LOGIN, DB_PASSWORD);
	db_select_db(DB_NAME);
?>