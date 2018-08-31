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

    # MAIN ##################################################################################

    db_connect(DB_HOST, DB_LOGIN, DB_PASSWORD);
    db_select_db(DB_NAME);

    db_query("TRUNCATE TABLE module_uagent");

    $content = file('user_agent.txt');

    foreach ($content as $string) {
        $sql = "INSERT INTO module_uagent SET page_id = 98, lang_id = 1, name = '".trim($string)."'";
        db_query($sql);

        echo $sql."<br>";
    }
?>
