<?php
    set_time_limit(0);

    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    define("IN_SITEDRIVE", 1);
    
    # INCLUDES ##############################################################################
    
    require_once('/var/www/vhosts/sidelka-galina.kz/parser.trainspotting.kz/common.php');                           // SiteDrive API and initialize
    require_once('/var/www/vhosts/sidelka-galina.kz/parser.trainspotting.kz/admin/includes/config.php');            // Настройки SiteDrive
    require_once('/var/www/vhosts/sidelka-galina.kz/parser.trainspotting.kz/admin/includes/values.php');            // Работа с константами
    require_once('/var/www/vhosts/sidelka-galina.kz/parser.trainspotting.kz/simple_html_dom.php');
    require_once('/var/www/vhosts/sidelka-galina.kz/parser.trainspotting.kz/pclzip.lib.php');
    require_once('/var/www/vhosts/sidelka-galina.kz/parser.trainspotting.kz/Classes/PHPExcel.php');

    include "/var/www/vhosts/sidelka-galina.kz/parser.trainspotting.kz/Snoopy.class.php";

    # VARS ##################################################################################

    $api = 'http://mini.s-shot.ru/1280/1280/jpeg/?';
    $archive = new PclZip("archive.zip");
    $dir = '/var/www/vhosts/sidelka-galina.kz/parser.trainspotting.kz/photos/';
    $out = '';

    # MAIN ##################################################################################

    db_connect(DB_HOST, DB_LOGIN, DB_PASSWORD);
    db_select_db(DB_NAME);

    // получаем выбранный шаблон
    // прикрутить выборку на отмеченные шаблоны
    $tpl_result = db_query("SELECT * FROM templates");
    if (db_num_rows($tpl_result) > 0) {
        while ($tpl_row = db_fetch_array($tpl_result)) {
            // подготавливаем структуру ссылок
            db_query("TRUNCATE links_cron");
            db_query("TRUNCATE TABLE catalog_cron");

            $where = 'WHERE lang_id = 1';

            // Производители
            $vendors = explode(";", $tpl_row['brend_val']);
            $vendors = array_unique($vendors);
            $vendors_count = count($vendors) - 1;
            $i = 0;

            if ($vendors_count > 0) {
                $where .= ' AND (';

                foreach ($vendors as $key => $value) {
                    if ($value != "") {
                        $i++;

                        $where .= 'vendor = '.str_replace("vendors_", "", $value);

                        if ($i != $vendors_count) $where .= ' OR ';
                    }
                }

                $where .= ')';
            } 

            // магазины
            $shops = explode(";", $tpl_row['shop_val']);
            $shops = array_unique($shops);
            $page_array = '';
            $i = 0;

            foreach ($shops as $key => $value) {
                if (getFirstChildID(str_replace("shops_", "", $value)) > 0) {
                    getPages(str_replace("shops_", "", $value));
                } else {
                    $page_array[] .= str_replace("shops_", "", $value);
                }
            }

            $shops_count = count($page_array) - 1;

            if ($shops_count > 0) {
                $where .= ' AND (';

                foreach ($page_array as $key => $value) {
                    if ($value != "") {
                        $i++;

                        $where .= 'page_id = '.str_replace("shops_", "", $value);

                        if ($i != $shops_count) $where .= ' OR ';
                    }
                }

                $where .= ')';
            }

            // города
            $cities = '';
            $cities_str_array = explode(";", $tpl_row['city_val']);
            $cities_str_array = array_unique($cities_str_array);
            foreach ($cities_str_array as $key => $value) {
                $value = str_replace("city_", "", $value);

                $cities[] .= db_get_data("SELECT value FROM module_city WHERE id = ".$value, "value");
            }

            $result = db_query("SELECT * FROM module_catalog ".$where);
            if (db_num_rows($result) > 0) {
                while ($row = db_fetch_array($result)) {
                    $params = db_get_data("SELECT * FROM module_parse WHERE id = ".$row['param']);

                    if ($params['cookie_val'] != "") {
                        $result2 = db_query("SELECT * FROM module_city WHERE page_id = ".$params['city']);
                        if (db_num_rows($result2) > 0) {
                            while ($row2 = db_fetch_array($result2)) {
                                if (in_array($row2['value'], $cities)) {
                                    $sql = "INSERT INTO links_cron SET 
                                                        page_id = ".$row['page_id'].", 
                                                        link = '".$row['url']."', 
                                                        city_id = '".$row2['id']."', 
                                                        city_val = '".$row2['value']."', 
                                                        cookie_val = '".$params['cookie_val']."', 
                                                        big_cookie = '".$params['big_cookie']."',
                                                        big_cookie_start = '".$params['big_cookie_start']."',
                                                        big_cookie_end = '".$params['big_cookie_end']."',
                                                        checked = 0,
                                                        parsed = 0, 
                                                        param = ".$row['param'].",
                                                        sleep = ".$params['sleep'].",
                                                        use_proxy = ".$params['use_proxy'].",
                                                        user_agent = ".$params['user_agent'].",
                                                        snoopy = ".$params['snoopy'].",
                                                        vendor = ".$row['vendor'].",
                                                        class_name = '".$row['class_name']."',
                                                        class_price = '".$row['class_price']."',
                                                        class_link = '".$row['class_link']."'";
                                    db_query($sql);
                                }                                                       
                            }
                        }
                    } else {
                        if ($params['link_parse'] > 0) {
                            if ($params['link_parse'] == 1) {
                                $pos1 = strrpos($row['url'], "www");
                                $protocol = substr($row['url'], 0, $pos1);
                                $url_str = substr($row['url'], $pos1, strlen($row['url']));

                                $pos2 = strpos($url_str, "/");
                                $domain = substr($url_str, 0, $pos2);
                                $url_param = substr($url_str, $pos2, strlen($url_str));

                                $result2 = db_query("SELECT * FROM module_city WHERE page_id = ".$params['city']);
                                if (db_num_rows($result2) > 0) {
                                    while ($row2 = db_fetch_array($result2)) {
                                        if (in_array($row2['value'], $cities)) {
                                            if ($row2['value'] != "default") {
                                                $new_url = $protocol.$domain."/".$row2['value'].$url_param;
                                            } else {
                                                $new_url = $row['url'];
                                            }                                            

                                            $sql = "INSERT INTO links_cron SET 
                                                            page_id = ".$row['page_id'].", 
                                                            link = '".$new_url."', 
                                                            city_id = '".$row2['id']."', 
                                                            city_val = '".$row2['value']."', 
                                                            cookie_val = '".$params['cookie_val']."', 
                                                            big_cookie = '".$params['big_cookie']."',
                                                            big_cookie_start = '".$params['big_cookie_start']."',
                                                            big_cookie_end = '".$params['big_cookie_end']."',
                                                            checked = 0,
                                                            parsed = 0, 
                                                            sleep = ".$params['sleep'].",
                                                            use_proxy = ".$params['use_proxy'].",
                                                            user_agent = ".$params['user_agent'].",
                                                            snoopy = ".$params['snoopy'].",
                                                            param = ".$row['param'].",
                                                            vendor = ".$row['vendor'].",
                                                            class_name = '".$row['class_name']."',
                                                            class_price = '".$row['class_price']."',
                                                            class_link = '".$row['class_link']."'";
                                            db_query($sql);
                                        }
                                    }
                                }
                            }

                            if ($params['link_parse'] == 2) {
                                $str = explode("/", $row['url']);

                                $first_part = $str[0]."//".$str[2]."/".$str[3]."/".$str['4'];
                                
                                $last_part = '';
                                for ($i = 5; $i < count($str); $i++) {
                                    $last_part .= '/'.$str[$i];
                                }

                                $result2 = db_query("SELECT * FROM module_city WHERE page_id = ".$params['city']);
                                if (db_num_rows($result2) > 0) {
                                    while ($row2 = db_fetch_array($result2)) {
                                        if (in_array($row2['value'], $cities)) {
                                            $new_url = $first_part."/".$row2['value'].$last_part;    

                                            $sql = "INSERT INTO links_cron SET 
                                                            page_id = ".$row['page_id'].", 
                                                            link = '".$new_url."', 
                                                            city_id = '".$row2['id']."', 
                                                            city_val = '".$row2['value']."', 
                                                            cookie_val = '".$params['cookie_val']."', 
                                                            big_cookie = '".$params['big_cookie']."',
                                                            big_cookie_start = '".$params['big_cookie_start']."',
                                                            big_cookie_end = '".$params['big_cookie_end']."',
                                                            checked = 0,
                                                            parsed = 0, 
                                                            sleep = ".$params['sleep'].",
                                                            use_proxy = ".$params['use_proxy'].",
                                                            user_agent = ".$params['user_agent'].",
                                                            snoopy = ".$params['snoopy'].",
                                                            param = ".$row['param'].",
                                                            vendor = ".$row['vendor'].",
                                                            class_name = '".$row['class_name']."',
                                                            class_price = '".$row['class_price']."',
                                                            class_link = '".$row['class_link']."'";
                                            db_query($sql);
                                        }
                                    }
                                }
                            }

                            if ($params['link_parse'] == 3) {
                                $str = explode("/", $row['url']);

                                $first_part = $str[0]."//".$str[2]."/".$str[3]."/".$str[4];
                                $last_part = substr($str[5], 1);
                                
                                $result2 = db_query("SELECT * FROM module_city WHERE page_id = ".$params['city']);
                                if (db_num_rows($result2) > 0) {
                                    while ($row2 = db_fetch_array($result2)) {
                                        if (in_array($row2['value'], $cities)) {
                                            $new_url = $first_part."/".$row2['value']."&".$last_part;    
                                            
                                            $sql = "INSERT INTO links_cron SET 
                                                            page_id = ".$row['page_id'].", 
                                                            link = '".$new_url."', 
                                                            city_id = '".$row2['id']."', 
                                                            city_val = '".$row2['value']."', 
                                                            cookie_val = '".$params['cookie_val']."',
                                                            big_cookie = '".$params['big_cookie']."',
                                                            big_cookie_start = '".$params['big_cookie_start']."',
                                                            big_cookie_end = '".$params['big_cookie_end']."', 
                                                            checked = 0,
                                                            parsed = 0, 
                                                            sleep = ".$params['sleep'].",
                                                            use_proxy = ".$params['use_proxy'].",
                                                            user_agent = ".$params['user_agent'].",
                                                            snoopy = ".$params['snoopy'].",
                                                            param = ".$row['param'].",
                                                            vendor = ".$row['vendor'].",
                                                            class_name = '".$row['class_name']."',
                                                            class_price = '".$row['class_price']."',
                                                            class_link = '".$row['class_link']."'";
                                            db_query($sql);
                                        }
                                    }
                                }
                            }
                        } else {
                            $sql = "INSERT INTO links_cron SET 
                                            page_id = ".$row['page_id'].", 
                                            link = '".$row['url']."', 
                                            city_id = 0,
                                            city_val = '', 
                                            cookie_val = '',
                                            big_cookie = '".$params['big_cookie']."',
                                            big_cookie_start = '".$params['big_cookie_start']."',
                                            big_cookie_end = '".$params['big_cookie_end']."', 
                                            checked = 0, 
                                            parsed = 0,
                                            sleep = ".$params['sleep'].",
                                            use_proxy = ".$params['use_proxy'].",
                                            user_agent = ".$params['user_agent'].",
                                            snoopy = ".$params['snoopy'].",
                                            param = ".$row['param'].",
                                            vendor = ".$row['vendor'].",
                                            class_name = '".$row['class_name']."',
                                            class_price = '".$row['class_price']."',
                                            class_link = '".$row['class_link']."'";
                           db_query($sql);
                        }
                    }
                }
            }

            echo "Формирование ссылок для шаблона ".$tpl_row['id']." закончено.<br>";

            // Проходим по ссылкам и забираем контент
            $result = db_query("SELECT * FROM links_cron WHERE checked = 0 ORDER BY id");
            if (db_num_rows($result) > 0) {
                while ($row = db_fetch_array($result)) {
                    if ($row['sleep'] == 1) {
                        sleep(5);
                    }

                    if ($row['snoopy'] == 1) {
                        $snoopy = new Snoopy;
                       
                        if ($row['big_cookie'] == 1) {
                            $cookie_value = $row['big_cookie_start'].$row['city_val'].$row['big_cookie_end'];

                            $snoopy->cookies[$row['cookie_val']] = $cookie_value;
                        } else {
                            $snoopy->cookies[$row['cookie_val']] = $row['city_val'];
                        }   

                        $snoopy->fetch($row['link']);

                        $curl_html = $snoopy->results;
                    } else {
                        $ch = curl_init();
                
                        curl_setopt($ch, CURLOPT_URL, $row['link']);  
                        
                        if ($row['use_proxy'] == 1) {
                            $proxy = db_get_data("SELECT name FROM module_proxy ORDER BY rand() LIMIT 1", "name");

                            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                        } else {
                            if ($row['big_cookie'] == 1) {
                                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 40); 
                                curl_setopt($ch, CURLOPT_TIMEOUT, 40);
                            } else {
                                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                            }
                        }
                        
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 

                        if ($row['user_agent'] == 1) {
                            $user_agent = db_get_data("SELECT name FROM module_uagent ORDER BY rand() LIMIT 1", "name");

                            curl_setopt($ch, CURLOPT_USERAGENT , $user_agent);
                        } else {
                            curl_setopt($ch, CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
                        }
                        
                        curl_setopt($ch, CURLOPT_VERBOSE, 1);

                        if ($row['cookie_val'] != "") {
                            if ($row['big_cookie'] == 1) {
                                $cookie_value = $row['big_cookie_start'].$row['city_val'].$row['big_cookie_end'];

                                curl_setopt($ch, CURLOPT_COOKIE, $row['cookie_val']."=".$cookie_value);
                            } else {
                                curl_setopt($ch, CURLOPT_COOKIE, $row['cookie_val']."=".$row['city_val']);
                            }                            
                        } 

                        $curl_html = curl_exec($ch); 

                        curl_close($ch); 
                    }

                    $curl_html = trim($curl_html); 
                    $curl_html = preg_replace("/ +/", " ", $curl_html);
                    $curl_html = preg_replace("/(\r\n){3,}/", "\r\n\r\n", $curl_html);

                    $filename = "files/".$row['id'].".html";
                    // print_r($filename);

                    $fp = fopen($filename, "w");
                    fwrite($fp, $curl_html);
                    fclose($fp);

                    // делаем отметку о том, что по ссылке уже обходили
                    $sql = "UPDATE links_cron SET checked = 1 WHERE id = ".$row['id'];
                    db_query($sql);
                }
            } 

            echo "Получение контента для шаблона ".$tpl_row['id']." закончено.<br>";
        }
    }

    // db_query("TRUNCATE TABLE catalog");

    // $elements = db_get_array("SELECT name, rrc FROM module_elements", "rrc", "name");

    // $i = 0;
    // $result = db_query("SELECT * FROM pages WHERE parent_id = 4 AND visible = 1 AND deleted = 0 ORDER BY sortfield");
    // if (db_num_rows($result) > 0) {
    //     while ($row = db_fetch_array($result)) {
           
    //         $result2 = db_query("SELECT * FROM pages WHERE parent_id = ".$row['id']." AND visible = 1 AND deleted = 0 ORDER BY sortfield");
    //         if (db_num_rows($result2) > 0) {
    //             while ($row2 = db_fetch_array($result2)) {

    //                 $result3 = db_query("SELECT * FROM module_catalog WHERE page_id = ".$row2['id']." ORDER BY sortfield");
    //                 if (db_num_rows($result3) > 0) {
    //                     while ($row3 = db_fetch_array($result3)) {
    //                         $params = db_get_data("SELECT * FROM module_parse WHERE id = ".$row3['param']);

    //                         $filename = "files/".$row3['id'].".html";

    //                         $html = file_get_html($filename);  

    //                         $k = 0;
    //                         $v = 0;
                            
    //                         $html_array = $html->find('[class='.$row3['class_name'].']');
    //                         $html_array = array_unique($html_array);

    //                         foreach ($html_array as $e) { 
    //                             $price_array = $html->find('[class='.$row3['class_price'].']');
    //                             $url_array = $html->find('[class='.$row3['class_link'].']');
    //                             $title_val = trim(strip_tags($e));
    //                             $encoding = mb_detect_encoding($title_val);

    //                             foreach ($elements as $key => $value) {
    //                                 $str_pos = strpos($title_val, $value);
    //                                 if ($str_pos > 0) {
    //                                     $rrc = $key;
    //                                     $title = $value;
    //                                     break;
    //                                 } else {
    //                                     $rrc = '';
    //                                     $title = $title_val;
    //                                 }
    //                             }

    //                             $price = '';
    //                             $url = '';

    //                             if ($params['url_type'] == 1) {
    //                                 $url = $params['domen'].$url_array[$k]->href;
    //                             } else {
    //                                 $url = $url_array[$k]->href;
    //                             }

    //                             if ($params['title_clean'] == 1) {
    //                                 $nbsp_pos = mb_strpos($title, "&nbsp;", $mb_detect_encoding) + 6;
    //                                 $title = mb_substr($title, $nbsp_pos, mb_strlen($title, $encoding), $encoding);
    //                             }

    //                             if ($params['price_cut_type1'] == 1) {
    //                                 $price = strip_tags($price_array[$k]);
    //                                 $price = intval(str_replace(" ", "", $price));
    //                             } 

    //                             if ($params['price_cut_type2'] == 1) {
    //                                 $price_array_val = explode("<li>", $price_array[$k]);
    //                                 $price = strip_tags($price_array_val[1]);

    //                                 $price = str_replace("Цена по прайсу", "", $price);
    //                                 $price = str_replace("₸", "", $price);
    //                                 $price = str_replace(" ", "", $price);
    //                             }

    //                             if ($params['param_parse'] == 1) {
    //                                 $start = strpos($html, "var array = [];");
    //                                 $str = substr($html, $start, strlen($html));
    //                                 $stop = strpos($str, "</script>");
    //                                 $str = substr($str, 0, $stop);

    //                                 $items = explode("array.push", $str);
                                    
    //                                 foreach ($items as $key => $value) {
    //                                     $title_pos = strpos($value, $title);

    //                                     if ($title_pos > 0) {
    //                                         $price_str_start = strpos($value, "unit_price");
    //                                         $price_str = substr($value, $price_str_start, strlen($value));
    //                                         $price_str_stop = strpos($price_str, ",");
    //                                         $price_str = substr($price_str, 0, $price_str_stop);

    //                                         $price = str_replace('"', "", $price_str);
    //                                         $price = str_replace('unit_price:', "", $price);
    //                                     }
    //                                 }
    //                             }

    //                             if ($title != "") {
    //                                 $sql = "INSERT INTO catalog SET date = NOW(), type_id = ".$row['id'].", shop_id = ".$row2['id'].", title = '".$title."', rrc = '".$rrc."', price = '".$price."', vendor = ".$row3['vendor'].", url = '".$url."'";
    //                                 db_query($sql);

    //                                 $k++;
    //                             }
    //                         }          
    //                     }
    //                 } 

    //                 $i++; 
    //             }
    //         }

    //     }
    // }

    // $out = '<table border="1" cellspacing="2" cellpadding="2" width="100%">';
    // $out .= '<tr>
    //             <td><b>Дата</b></td>
    //             <td><b>Время</b></td>
    //             <td><b>Партнер</b></td>
    //             <td><b>Город</b></td>
    //             <td><b>Производитель</b></td>
    //             <td><b>Группа товаров</b></td>
    //             <td><b>Модель</b></td>
    //             <td><b>РРЦ</b></td>
    //             <td><b>Цена на сайте партнера</b></td>
    //         </tr>';

    // $result = db_query("SELECT * FROM catalog");
    // if (db_num_rows($result) > 0) {
    //     while ($row = db_fetch_array($result)) {
    //         $group_name = getPageTitle($row['type_id']);
    //         $partner = getPageTitle($row['shop_id']);
    //         $date = date("d.m.Y", strtotime($row['date']));
    //         $time = date("H:i", strtotime($row['date']));
    //         $vendor = db_get_data("SELECT name FROM module_vendors WHERE id = ".$row['vendor'], "name");

    //         if ($row['rrc'] != "" && $row['rrc'] != $row['price']) {
    //             $screen_name = $group_name."_".$partner."_".$row['title']."_".date("d m Y h:i:s");

    //             @$fp = fopen('photos/'.$screen_name.'.jpg', 'w'); 
    //             @fwrite($fp, file_get_contents($api.$row['url'])); 
    //             @fclose($fp);
    //         }

    //         $out .= '<tr>
    //                     <td>'.$date.'</td>
    //                     <td>'.$time.'</td>
    //                     <td>'.$partner.'</td>
    //                     <td>Город</td>
    //                     <td>'.$vendor.'</td>
    //                     <td>'.$group_name.'</td>
    //                     <td>'.$row['title'].'</td>
    //                     <td>'.$row['rrc'].'</td>
    //                     <td>'.$row['price'].'</td>
    //                 </tr>'; 
    //     }
    // }

    // $out .= '</table>';

    // // создаем архив скриншотов для отправки по почте
    // $list = $archive->create('photos');

    // echo $out;

    // $fp = fopen("file.html", "w");
    // fwrite($fp, $out);
    // fclose($fp);

    // $inputFileType = 'HTML';
    // $inputFileName = 'file.html';
    // $outputFileType = 'Excel2007';
    // $outputFileName = 'file.xlsx';
 
    // $objPHPExcelReader = PHPExcel_IOFactory::createReader($inputFileType);
    // $objPHPExcel = $objPHPExcelReader->load($inputFileName);

    // $objPHPExcelWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,$outputFileType);
    // $objPHPExcel = $objPHPExcelWriter->save($outputFileName);
?>
