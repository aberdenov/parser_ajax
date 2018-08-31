<?php
    set_time_limit(0);

    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    define("IN_SITEDRIVE", 1);

    # INCLUDES ##############################################################################
    
    require_once('./common.php');                           // SiteDrive API and initialize
    require_once('./admin/includes/config.php');            // Настройки SiteDrive
    require_once('./admin/includes/values.php');            // Работа с константами
    require_once('./simple_html_dom.php');
    require_once('./pclzip.lib.php');
    require_once('./Classes/PHPExcel.php');

    include "Snoopy.class.php";

    # VARS ##################################################################################

    $api = 'http://mini.s-shot.ru/1280/1280/jpeg/?';
    $archive = new PclZip("archive.zip");
    $dir = './../photos/';
    $out = '';
	
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

    # MAIN ##################################################################################

   	db_connect(DB_HOST, DB_LOGIN, DB_PASSWORD);
    db_select_db(DB_NAME);

    if (isset($_POST['type'])) {
        if ($_POST['type'] == "html-request") {
            // подготавливаем структуру ссылок
            if ($_POST['action'] == 1) {
                db_query("TRUNCATE links");
                db_query("TRUNCATE TABLE catalog");

                $where = 'WHERE lang_id = 1';

                // Производители
                $vendors = explode(";", $_POST['vendors']);
                $vendors = array_unique($vendors);
                $vendors_count = count($vendors) - 1;
                $i = 0;

                if ($vendors_count > 0) {
                    $where .= ' AND (';

                    foreach ($vendors as $key => $value) {
                        if ($value != "") {
                            $i++;

                            $where .= 'vendor = '.$value;

                            if ($i != $vendors_count) $where .= ' OR ';
                        }
                    }

                    $where .= ')';
                } 

                // магазины
                $shops = explode(";", $_POST['shops']);
                $shops = array_unique($shops);
                $page_array = '';
                $i = 0;

                foreach ($shops as $key => $value) {
                    if (getFirstChildID($value) > 0) {
                        getPages($value);
                    } else {
                        $page_array[] .= $value;
                    }
                }

                $shops_count = count($page_array) - 1;

                if ($shops_count > 0) {
                    $where .= ' AND (';

                    foreach ($page_array as $key => $value) {
                        if ($value != "") {
                            $i++;

                            $where .= 'page_id = '.$value;

                            if ($i != $shops_count) $where .= ' OR ';
                        }
                    }

                    $where .= ')';
                }

                // города
                $cities = explode(";", $_POST['cities']);
                $cities = array_unique($cities);

                $result = db_query("SELECT * FROM module_catalog ".$where);
                if (db_num_rows($result) > 0) {
                    while ($row = db_fetch_array($result)) {
                        $params = db_get_data("SELECT * FROM module_parse WHERE id = ".$row['param']);

                        if ($params['cookie_val'] != "") {
                            $result2 = db_query("SELECT * FROM module_city WHERE page_id = ".$params['city']);
                            if (db_num_rows($result2) > 0) {
                                while ($row2 = db_fetch_array($result2)) {
                                    if (in_array($row2['value'], $cities)) {
                                        $sql = "INSERT INTO links SET 
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

                                                $sql = "INSERT INTO links SET 
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

                                                $sql = "INSERT INTO links SET 
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
                                                
                                                $sql = "INSERT INTO links SET 
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
                                $sql = "INSERT INTO links SET 
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
            }

            // Генерируем html файлы для разбора
            if ($_POST['action'] == 2) {
                $result = db_query("SELECT * FROM links WHERE checked = 0 ORDER BY id LIMIT 1");
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
                        print_r($filename);

                        $fp = fopen($filename, "w");
                        fwrite($fp, $curl_html);
                        fclose($fp);

                        // делаем отметку о том, что по ссылке уже обходили
                        $sql = "UPDATE links SET checked = 1 WHERE id = ".$row['id'];
                        db_query($sql);
                    }
                } else {
                    print_r("stop");
                }
            }

            // Разбор файлов и запись контента во временную таблицу базы
            if ($_POST['action'] == 3) {
                $elements = db_get_array("SELECT name, rrc FROM module_elements", "rrc", "name");

                $result = db_query("SELECT * FROM links WHERE parsed = 0 ORDER BY id LIMIT 1");
                if (db_num_rows($result) > 0) {
                    while ($row = db_fetch_array($result)) {
                        $params = db_get_data("SELECT * FROM module_parse WHERE id = ".$row['param']);

                        $filename = "files/".$row['id'].".html";

                        $content = file_get_contents($filename); 
                        $content = preg_replace('#(<ul class="b-list b-list_theme_normal b-list_catalog-menu">.*</ul>)#sU', '', $content);  

                        file_put_contents($filename, $content);

                        $html = file_get_html($filename);  

                        $k = 0;
                        
                        $html_array = $html->find('[class='.$row['class_name'].']');
                        $html_array = array_unique($html_array);

                        foreach ($html_array as $e) { 
							$title_val = str_replace("quot;", "", $title_val);
							$title_val = str_replace("&amp;", "", $title_val);
							$title_val = preg_replace($search, $replace, $e);
							$title_val = trim($title_val);
                            
                            if ($title_val != "По возрастанию цены" && 
                                $title_val != "Без группировки" && 
                                $title_val != "без группировки" &&
                                $title_val != "Все" && 
                                $title_val != "В магазинах и под заказ" &&
                                $title_val != "Ноутбуки и планшеты" &&
                                $title_val != "Компьютеры и периферия" &&
                                $title_val != "Комплектующие для ПК" &&
                                $title_val != "Телефоны и смарт-часы" &&
                                $title_val != "Телевизоры и медиа" &&
                                $title_val != "Игры и приставки" &&
                                $title_val != "Аудиотехника" &&
                                $title_val != "Фото-видеоаппаратура" &&
                                $title_val != "Офисная техника и мебель" &&
                                $title_val != "Сетевое оборудование" &&
                                $title_val != "Сеть и коммутация" &&
                                $title_val != "Автотовары" &&
                                $title_val != "Крупная бытовая техника" &&
                                $title_val != "Товары для кухни" &&
                                $title_val != "Красота и здоровье" &&
                                $title_val != "Товары для дома" &&
                                $title_val != "Инструменты" &&
                                $title_val != "Одежда и обувь" &&
                                $title_val != "Услуги" &&
                                $title_val != "по наличию" &&
                                $title_val != "По наличию" 
                            ) {
                                $price_array = $html->find('[class='.$row['class_price'].']');
                                $url_array = $html->find('[class='.$row['class_link'].']');
                                $encoding = mb_detect_encoding($title_val);

                                foreach ($elements as $key => $value) {
                                    $str_pos = strpos(mb_strtolower($title_val, $encoding), mb_strtolower($value, $encoding));
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
                                    $price = strip_tags($price_array_val[3]);

                                    $price = str_replace("Цена в интернет-магазине", "", $price);
                                    $price = str_replace("Цены указаны с учетом НДС", "", $price);
                                    $price = str_replace("₸", "", $price);
                                    $price = str_replace(" ", "", $price);
                                }

                                if ($params['price_cut_type3'] == 1) {
                                    $price = strip_tags($price_array[$k]);

                                    $price = str_replace("Цена:", "", $price);
                                    $price = str_replace("&#8376;", "", $price);
                                    $price = str_replace(" ", "", $price);
                                }

                                if ($params['price_cut_type4'] == 1) {
                                    $price = strip_tags($price_array[$k]);
                                    $price = str_replace("&nbsp;", "", $price);
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

                                // if ($title != "" && $title != "По возрастанию цены" && $title != "Без группировки" && $title != "Все" && $title != "В магазинах и под заказ") {
                                    $sql = "INSERT INTO catalog SET date = NOW(), type_id = ".getPageParentID(getPageParentID($row['page_id'])).", shop_id = ".getPageParentID($row['page_id']).", title = '".$title."', rrc = '".$rrc."', price = '".$price."', vendor = ".$row['vendor'].", city_id = ".$row['city_id'].", url = '".$url."'";
                                    db_query($sql);

                                    $id = db_insert_id();
                                    
                                    print_r("Insert id: ".$id."<br>");

                                    $k++;
                                // }
                            }
                        }

                        // делаем отметку о том, что контент по ссылке уже получили и разобрали
                        $sql = "UPDATE links SET parsed = 1 WHERE id = ".$row['id'];
                        db_query($sql);
                    }  
                } else {
                    print_r("stop");
                }
            }

            // Генерация файлов
            if ($_POST['action'] == 4) {
                $out = '<html xmlns:o="urn:schemas-microsoft-com:office:office"
							xmlns:x="urn:schemas-microsoft-com:office:excel"
							xmlns="http://www.w3.org/TR/REC-html40">
						<head>
						<table border="1" cellspacing="2" cellpadding="2" width="100%">';
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
                            <td></td>
                        </tr>';

                // формируем дополнительные параметры запроса, если указано использовать выборку по РРЦ
                if ($_POST['rrc_status'] > 0) {
                    $goods_id = explode(";", $_POST['goods']);
                    $goods_id = array_unique($goods_id);
                    $count_goods = count($goods_id) - 1;
                    $where_goods = 'WHERE ';

                    $i = 0;
                    foreach ($goods_id as $key => $value) {
                        $i++;

                        if ($value != "") {
                            $title = db_get_data("SELECT name FROM module_elements WHERE id = ".$value, "name");

                            $where_goods .= "title LIKE '%".$title."%'";

                            if ($i != $count_goods) $where_goods .= ' OR '; 
                        }
                    }
                } else {
                    $where_goods = '';
                }
                
                $result = db_query("SELECT * FROM catalog ".$where_goods);
                if (db_num_rows($result) > 0) {
                    while ($row = db_fetch_array($result)) {
                        if ($row['title'] != "По возрастанию цены" && 
                            $row['title'] != "Без группировки" && 
                            $row['title'] != "без группировки" &&
                            $row['title'] != "Все" && 
                            $row['title'] != "В магазинах и под заказ" &&
                            $row['title'] != "Ноутбуки и планшеты" &&
                            $row['title'] != "Компьютеры и периферия" &&
                            $row['title'] != "Комплектующие для ПК" &&
                            $row['title'] != "Телефоны и смарт-часы" &&
                            $row['title'] != "Телевизоры и медиа" &&
                            $row['title'] != "Игры и приставки" &&
                            $row['title'] != "Аудиотехника" &&
                            $row['title'] != "Фото-видеоаппаратура" &&
                            $row['title'] != "Офисная техника и мебель" &&
                            $row['title'] != "Сетевое оборудование" &&
                            $row['title'] != "Сеть и коммутация" &&
                            $row['title'] != "Автотовары" &&
                            $row['title'] != "Крупная бытовая техника" &&
                            $row['title'] != "Товары для кухни" &&
                            $row['title'] != "Красота и здоровье" &&
                            $row['title'] != "Товары для дома" &&
                            $row['title'] != "Инструменты" &&
                            $row['title'] != "Одежда и обувь" &&
                            $row['title'] != "По наличию" &&
                            $row['title'] != "по наличию" &&
                            $row['title'] != "Услуги" &&
                            $row['title'] != ""  
                        ) {
                            $group_name = getPageTitle($row['type_id']);
                            $partner = getPageTitle($row['shop_id']);
                            $date = date("d.m.Y", strtotime($row['date']));
                            $time = date("H:i", strtotime($row['date']));
                            $vendor = db_get_data("SELECT name FROM module_vendors WHERE id = ".$row['vendor'], "name");
                            $city = db_get_data("SELECT name FROM module_city WHERE id = ".$row['city_id'], "name");

                            if ($row['rrc'] != "" && intval($row['rrc']) > intval($row['price'])) {
                                $status = 'Несоответствие';
                                $screen_name = $group_name."_".$partner."_".$row['title']."_".date("d m Y h:i:s");

                                @$fp = fopen('photos/'.$screen_name.'.jpg', 'w'); 
                                @fwrite($fp, file_get_contents($api.$row['url'])); 
                                @fclose($fp);
                            } else {
                                $status = '';
                            }

                            $out .= '<tr>
                                        <td>'.$date.'</td>
                                        <td>'.$time.'</td>
                                        <td>'.$partner.'</td>
                                        <td>'.$city.'</td>
                                        <td>'.$vendor.'</td>
                                        <td>'.$group_name.'</td>
                                        <td>'.$row['title'].'</td>
                                        <td>'.$row['rrc'].'</td>
                                        <td>'.$row['price'].'</td>
                                        <td>'.$status.'</td>
                                    </tr>'; 
                        }
                    }
                }

                $out .= '</table></body></html>';

                // создаем архив скриншотов для отправки по почте
                $list = $archive->create('photos');

                //print_r($out);

                $fp = fopen("file.html", "w");
                fwrite($fp, $out);
                fclose($fp);

                $inputFileType = 'HTML';
                $inputFileName = 'file.html';
                $outputFileType = 'Excel5';
                $outputFileName = 'file.xls';
             
                $objPHPExcelReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objPHPExcelReader->load($inputFileName);

                $objPHPExcelWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $outputFileType);
                $objPHPExcel = $objPHPExcelWriter->save($outputFileName);

                $sql = "UPDATE links SET checked = 0, parsed = 0 WHERE id = ".$row['id'];
                db_query($sql);

                // очищаем временные папки
//                cleanDir("./files");
//                cleanDir("./photos");
            }

            // получаем список магазинов
            if ($_POST['action'] == 5) {
                $chapters = explode(";", $_POST['chapters']);
                $chapters = array_unique($chapters);
                $count = count($chapters) - 1;
                $out = '';
                $i = 0;

                foreach ($chapters as $key => $value) {
                    if ($value != "") {
                        $i++;

                        $result = db_query("SELECT * FROM pages WHERE parent_id = ".$value." AND deleted = 0 AND visible = 1 ORDER BY sortfield");
                        if (db_num_rows($result) > 0) {
                            $chapter_title = getPageTitle($value);
                            $out .= '<div style="margin: 0px 0px 10px 0px"><b>'.$chapter_title.'</b></div>';

                            while ($row = db_fetch_array($result)) {
                                $out .= '<div style="display: inline-block; width: 200px">
                                            <div style="display: inline-block; width: 20px; vertical-align: top"><input type="checkbox" value="'.$row['id'].'" id="shops_'.$row['id'].'" name="shops[]" onchange="getCities();"></div>
                                            <div style="display: inline-block; width: 160px; vertical-align: top">'.$row['title'].'</div>
                                        </div>';
                            }
                        }

                        if ($i != $count) $out .= '<hr>';
                    }
                }

                print_r($out);
            }

            // получаем список городов
            if ($_POST['action'] == 6) {
                $shops = explode(";", $_POST['shops']);
                $shops = array_unique($shops);
                $count = count($shops) - 1;
                $out = '';
                $page_array = '';
                $city_array = '';
                $i = 0;

                foreach ($shops as $key => $value) {
                    if (getFirstChildID($value) > 0) {
                        $page_array[] .= getFirstChildID($value);
                        //getPages($value);
                    } else {
                        $page_array[] .= $value;
                    }
                }

                foreach ($page_array as $key => $value) {
                    if ($value != "") {
                        $i++;

                        $param_id = db_get_data("SELECT param FROM module_catalog WHERE page_id = ".$value." LIMIT 1", "param");
                        $city_id = db_get_data("SELECT city FROM module_parse WHERE id = ".$param_id, "city");
                        
                        $city_array[] .= $city_id; 
                    }
                }

                $city_array = array_unique($city_array);

                foreach ($city_array as $key => $value) {
                    if ($value != "") {
                        $result = db_query("SELECT * FROM module_city WHERE page_id = ".$value." ORDER BY name");
                        if (db_num_rows($result) > 0) {
                            //$chapter_title = getPageTitle(getPageParentID($value))." - ".getPageTitle($value);
                            $chapter_title = getPageTitle($value);
                            $out .= '<div style="margin: 0px 0px 10px 0px"><b>'.$chapter_title.'</b></div>';
                            $out .= '<div style="margin: 0px 0px 10px 0px"><input type="checkbox" value="" name="checkall" onchange="selAll(this, '.$value.');")>Выбрать все</div>';

                            while ($row = db_fetch_array($result)) {
                                $out .= '<div style="display: inline-block; width: 200px">
                                            <div style="display: inline-block; width: 20px; vertical-align: top"><input type="checkbox" alt="city_rel_'.$value.'" value="'.$row['value'].'" id="city_'.$row['id'].'" name="city[]"></div>
                                            <div style="display: inline-block; width: 160px; vertical-align: top">'.$row['name'].'</div>
                                        </div>';
                            }
                        }

                        if ($i != $count) $out .= '<hr>';
                    }
                }
                
                print_r($out);
            }

            // создаем шаблон
            if ($_POST['action'] == 7) {
                $sql = "INSERT INTO templates SET name = '".$_POST['name']."', 
                                                brend_val = '".$_POST['brend_val']."', 
                                                chapter_val = '".$_POST['chapter_val']."', 
                                                shop_val = '".$_POST['shop_val']."', 
                                                city_val = '".$_POST['city_val']."', 
                                                rrc_val = '".$_POST['rcc_val']."', 
                                                use_rcc = '".$_POST['rrc_status']."'";
                db_query($sql);
            }

            // получаем список всех шаблонов
            if ($_POST['action'] == 8) {
                $out = '<option value="0">Не выбрано</option>';

                $result = db_query("SELECT * FROM templates");
                if (db_num_rows($result) > 0) {
                    while ($row = db_fetch_array($result)) {
                        $out .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
                    }
                }

                print_r($out);
            }

            // изменяем шаблон
            if ($_POST['action'] == 9) {
                $sql = "UPDATE templates SET brend_val = '".$_POST['brend_val']."', 
                                            chapter_val = '".$_POST['chapter_val']."', 
                                            shop_val = '".$_POST['shop_val']."', 
                                            city_val = '".$_POST['city_val']."', 
                                            rrc_val = '".$_POST['rcc_val']."', 
                                            use_rcc = '".$_POST['rrc_status']."' WHERE id = ".$_POST['id'];
                db_query($sql);
            }

            // применяем шаблон
            if ($_POST['action'] == 10) {
                $data = db_get_data("SELECT * FROM templates WHERE id = ".$_POST['id']);

                $brend = explode(";", $data['brend_val']);
                $brend = array_unique($brend);
                $brend_str = '';
                $i = 0;
                $count = count($brend) - 1;
                foreach ($brend as $key => $value) {
                    if ($value != "") {
                        $i++;

                        $brend_str .= $value;

                        if ($i != $count) $brend_str .= ';';
                    }
                }

                $chapter = explode(";", $data['chapter_val']);
                $chapter = array_unique($chapter);
                $chapter_str = '';
                $i = 0;
                $count = count($chapter) - 1;
                foreach ($chapter as $key => $value) {
                    if ($value != "") {
                        $i++;

                        $chapter_str .= $value;

                        if ($i != $count) $chapter_str .= ';';
                    }
                }

                $shop = explode(";", $data['shop_val']);
                $shop = array_unique($shop);
                $shop_str = '';
                $i = 0;
                $count = count($shop) - 1;
                foreach ($shop as $key => $value) {
                    if ($value != "") {
                        $i++;

                        $shop_str .= $value;

                        if ($i != $count) $shop_str .= ';';
                    }
                }

                $city = explode(";", $data['city_val']);
                $city = array_unique($city);
                $city_str = '';
                $i = 0;
                $count = count($city) - 1;
                foreach ($city as $key => $value) {
                    if ($value != "") {
                        $i++;

                        $city_str .= $value;

                        if ($i != $count) $city_str .= ';';
                    }
                }

                $rrc = explode(";", $data['rrc_val']);
                $rrc = array_unique($rrc);
                $rrc_str = '';
                $i = 0;
                $count = count($rrc) - 1;
                foreach ($rrc as $key => $value) {
                    if ($value != "") {
                        $i++;

                        $rrc_str .= $value;

                        if ($i != $count) $rrc_str .= ';';
                    }
                }

                print_r($brend_str."#".$chapter_str."#".$shop_str."#".$city_str."#".$rrc_str."#".$data['use_rcc']);
            }

            // удаляем шаблон
            if ($_POST['action'] == 11) {
                $sql = "DELETE FROM templates WHERE id = ".$_POST['id'];
                db_query($sql);
            }

            // загрузка proxy
            if ($_POST['action'] == 12) {
                $result = db_query("SELECT * FROM proxy WHERE parsed = 0 ORDER BY id LIMIT 1");
                if (db_num_rows($result) > 0) {
                    while ($row = db_fetch_array($result)) {
                        $ch = curl_init();
                            
                        curl_setopt($ch, CURLOPT_URL, "https://www.citilink.ru/");  
                        curl_setopt($ch, CURLOPT_PROXY, $row['proxy']);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_USERAGENT , "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0");
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_VERBOSE, 1);

                        $curl_html = curl_exec($ch); 

                        curl_close($ch);

                        if ($curl_html) {
                            $sql = "INSERT INTO module_proxy SET page_id = 54, lang_id = 1, name = '".$row['proxy']."'";;
                            db_query($sql);
                            
                            $str = $row['proxy']." - работает";
                        } else {
                            $str = $row['proxy']." - не работает";
                        }

                        print_r($str);

                        // делаем отметку о том, что по ссылке уже обходили
                        $sql = "UPDATE proxy SET parsed = 1 WHERE id = ".$row['id'];
                        db_query($sql);
                    }
                } else {
                    print_r("stop");
                }
            }

            // выборка rrc взависимости от странцы
            if ($_POST['action'] == 13) {
                $out = '';
                $result = db_query("SELECT * FROM pages WHERE parent_id = ".$_POST['id']." AND deleted = 0 AND visible = 1 ORDER BY sortfield");
                if (db_num_rows($result) > 0) {
                    while ($row = db_fetch_array($result)) {
                        $out .= '<div style="margin: 20px auto 10px auto"><b>'.$row['title'].'</b></div>';

                        $result2 = db_query("SELECT * FROM pages WHERE parent_id = ".$row['id']." AND deleted = 0 AND visible = 1 ORDER BY sortfield");
                        if (db_num_rows($result2) > 0) {
                            while ($row2 = db_fetch_array($result2)) {
                                $out .= '<div style="margin: 20px auto 10px auto">'.$row2['title'].'&nbsp;<a href="javascript:void(0);" style="color: #000" onclick="openDiv(\'block_'.$row2['id'].'\')">[развернуть]</a></div>';
                                $out .= '<div id="block_'.$row2['id'].'" style="display: none">';

                                $result3 = db_query("SELECT * FROM module_elements WHERE page_id = ".$row2['id']." ORDER BY sortfield");
                                if (db_num_rows($result3) > 0) {
                                    while ($row3 = db_fetch_array($result3)) {
                                        $out .= '<div style="display: inline-block; width: 200px">
                                                        <div style="display: inline-block; width: 20px; vertical-align: top"><input type="checkbox" value="'.$row3['id'].'" id="rrc_'.$row3['id'].'" name="rrc[]"></div>
                                                        <div style="display: inline-block; width: 160px; vertical-align: top">'.$row3['name'].'</div>
                                                    </div>';
                                    }
                                }

                                $out .= '</div>';
                            }
                        }
                    }
                }

                print_r($out);
            }

            // получаем подразделы
            if ($_POST['action'] == 14) {
                $out = '';
                $result = db_query("SELECT * FROM pages WHERE parent_id = ".$_POST['id']." AND deleted = 0 AND visible = 1 ORDER BY sortfield");
                if (db_num_rows($result) > 0) {
                    while ($row = db_fetch_array($result)) {
                        $out .= '<option value="'.$row['id'].'">'.$row['title'].'</option>';
                    }
                }

                print_r($out."#".$_POST['result_id']);
            }
        }
    }    
?>
