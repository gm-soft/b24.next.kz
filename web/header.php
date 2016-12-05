
<?php $pageTitle = isset($pageTitle) ? $pageTitle : "NEXT.Events"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="../assets/css/select2.min.css">


    <script src="../assets/js/jquery_1.12.4.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/select2.min.js"></script>
    <script src="../assets/js/custom.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <title><?= $pageTitle ?> </title>
</head>
<body>

<nav class="navbar navbar-inverse">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="../web/index.php">NEXT.Events 2.0</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Менеджерам<span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="https://goo.gl/C7DYv5" target="_blank">Консолидация заказов</a></li>
                        <li><a href="https://goo.gl/sgk0S1" target="_blank">ТЗФ таблица заказа фуршета</a></li>
                        <li><a href="https://goo.gl/9V7ekp" target="_blank">ТЗС таблица заказа сувенирки</a></li>
                        <li><a href="https://telegram.me/next_assistent_bot" target="_blank">Создать диалог с Некстоном</a></li>
                    </ul>
                </li>
                <!-- Меню для доступа к старой версии системы -->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Таблицы<span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="https://goo.gl/90OhcC" target="_blank">9-2 База данных</a></li>
                        <li><a href="https://goo.gl/wTF4mE" target="_blank">9-3 Аккаунты лояльности</a></li>
                        <li><a href="https://docs.google.com/spreadsheets/d/1nSMwp3ErhfsG58R-fJ_Mc3mPM55V_MfcPN3bNfldRac/edit#gid=1423067290" target="_blank">9-4 Пользователи</a></li>   
                        <li><a href="https://goo.gl/A2jiYJ" target="_blank">Таблица запросов создания</a></li>
                        <li><a href="https://goo.gl/UMKMUj" target="_blank">Таблица запросов изменения</a></li>
                        <li><a href="https://goo.gl/L6gegV" target="_blank">Таблица запросов пост-обработки</a></li> 
                        <li><a href="https://docs.google.com/spreadsheets/d/1X2_2bnujiF_2xgcJo-4O1U_IQ45kIq17CdCj5-YK2i4/edit" target="_blank">Таблица корп.продаж и бусов</a></li> 
                        <li><a href="https://docs.google.com/spreadsheets/d/1HYFcGfnn138rUt7JEioQtgQVATUgfJAyNfxbF6ZZF8I/edit " target="_blank">Таблица цен на бусы</a></li> 
                        
                        
                                   
                    </ul>
                </li>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Разработчикам<span class="caret"></span></a>
                    <ul class="dropdown-menu"> 
                        <li><a href="https://script.google.com/a/next.kz/d/18JRghzG3Rp8uQ03bwPznZFmjbJfxtC5RJHmW4JtyNUJZ9wsnhDJkDzf2/edit" target="_blank">Скрипт-файл</a></li>  
                        <li><a href="https://docs.google.com/spreadsheets/d/18xiXT4uqU6pgBOCVYJgSUEPdtHxSD3maY7l4QBtlKRk/edit" target="_blank">Таблица управления бот-сервером</a></li>  
                        <li><a href="https://b24.next.kz/server/" >Сервер авторизации next</a></li>                         
                        <li><a href="http://newb24.next.kz/server/" >Сервер авторизации habb</a></li>
                                   
                    </ul>
                </li>
            </ul>

            <ul class="nav navbar-nav navbar-right">
              <?php
                $username = $_COOKIE["username"];
                $ts = $_COOKIE["ts"];

                if( !isset($_COOKIE["username"]) || (time() - $ts) > 3600)
                {
                ?>
                    <li><a href="../session/register.php"><span class="glyphicon glyphicon-log-plus"></span> Регистрация</a></li>
                    <li><a href="../session/login.php"><span class="glyphicon glyphicon-log-in"></span> Войти</a></li>

                <?php
                } else {
                    ?>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-user"></span> <?= $username ?>
                            <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="../session/profile.php"><span class="glyphicon glyphicon-cog"></span> Профайл</a></li>
                            <li><a href="../session/logout.php"><span class="glyphicon glyphicon-log-out"></span> Выйти</a></li>

                        </ul>
                    </li>


              <?php 
                }
                ?>
          </ul>


        </div><!--/.navbar-collapse -->
    </div>
</nav>


<?php
  if (isset($_SESSION["errors"])) {
    ?>
    <div class="container alert alert-danger alert-dismissible">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <?php
        foreach ($_SESSION["errors"] as $key => $value){
            echo $value."<br>";
        }
        ?>
    </div>
    <?php
    unset($_SESSION["errors"]);
}

if (isset($_SESSION["success"])) {
    ?>
    <div class="container alert alert-success alert-dismissible">
      <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <?php
        foreach ($_SESSION["success"] as $key => $value){
            echo $value."<br>";
        }
        ?>
    </div>
    <?php
    unset($_SESSION["success"]);
}
?>