<?php
    set_time_limit(0);

    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    define("IN_SITEDRIVE", 1);
    
    # INCLUDES ##############################################################################
    
    require_once('./Classes/PHPExcel.php');
    require_once('./common.php');                           // SiteDrive API and initialize
    require_once('./admin/includes/config.php');            // Настройки SiteDrive
    require_once('./admin/includes/values.php');            // Работа с константами
    require_once('simple_html_dom.php');
    require_once('pclzip.lib.php');

    # VARS ##################################################################################


    # MAIN ##################################################################################

    // function cleanDir($dir) {
    //     $files = glob($dir."/*");
    //     $c = count($files);
    //     if (count($files) > 0) {
    //         foreach ($files as $file) {      
    //             if (file_exists($file)) {
    //                 unlink($file);
    //             }   
    //         }
    //     }
    // }

    //cleanDir("./files");
    $filename = "./files/1.html";

//    $content = file_get_contents($filename);
    // $content = preg_replace('#(<ul class="b-list b-list_theme_normal b-list_catalog-menu">.*</ul>)#sU', '', $content);  

 //   
//	$html = file_get_contents($filename);
    $html = file_get_html($filename);

    $html_array = $html->find('[class=product-tile-title]');
	
	$search = array ("'<script[^>]*?>.*?</script>'si",  // Вырезает javaScript
					 "'<[\/\!]*?[^<>]*?>'si",           // Вырезает HTML-теги
					 "'([\r\n])[\s]+'",                 // Вырезает пробельные символы
					 "'&(quot|#34);'i",                 // Заменяет HTML-сущности
					 "'&(amp|#38);'i",
					 "'&(lt|#60);'i",
					 "'&(gt|#62);'i",
					 "'&(nbsp|#160);'i",
					 "'&(iexcl|#161);'i",
					 "'&(cent|#162);'i",
					 "'&(pound|#163);'i",
					 "'&(copy|#169);'i",
					 "'&#(\d+);'e");                    // интерпретировать как php-код

	$replace = array ("",
					  "",
					  "\\1",
					  "\"",
					  "&",
					  "<",
					  ">",
					  " ",
					  chr(161),
					  chr(162),
					  chr(163),
					  chr(169),
					  "chr(\\1)");

    foreach($html_array as $value) {
		$value = str_replace("quot;", "", $value);
		$value = str_replace("&amp;", "", $value);
		$value = preg_replace($search, $replace, $value );
//        $value = strip_tags($value);
//        $value = str_replace("", "", $value);

        echo $value."<br>";
    }
                   
    $html->clear(); 
    unset($html);
?>
