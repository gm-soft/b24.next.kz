<?php
require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/help.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/classes/MysqlHelperClass.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/classes/UserClass.php");
//---------------------------------------------
if(!isset($_SESSION)) session_start();

if (!isset($_COOKIE["hash"])) {
    $_SESSION["error"] = array("Вы должны быть авторизованы на сайте");
    redirect("../session/login.php");
}

require_once($_SERVER["DOCUMENT_ROOT"]."/web/header.php");
$mysql = new MysqlHelper(DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
$data = $mysql->getUserData($_COOKIE["hash"], "hash");
$user = $data["result"] == true ? $data["data"] : null;

if (!is_null($user)) {
    ?>
    <div class="container">
        <h1>Профайл пользователя</h1>
        <div class="dl-horizontal">
            <dt>ID пользователя</dt><dd><?= $user->getId()?></dd>
            <dt>Логин</dt><dd><?= $user->getUsername()?></dd>
            <dt>Группа</dt><dd><?= $user->getUsergroup()?></dd>
            <dt>Создан</dt><dd><?= $user->getCreatedAt()?></dd>
        </div>
    </div>
    <?php
} else {
    $expired = time() - 3600;
    setcookie("username", "", $expired, "/");
    setcookie("hash", "", $expired, "/");
    setcookie("ts", "", $expired, "/");
    
    $_SESSION["error"] = array("Возникла какая-то ошибка. Пользователь не найден");
    redirect("../clients/index.php");
}

require_once($_SERVER["DOCUMENT_ROOT"]."/web/footer.php");