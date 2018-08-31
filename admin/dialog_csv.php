<?php
	# DEFINES ############################################################################
	
	set_time_limit(0);

	ob_start("ob_gzhandler", 9);
	
	require_once("./includes/auth.php");
	require_once("./includes/common.php");
	require_once("./includes/db_init.php");
	require_once("../class.Pages.php");
	require_once(FASTTEMPLATES_PATH . "template.php");
	
	$tpl = new FastTemplate(TEMPLATES_PATH);
	
	$tpl->define(array(
			"page" => "page.tpl",
			"dialog_csv" => "dialog_csv.tpl",
			"main_menu"     => "main_menu.tpl",
			"lang_select"   => "lang_select.tpl",
		));
		
	# DEFINES ################################################################################
	
	require_once("./control_menu.php");
	
	# FUNCTIONS #############################################################################

	$_dir = './upload/temp/';

	// Получаем тип файла
	function getFileExt($filename) {
		$path_parts = pathinfo($filename);
		if (is_array($path_parts)) {
			return $path_parts["extension"];
		}
	}

	// Копирует файл
	function copyFile($tmp_filename, $filename) {
		if (file_exists($filename)) $result = "Файл перезаписан"; else $result = "Файл успешно закачен";
		if (move_uploaded_file($tmp_filename, $filename)) return $result;
			else return "Файл не закачен";
	}

	function cleanDir($dir) {
        $files = glob($dir."/*");
        $c = count($files);
        if (count($files) > 0) {
            foreach ($files as $file) {      
                if (file_exists($file)) {
                    unlink($file);
                }   
            }
        }
    }

	# POST ##################################################################################

	if (isset($_POST['send'])) {
		db_query("TRUNCATE proxy");
		db_query("TRUNCATE module_proxy");

		// Загрузка файла во временную папку
		if ($_FILES['file']['name'] != '') {
			$filename = chop($_FILES['file']['name']);
			$tmp_filename = $_FILES['file']['tmp_name'];
			$size = abs($_FILES['file']['size']);

			$ext = strtolower(getFileExt($filename));
			if ($ext == "csv" && is_uploaded_file($tmp_filename) ) {
				if ($size != 0) {
					$filename = "proxy.csv";
					$res_msg = copyFile($tmp_filename, $_dir.$filename);

					// Разбор файла
					$handle = fopen($_dir.$filename, "r");
					
					while (!feof($handle)) {
						$buffer = fgets($handle, 16384);
						if ($buffer != "") {
							$sql = "INSERT INTO proxy SET proxy = '".trim($buffer)."', parsed = 0";
							db_query($sql);
						}
					}

					// очищаем временные папки
			        cleanDir("./upload/temp");

					header("Location: dialog_csv.php?result=3");
					exit;
				} else {
					header("Location: dialog_csv.php?result=1");
					exit;
				}
			} else {
				header("Location: dialog_csv.php?result=2");
				exit;
			}
		}
	}

	# MAIN ##################################################################################
	
	// Отображаем сообщение о проделанной операции
	if (isset($_GET['result'])) {
		switch ($_GET['result']) {
			case 1: $tpl->assign("RESULT_MESSAGE", '<div class="result_success">Неверный размер файла</div>'); break;
			case 2: $tpl->assign("RESULT_MESSAGE", '<div class="result_success">Неверный тип файла</div>'); break;
			case 3: $tpl->assign("RESULT_MESSAGE", '<div class="result_success">Файл успешно загружен</div>'); break;
			default: $tpl->assign("RESULT_MESSAGE", '');
		}

		$tpl->assign("FUNCTION", 'startParse();');
	} else {
		$tpl->assign("RESULT_MESSAGE", '');
		$tpl->assign("FUNCTION", '');
	}

	$tpl->parse("PAGE_CONTENT", "dialog_csv");
	$tpl->parse("FINAL", "page");
	$tpl->FastPrint();
?>