<?php

    require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
    require($_SERVER["DOCUMENT_ROOT"] . "/include/help.php");
    if(!isset($_SESSION)) session_start();

    $access_data = isset($_SESSION["access_data"]) ? $_SESSION["access_data"] : null;
    require_once($_SERVER["DOCUMENT_ROOT"]."/web/header.php");

    $logtype = isset($_REQUEST["type"]) ? $_REQUEST["type"] : null;

    switch ($logtype) {
        case 'errors':
            $log_filename = $_SERVER["DOCUMENT_ROOT"]."/log/errors.log";
            break;
        case "process_events":
            $log_filename = $_SERVER["DOCUMENT_ROOT"]."/log/process_events.log";
            break;
        case "auth_events":
            $log_filename = $_SERVER["DOCUMENT_ROOT"]."/log/auth.log";
            break;
        //case "apache":
        //    $log_filename = "/var/log/apache2/error.log";
        //    break;
        case "debug":
            $log_filename = $_SERVER["DOCUMENT_ROOT"]."/log/debug.log";
            break;
        default:
            $log_filename = null;
            break;
    }

    $log_text = "empty log file";
    if (!is_null($log_filename)) {
        $filename = $log_filename;
        //log_event("Log filename ".$filename);
        $log_text = read_from_file($filename);

        if ($logtype == "process_events" || $logtype == "errors") {
            $log_text_split = $split_array = explode("\n", $log_text);
            $log_text_split = reverse_array($log_text_split);
            $log_text = join("\n", $log_text_split);
        }

    }

    $page_header = !is_null($log_filename) ? "Файл ".$log_filename : "Открыть файл логов";
    $link_to_file = str_replace("/var/www/b24.next.kz", '', $log_filename);
?>

<div class="container">
    <h1><?= $page_header ?></h1>

    <?php 
        if (!is_null($log_filename)) { ?>
        <p>Здесь выводятся ошибки программы. Записи генерируются скриптами, не апачем. <a href="..<?= $link_to_file ?>">Открыть</a> текст логов</p>
        <pre><?= $log_text ?></pre>

        <?php 
        } else {
            ?>
            <p>Выберите из списка файл логов, чтобы открыть его</p>
            <div class="list-group">
              <a href="../server/log_page.php?type=errors" class="list-group-item">errors.log</a>
              <a href="../server/log_page.php?type=process_events" class="list-group-item">events.log</a>
              <a href="../server/log_page.php?type=auth_events" class="list-group-item">auth.log</a>
              <a href="../server/log_page.php?type=debug" class="list-group-item">debug.log</a>
            </div>
        <?php
        }
        ?>
</div>



<?php
     require_once($_SERVER["DOCUMENT_ROOT"]."/web/footer.php"); 
     ?>
