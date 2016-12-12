<!DOCTYPE html>
<html lang="en">
<head>
    <title>NEXT.Events</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="../favicon.ico">
    <!--link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script-->

    <link rel="stylesheet" href="../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    
    <link rel="stylesheet" href="../assets/css/select2.min.css">

    
    <script src="../assets/js/jquery1.12.4.min.js"></script>
    <script src="../assets/js/bootstrap3.3.7.min.js"></script>
    <script src="//api.bitrix24.com/api/v1/"></script>

    <script src="../assets/js/select2.min.js"></script>
    <script src="../assets/js/custom.js"></script>

    <script type="text/javascript">
         
        $(document).ready(function() {
            var sizes = BX24.getScrollSize();
            var width = sizes.scrollWidth;
            BX24.resizeWindow(width, 800);
        });
        $('select').select2();
        $('[data-toggle="tooltip"]').tooltip();
    </script>
</head>
<body>

<nav class="navbar navbar-inverse blue-bg">
    <div class="container-fluid">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="../forms/index.php?auth_id=<?= isset($_REQUEST["AUTH_ID"]) ? $_REQUEST["AUTH_ID"] : $_REQUEST["auth_id"]?>">Система продаж</a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <ul class="nav navbar-nav">
                    <li><a href="#" id="updatePageButton">Обновить страницу</a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <?php
                    $user_name = isset($_SESSION["user_name"]) ? $_SESSION["user_name"]." [".$_SESSION["user_id"]."]" : "Не залогинен";
                    ?>

                    <li><a href="#"><span class="glyphicon glyphicon-user"></span> <?= $user_name ?></a></li>
                </ul>

            </div>
        </div>
    </div>
</nav>


<?php
//-----------------------------------
if (isset($_GET["error"]) || isset($_GET["success"]) || isset($_GET["warning"])) {

    $class = "info";
    $message = "";
    if (isset($_GET["error"])) {
        $class = "danger";
        $message = $_GET["error"];

    } else if (isset($_GET["success"])) {
        $class = "success";
        $message = $_GET["success"];

    } else if (isset($_GET["warning"])) {
        $class = "warning";
        $message = $_GET["warning"];

    }
    if ($message != "") {
    ?>
    <div class="container alert alert-<?= $class ?> alert-dismissible">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <?= $message ?>
    </div>
    <?php
    }
}
?>
<div class="unicorn-image"><img src="../assets/images/unicorn-with-cat.png"> </div>