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

    //$url = "https://www.ulmart.ru/catalog/monitors?filters=active%3Ab&applied=true&brands=20&jdSuppliers=&warranties=&bargainTypes=&receiptTime=&priceColors=&shops=&labels=&available=false&reserved=false&suborder=false&rec=false&superPrice=false&showCrossboardGoods=f";
    // $url = "https://shop.kz/smartfony/";

    # MAIN ##################################################################################

    $fields = array(
            'filters' => "active%3Ab",
            'applied' => "true",
            'brands' => "20",
            'jdSuppliers' => "",
            'warranties' => "",
            'bargainTypes' => "",
            'receiptTime' => "",
            'priceColors' => "",
            'shops' => "",
            'labels' => "",
            'available' => "false",
            'reserved' => "false",
            'suborder' => "false",
            'rec' => "false",
            'superPrice' => "false",
            'showCrossboardGoods' => "f"
        );

    //url-ify the data for the POST
    // foreach ($fields as $key => $value) { $fields_string .= $key.'='.$value.'&'; }
    // rtrim($fields_string, '&');

    // include "Snoopy.class.php";
    // $snoopy = new Snoopy;
    
    // $snoopy->fetch($url);
    // print $snoopy->results;
    
    $ch = curl_init();
            
    curl_setopt($ch, CURLOPT_URL, $url);  
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT , "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.5) Gecko/2008120122 Firefox/3.0.5");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 40);
    curl_setopt($ch, CURLOPT_TIMEOUT, 40);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);

    // curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

    // curl_setopt($ch, CURLOPT_COOKIE, "_space=krasn_cl:krasnabak");

    $curl_html = curl_exec($ch); 

    curl_close($ch); 

    // $curl_html = file_get_contents($url);
    echo $curl_html;

    // echo $client;
?>
