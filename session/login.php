<?php
require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/help.php");
require($_SERVER["DOCUMENT_ROOT"]."/Helpers/MysqlHelper.php");
require($_SERVER["DOCUMENT_ROOT"]."/Model/UserClass.php");
//---------------------------------------------
if(!isset($_SESSION)) session_start();


require_once($_SERVER["DOCUMENT_ROOT"]."/web/header.php");



if (!isset($_POST["login"])) {
    ?>
    <div class="container">
        <h1>Авторизация на сайте</h1>
        <form method="post" action="../session/login.php" class="form-horizontal">
            <div class="form-group">
                <label class="control-label col-sm-2" for="login">Логин:</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="login" name="login" placeholder="Введите свой логин"  maxlength="30" minlength="3" required>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-sm-2" for="pwd">Пароль:</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" id="pwd" name="password" placeholder="Введите пароль" required>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-default">Авторизоваться</button>
                </div>
            </div>

        </form>
    </div>
    <?php
} else {
    $err = array();
    $login = trim(htmlspecialchars(stripslashes($_POST["login"])));
    $pwd = trim(htmlspecialchars(stripslashes($_POST["password"])));

    //if (preg_match("/^[a-zA-Z0-9]+$/", $login)) $err[] = "Логин может состоять только из букв английского алфавита и цифр";
    //if (strlen($login) >30 || strlen($login) <3) $err[] = "Логин должен быть не меньше 3-х символов и не больше 30";


    $mysql = new MysqlHelper(DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);

    $data = $mysql->getUserData($login, "username");
    if (is_null($data["data"]) ) $err[] = "Пользователь с таким логином отсутствует в базе";

    $user = User::fromDatabase($data);
    $user->setHash(md5(User::generateCode(10)));
    //$user = $data["result"] == true && count($err) == 0 ? $data["data"] : null;
    $inputedPassword = md5(md5($pwd));

    if (!is_null($user) && $inputedPassword != $user->getPassword()) $err[] = "Пароль не совпадает";

    if(count($err) == 0)
    {
        $res = $mysql->updateUser($user);

        $ts = time();
        $expired  = time()+60*60*24*30;
        setcookie("username", $user->getUsername(), $expired, "/");
        setcookie("hash", $user->getHash(), $expired, "/");
        setcookie("ts", $ts, $expired, "/");

        $_SESSION["success"] = array("Вы успешно авторизовались");
        redirect("../clients/index.php");

    } else {
        $_SESSION["errors"] = $err;
        redirect("../session/login.php");
    }
    ?>

    <pre class="container"><?= var_export($data, true)?></pre>
    <?php
}

require_once($_SERVER["DOCUMENT_ROOT"]."/web/footer.php");