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

    $api = 'http://mini.s-shot.ru/1280/1280/jpeg/?';
    $archive = new PclZip("archive.zip");
    $dir = './../photos/';
    $out = '';

    # MAIN ##################################################################################

    db_connect(DB_HOST, DB_LOGIN, DB_PASSWORD);
    db_select_db(DB_NAME);

    db_query("TRUNCATE TABLE catalog");

    $elements = db_get_array("SELECT name, rrc FROM module_elements", "rrc", "name");

    $i = 0;
    $result = db_query("SELECT * FROM pages WHERE parent_id = 4 AND visible = 1 AND deleted = 0 ORDER BY sortfield");
    if (db_num_rows($result) > 0) {
        while ($row = db_fetch_array($result)) {
           
            $result2 = db_query("SELECT * FROM pages WHERE parent_id = ".$row['id']." AND visible = 1 AND deleted = 0 ORDER BY sortfield");
            if (db_num_rows($result2) > 0) {
                while ($row2 = db_fetch_array($result2)) {

                    $result3 = db_query("SELECT * FROM module_catalog WHERE page_id = ".$row2['id']." ORDER BY sortfield");
                    if (db_num_rows($result3) > 0) {
                        while ($row3 = db_fetch_array($result3)) {
                            $params = db_get_data("SELECT * FROM module_parse WHERE id = ".$row3['param']);

                            $filename = "files/".$row3['id'].".html";

                            $html = file_get_html($filename);  

                            $k = 0;
                            $v = 0;
                            
                            $html_array = $html->find('[class='.$row3['class_name'].']');
                            $html_array = array_unique($html_array);

                            foreach ($html_array as $e) { 
                                $price_array = $html->find('[class='.$row3['class_price'].']');
                                $url_array = $html->find('[class='.$row3['class_link'].']');
                                $title_val = trim(strip_tags($e));
                                $encoding = mb_detect_encoding($title_val);

                                foreach ($elements as $key => $value) {
                                    $str_pos = strpos($title_val, $value);
                                    if ($str_pos > 0) {
                                        $rrc = $key;
                                        $title = $value;
                                        break;
                                    } else {
                                        $rrc = '';
                                        $title = $title_val;
                                    }
                                }

                                $price = '';
                                $url = '';

                                if ($params['url_type'] == 1) {
                                    $url = $params['domen'].$url_array[$k]->href;
                                } else {
                                    $url = $url_array[$k]->href;
                                }

                                if ($params['title_clean'] == 1) {
                                    $nbsp_pos = mb_strpos($title, "&nbsp;", $mb_detect_encoding) + 6;
                                    $title = mb_substr($title, $nbsp_pos, mb_strlen($title, $encoding), $encoding);
                                }

                                if ($params['price_cut_type1'] == 1) {
                                    $price = strip_tags($price_array[$k]);
                                    $price = intval(str_replace(" ", "", $price));
                                } 

                                if ($params['price_cut_type2'] == 1) {
                                    $price_array_val = explode("<li>", $price_array[$k]);
                                    $price = strip_tags($price_array_val[1]);

                                    $price = str_replace("Цена по прайсу", "", $price);
                                    $price = str_replace("₸", "", $price);
                                    $price = str_replace(" ", "", $price);
                                }

                                if ($params['param_parse'] == 1) {
                                    $start = strpos($html, "var array = [];");
                                    $str = substr($html, $start, strlen($html));
                                    $stop = strpos($str, "</script>");
                                    $str = substr($str, 0, $stop);

                                    $items = explode("array.push", $str);
                                    
                                    foreach ($items as $key => $value) {
                                        $title_pos = strpos($value, $title);

                                        if ($title_pos > 0) {
                                            $price_str_start = strpos($value, "unit_price");
                                            $price_str = substr($value, $price_str_start, strlen($value));
                                            $price_str_stop = strpos($price_str, ",");
                                            $price_str = substr($price_str, 0, $price_str_stop);

                                            $price = str_replace('"', "", $price_str);
                                            $price = str_replace('unit_price:', "", $price);
                                        }
                                    }
                                }

                                if ($title != "") {
                                    $sql = "INSERT INTO catalog SET date = NOW(), type_id = ".$row['id'].", shop_id = ".$row2['id'].", title = '".$title."', rrc = '".$rrc."', price = '".$price."', vendor = ".$row3['vendor'].", url = '".$url."'";
                                    db_query($sql);

                                    $k++;
                                }
                            }          
                        }
                    } 

                    $i++; 
                }
            }

        }
    }

    $out = '<table border="1" cellspacing="2" cellpadding="2" width="100%">';
    $out .= '<tr>
                <td><b>Дата</b></td>
                <td><b>Время</b></td>
                <td><b>Партнер</b></td>
                <td><b>Город</b></td>
                <td><b>Производитель</b></td>
                <td><b>Группа товаров</b></td>
                <td><b>Модель</b></td>
                <td><b>РРЦ</b></td>
                <td><b>Цена на сайте партнера</b></td>
            </tr>';

    $result = db_query("SELECT * FROM catalog");
    if (db_num_rows($result) > 0) {
        while ($row = db_fetch_array($result)) {
            $group_name = getPageTitle($row['type_id']);
            $partner = getPageTitle($row['shop_id']);
            $date = date("d.m.Y", strtotime($row['date']));
            $time = date("H:i", strtotime($row['date']));
            $vendor = db_get_data("SELECT name FROM module_vendors WHERE id = ".$row['vendor'], "name");

            if ($row['rrc'] != "" && $row['rrc'] != $row['price']) {
                $screen_name = $group_name."_".$partner."_".$row['title']."_".date("d m Y h:i:s");

                @$fp = fopen('photos/'.$screen_name.'.jpg', 'w'); 
                @fwrite($fp, file_get_contents($api.$row['url'])); 
                @fclose($fp);
            }

            $out .= '<tr>
                        <td>'.$date.'</td>
                        <td>'.$time.'</td>
                        <td>'.$partner.'</td>
                        <td>Город</td>
                        <td>'.$vendor.'</td>
                        <td>'.$group_name.'</td>
                        <td>'.$row['title'].'</td>
                        <td>'.$row['rrc'].'</td>
                        <td>'.$row['price'].'</td>
                    </tr>'; 
        }
    }

    $out .= '</table>';

    // создаем архив скриншотов для отправки по почте
    $list = $archive->create('photos');

    echo $out;

    $fp = fopen("file.html", "w");
    fwrite($fp, $out);
    fclose($fp);

    $inputFileType = 'HTML';
    $inputFileName = 'file.html';
    $outputFileType = 'Excel2007';
    $outputFileName = 'file.xlsx';
 
    $objPHPExcelReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objPHPExcelReader->load($inputFileName);

    $objPHPExcelWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,$outputFileType);
    $objPHPExcel = $objPHPExcelWriter->save($outputFileName);
?>
