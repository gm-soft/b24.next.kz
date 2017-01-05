<?php
	require($_SERVER["DOCUMENT_ROOT"] . "/include/config.php");
	require($_SERVER["DOCUMENT_ROOT"] . "/include/help.php");
    require($_SERVER["DOCUMENT_ROOT"] . "/Helpers/BitrixHelperClass.php");


	$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
	$authId = isset($_REQUEST["authId"]) && !empty($_REQUEST["authId"]) ? $_REQUEST["authId"] : null;
	if (!isset($_REQUEST["authId"]) || empty($_REQUEST["authId"])) {
		redirect("https://b24.next.kz/sales/index.php");
	}


    $adminAuthToken = isset($_REQUEST["adminToken"]) ? $_REQUEST["adminToken"] : get_access_data(true);

    $curr_user = BitrixHelper::getCurrentUser($authId);
    $_SESSION["user_name"] =  $curr_user["EMAIL"];
    $_SESSION["user_id"] =  $curr_user["ID"];
    $userId = $curr_user["ID"];




	switch ($action) {
		case "booth":
			$form_action = "booth.php";
			$form_title = "Продажа аренды буса";
			break;
		case "preorder":
			$form_action = "preorder.php";
			$form_title = "Создание предзаказника";
			break;
	}

	$actionPerformed = isset($_REQUEST["actionPerformed"]) ? $_REQUEST["actionPerformed"] : "initiated";

	switch ($actionPerformed){
        case "initiated":
            require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
            ?>
            <div class="container">
                <h2><?= $form_title ?></h2>
                <p>Введите номер телефона. Будет осуществлен поиск существующего контакта по этому номеру. Если номер телефона не будет найден, то будет создан новый контакт в Б24</p>
                <form  id="form" class="form-horizontal" method="post" action="">
                    <input type="hidden" name="action" value="<?= $action ?>">
                    <input type="hidden" name="authId" value="<?= $authId ?>">
                    <input type="hidden" name="actionPerformed" value="contact_inputed">
                    <input type="hidden" name="adminToken" value="<?= $adminAuthToken ?>">

                    <div class="form-group">
                        <label class="control-label col-sm-2" for="phone">Номер телефона:</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <input type="tel" class="form-control" id="phone" name="contact_phone" pattern="^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$" required placeholder="8(701)111-2233">
                                <span class="input-group-addon"> <i class="glyphicon glyphicon-earphone"></i></span>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="contact_name" value="">
                    <input type="hidden" name="last_name" value="">

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-primary">Найти по номеру телефона</button>
                        </div>
                    </div>
                </form>

            </div>
            <?php
            break;
        case "contact_inputed":
            $phone = BitrixHelper::formatPhone($_REQUEST["contact_phone"]);
            $contacts = BitrixHelper::searchContact($phone, $adminAuthToken);

            if (count($contacts) > 0) {
                require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
            ?>
            <div class="container">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        <form id="form" class="form-horizontal" method="post" action="<?= $form_action ?>">
                            <input type="hidden" name="actionPerformed" value="contact_defined">
                            <input type="hidden" name="action" value="<?= $action ?>">
                            <input type="hidden" name="authId" value="<?= $authId ?>">
                            <input type="hidden" name="adminToken" value="<?= $adminAuthToken ?>">

                            <h3>Выбор существующего контакта</h3>
                            <p>Контактов с указанным номером телефона в базе данных: <?= count($contacts) ?>. Выберите нужный</p>
                            <input type="hidden" name="contact_name" value="">
                            <input type="hidden" name="last_name" value="">
                            <input type="hidden" name="contact_phone" value="">
                            <input type="hidden" name="parent" value="">
                            <input type="hidden" name="birthday" value="">

                            <div class="form-group">
                                <label class="control-label col-sm-2" for="contact_id">Выберите контакт:</label>
                                <div class="col-sm-10">
                                    <select class="form-control" id="contact_id" name="contact_id" required>
                                        <?php
                                        $i = 0;
                                        foreach ($contacts as $key => $value) {

                                            $selected = $i == 0 ? "selected" : "";
                                            $option =
                                                "<option value=\"".$value["ID"]."\" ".$selected.">".$value["NAME"]." ".$value["LAST_NAME"].
                                                " (".$value["PHONE"][0]["VALUE"].") [".$value["ID"]."]</option>";
                                            echo $option;
                                            $i++;
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <a href="https://b24.next.kz/sales/index.php?authId=<?= $authId ?>" type="button" class="btn btn-link"><< В главное меню</a>
                                    <button type="submit" id="submit-btn" class="btn btn-primary">Далее</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php
            } else {
                $url = "../sales/contact.php?" .
                    "authId=$authId&" .
                    "action=$action&" .
                    "actionPerformed=contact_create&".
                    "contact_phone=$phone&" .
                    "error=Ни одного контакта с указанным телефоном не найдено. Создайте новый";
                redirect($url);
            }
            break;

        case "contact_create":
            $phone = isset($_REQUEST["contact_phone"]) ? BitrixHelper::formatPhone($_REQUEST["contact_phone"]) : "";
            require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
            ?>
            <div class="container">
                <h2><?= $form_title ?></h2>
                <p>Создайте новый контакт в системе CRM</p>
                <form id="form" class="form-horizontal" method="post" action="">
                    <input type="hidden" name="action" value="<?= $action ?>">
                    <input type="hidden" name="authId" value="<?= $authId ?>">
                    <input type="hidden" name="actionPerformed" value="contact_created">
                    <input type="hidden" name="adminToken" value="<?= $adminAuthToken ?>">

                    <h3>Создание нового контакта</h3>
                    <p>Контакт не был найден по номеру телефона. Будет создан новый контакт</p>
                    <input type="hidden" name="contact_id" value="">
                    <div class="form-group">
                        <label class="control-label col-sm-2" for="contact_name">Имя:</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="contact_name" name="contact_name" required
                                   value="<?= $_REQUEST["contact_name"] ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-2" for="last_name">Фамилия:</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="last_name" name="last_name" required
                                   value="<?= $_REQUEST["last_name"] ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-2" for="contact_phone">Телефон:</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="contact_phone" name="contact_phone" required value="<?= $phone ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-2" for="parent">Родитель (представитель ребенка):</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="parent" name="parent">
                        </div>
                    </div>

                    <div class="form-group has-feedback">
                        <label class="control-label col-sm-2" for="birthday">Дата рождения:</label>
                        <div class="col-sm-10">
                            <input type="date" class="form-control" id="birthday" name="birthday" required placeholder="Выберите дату">
                            <span class="glyphicon glyphicons-calendar form-control-feedback"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <a href="https://b24.next.kz/sales/index.php?authId=<?= $authId ?>" type="button" class="btn btn-link"><< В главное меню</a>
                            <button type="submit" id="submit-btn" class="btn btn-primary">Далее</button>
                        </div>
                    </div>
                </form>

            </div>
            <?php


            break;

        case "contact_created":

            $birthday = $_REQUEST["birthday"];
            $birthAtom = $birthday."T12:00+03:00";
            $params = array(
                "fields[NAME]" => $_REQUEST["contact_name"],
                "fields[LAST_NAME]"=> $_REQUEST["last_name"],
                "fields[OPENED]" => "Y",
                "fields[SOURCE_ID]" => "SELF",
                "fields[TYPE_ID]" => "CLIENT",
                "fields[BIRTHDATE]" => $birthAtom,
                "fields[UF_CRM_1468207818]" => array(0 => $_REQUEST["parent"]),
                "fields[PHONE][0][VALUE]" => $_REQUEST["contact_phone"],
                "fields[PHONE][0][VALUE_TYPE]" => "WORK",
                "fields[ASSIGNED_BY_ID]" => $userId,
                "auth" => $adminAuthToken
            );
            $createResult = BitrixHelper::callMethod("crm.contact.add", $params);
            $contactId = $createResult["result"];

            $url = "../sales/$form_action?".
                "authId=$authId&".
                "action=$action&".
                "actionPerformed=contact_defined&".
                "contactId=$contactId&".
                "success=Создан новый контакт ".$_REQUEST["name"]." ".$_REQUEST["last_name"]." [$contactId]";
            redirect($url);


            break;
    }
    ?>
    <script type="text/javascript">
        $('#form').submit(function(){
            $("#submit").prop('disabled',true);
            $('#alert').append("<strong>Внимание!</strong> Идет обрабокта информации. Не закрывайте окно!");
        });
    </script>
    <?php
	require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/footer.php");
?>
