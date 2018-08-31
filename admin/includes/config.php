<?php
	# Конфигурационный файл SiteDrive #######################################################
	
	define('CONFIG_CACHE_ENABLED', false);				// Режим кэширования (вкл, выкл)
	define('CONFIG_CACHE_COMPRESSION', true);			// Использовать GZip компресиию для кэширования
	
	define('CONFIG_GZIP_ENABLED', true);				// Режим компрессии (вкл, выкл) GZip для всего сайта
	
	define('CONFIG_DEVELOPER_IP', '212.13.136.2');		// Сообщения trace() выводятся только для CONFIG_DEVELOPER_IP
	
	define('CONFIG_UPLOAD_FILESIZE', 52428800); 			// Максимально допустимый размер файла для закачки (5 мб)
	
	define("CONFIG_ALLOWED_IPMASK", "212.13.136");		// Разрешенные маски IP для входа в адм. часть

	define('CONFIG_SQL_LOGGING', true);
?>