<?php
    set_time_limit(0);

    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('memory_limit', '256M');

    define("IN_SITEDRIVE", 1);
    
    # INCLUDES ##############################################################################
    
    require_once('./Classes/PHPExcel.php');
    require_once('./common.php');                           // SiteDrive API and initialize
    require_once('./admin/includes/config.php');            // Настройки SiteDrive
    require_once('./admin/includes/values.php');            // Работа с константами
    require_once('simple_html_dom.php');
    require_once('pclzip.lib.php');

    # MAIN ##################################################################################

    // db_connect(DB_HOST, DB_LOGIN, DB_PASSWORD);
    // db_select_db(DB_NAME);

    // $out = '<html xmlns:o="urn:schemas-microsoft-com:office:office"
    //             xmlns:x="urn:schemas-microsoft-com:office:excel"
    //             xmlns="http://www.w3.org/TR/REC-html40">
    //         <head>
    //         <table border="1" cellspacing="2" cellpadding="2" width="100%">';
    // $out .= '<tr>
    //             <td><b>Id</b></td>
    //             <td><b>Дата</b></td>
    //             <td><b>Время</b></td>
    //             <td><b>Партнер</b></td>
    //             <td><b>Город</b></td>
    //             <td><b>Производитель</b></td>
    //             <td><b>Группа товаров</b></td>
    //             <td><b>Модель</b></td>
    //             <td><b>РРЦ</b></td>
    //             <td><b>Цена на сайте партнера</b></td>
    //             <td></td>
    //         </tr>';
    
    // $result = db_query("SELECT * FROM catalog");
    // if (db_num_rows($result) > 0) {
    //     while ($row = db_fetch_array($result)) {
    //         if ($row['title'] != "По возрастанию цены" && 
    //             $row['title'] != "Без группировки" && 
    //             $row['title'] != "без группировки" &&
    //             $row['title'] != "Все" && 
    //             $row['title'] != "В магазинах и под заказ" &&
    //             $row['title'] != "Ноутбуки и планшеты" &&
    //             $row['title'] != "Компьютеры и периферия" &&
    //             $row['title'] != "Комплектующие для ПК" &&
    //             $row['title'] != "Телефоны и смарт-часы" &&
    //             $row['title'] != "Телевизоры и медиа" &&
    //             $row['title'] != "Игры и приставки" &&
    //             $row['title'] != "Аудиотехника" &&
    //             $row['title'] != "Фото-видеоаппаратура" &&
    //             $row['title'] != "Офисная техника и мебель" &&
    //             $row['title'] != "Сетевое оборудование" &&
    //             $row['title'] != "Сеть и коммутация" &&
    //             $row['title'] != "Автотовары" &&
    //             $row['title'] != "Крупная бытовая техника" &&
    //             $row['title'] != "Товары для кухни" &&
    //             $row['title'] != "Красота и здоровье" &&
    //             $row['title'] != "Товары для дома" &&
    //             $row['title'] != "Инструменты" &&
    //             $row['title'] != "Одежда и обувь" &&
    //             $row['title'] != "По наличию" &&
    //             $row['title'] != "по наличию" &&
    //             $row['title'] != "Услуги" &&
    //             $row['title'] != ""  
    //         ) {
    //             $group_name = getPageTitle($row['type_id']);
    //             $partner = getPageTitle($row['shop_id']);
    //             $date = date("d.m.Y", strtotime($row['date']));
    //             $time = date("H:i", strtotime($row['date']));
    //             $vendor = db_get_data("SELECT name FROM module_vendors WHERE id = ".$row['vendor'], "name");
    //             $city = db_get_data("SELECT name FROM module_city WHERE id = ".$row['city_id'], "name");

    //             if ($row['rrc'] != "" && intval($row['rrc']) > intval($row['price'])) {
    //                 $status = 'Несоответствие';
    //                 $screen_name = $group_name."_".$partner."_".$row['title']."_".date("d m Y h:i:s");

    //                 @$fp = fopen('photos/'.$screen_name.'.jpg', 'w'); 
    //                 @fwrite($fp, file_get_contents($api.$row['url'])); 
    //                 @fclose($fp);
    //             } else {
    //                 $status = '';
    //             }

    //             $out .= '<tr>
    //                         <td>'.$row['id'].'</td>
    //                         <td>'.$date.'</td>
    //                         <td>'.$time.'</td>
    //                         <td>'.$partner.'</td>
    //                         <td>'.$city.'</td>
    //                         <td>'.$vendor.'</td>
    //                         <td>'.$group_name.'</td>
    //                         <td>'.$row['title'].'</td>
    //                         <td>'.$row['rrc'].'</td>
    //                         <td>'.$row['price'].'</td>
    //                         <td>'.$status.'</td>
    //                     </tr>'; 
    //         }
    //     }
    // }

    // $out .= '</table></body></html>';

    // $fp = fopen("file.html", "w");
    // fwrite($fp, $out);
    // fclose($fp);

    $inputFileType = 'HTML';
    $inputFileName = 'file.html';
    $outputFileType = 'Excel5';
    $outputFileName = 'file.xls';

    // echo 0;    

    // $objPHPExcelReader = PHPExcel_IOFactory::createReader($inputFileType);
    // $objPHPExcel = $objPHPExcelReader->load($inputFileName);

    // echo 1;

    // $cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
    // $cacheSettings = array('memoryCacheSize' => '256MB');
    // PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

    // echo 2;

    // $objPHPExcelWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $outputFileType);
    // $objPHPExcel = $objPHPExcelWriter->save($outputFileName);
?>
