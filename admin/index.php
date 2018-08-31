<?php
	if (is_file('./includes/db_config.php')) {
		header("Location: login.php");
	} else {
		if (is_file('./install.php')) header("Location: install.php");
			else header("Location: login.php");
	}
?> 