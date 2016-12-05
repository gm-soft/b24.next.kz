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
        <h1>Регистрация</h1>
        <form method="post" action="../session/register.php" class="form-horizontal">
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
                <label class="control-label col-sm-2" for="pwdconf">Подтвердите пароль:</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" id="pwdconf" name="password_confirm" placeholder="Введите пароль повторно" required>
                    <span id='pwd_message'></span>
                </div>
                
            </div>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-default">Зарегистрироваться</button>
                </div>
            </div>

        </form>
    </div>
    <script>
        $('#pwd, #pwdconf').on('keyup', function () {
            if ($('#pwd').val() == $('#pwdconf').val()) {
                $('#pwd_message').html('Пароли совпадают').css('color', 'green');
                } else 
                $('#pwd_message').html('Пароли не совпадают').css('color', 'red');
        });

    </script>


    <?php
} else {
    $err = array();
    $login = trim(htmlspecialchars(stripslashes($_POST["login"])));
    $pwd = trim(htmlspecialchars(stripslashes($_POST["password"])));
    $pwd_conf = trim(htmlspecialchars(stripslashes($_POST["password_confirm"])));

    //if (preg_match("/^[a-zA-Z0-9]+$/", $login)) $err[] = "Логин может состоять только из букв английского алфавита и цифр";
    if ($pwd != $pwd_conf) $err[] = "Введенные пароли не совпадают";
    if (strlen($login) >30 || strlen($login) <3) $err[] = "Логин должен быть не меньше 3-х символов и не больше 30";


    $mysql = new MysqlHelper(DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);

    $data = $mysql->getUserData($login, "username");
    if ($data["result"] == true && !is_null($data["data"]) ) $err[] = "Пользователь с таким логином уже существует в базе данных";
    if(count($err) == 0)

    {
        $password = md5(md5($pwd));
        $hash = md5(generateCode(10));
        $newUser = UserClass::withUserdata($login, $password, $hash);

        $res = $mysql->addUser($newUser);

        if ($res["result"] == true) {

            $newUser->setId($res["data"]);

            $ts = time();
            $expired  = time()+60*60*24*30;
            setcookie("username", $newUser->getUsername(), $expired, "/");
            setcookie("hash", $newUser->getHash(), $expired, "/");
            setcookie("ts", $ts, $expired, "/");

            $_SESSION["success"] = array("Вы успешно зарегистрировались на сайте");
            redirect("../clients/index.php");
        } else {

            $_SESSION["errors"] = array($res["data"]);
            redirect("../session/register.php");
        }
    } else {
        $_SESSION["errors"] = $err;
        redirect("../session/register.php");
    }
    ?>

    <pre class="container"><?= var_export($data, true)?></pre>
    <?php
}

require_once($_SERVER["DOCUMENT_ROOT"]."/web/footer.php");