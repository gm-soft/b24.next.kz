<?php
require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/help.php");
require ($_SERVER["DOCUMENT_ROOT"]."/Helpers/BitrixHelperClass.php");

$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
$auth_id = isset($_REQUEST["auth_id"]) ? $_REQUEST["auth_id"] : null;
if (is_null($action)) {
    redirect("../forms/index.php?auth_id=<?= $auth_id ?>");
}



$adminAuthToken = isset($_REQUEST["admin_token"]) ? $_REQUEST["admin_token"] : get_access_data(true);

$curr_user = BitrixHelper::getCurrentUser($auth_id);
$_SESSION["user_name"] =  $curr_user["EMAIL"];
$_SESSION["user_id"] =  $curr_user["ID"];
$userId = $curr_user["ID"];

/*if (!isset($_REQUEST["company_id"])) {
    $url = "../forms/company.php?".
                "auth_id=$auth_id&".
                "action=$action&".
                "action_performed=choose_company";
    redirect($url);
}*/


$companyId = $_REQUEST["company_id"];
$actionPerformed = isset($_REQUEST["action_performed"]) ? $_REQUEST["action_performed"] : "initiated";
switch ($actionPerformed) {


    case "company_defined":
        $companyContacts = BitrixHelper::getContactsOfTheCompany($companyId, $adminAuthToken);
        $desc = "В списке представлены контакты, которые закреплены к выбранной компании";

        if (count($companyContacts) == 0) {

            $url = "../forms/company_contact.php?".
                "auth_id=$auth_id&".
                "action=$action&".
                "action_performed=search_contact&".
                "company_id=$companyId&".
                "warning=<strong>Внимание!</strong> В компании нет прикрепленных контактов. Выберите нужный из списка или найдите по номеру телефона";
            redirect($url);
        }

        $company = BitrixHelper::getCompany($companyId, $adminAuthToken);

        require_once($_SERVER["DOCUMENT_ROOT"]."/forms/header.php");
        ?>
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <h1>Корпоративная продажа</h1>

                    <h3>Выбор контакта</h3>
                    <p>Выберите контакт из списка. <?= $desc ?></p>
                    <form id="form" class="form-horizontal" method="post" action="company_contact.php">

                        <input type="hidden" name="action" value="<?= $action ?>">
                        <input type="hidden" name="auth_id" value="<?= $auth_id ?>">
                        <input type="hidden" name="action_performed" value="contact_defined">
                        <input type="hidden" name="admin_token" value="<?= $adminAuthToken ?>">

                        <input type="hidden" name="company_id" value="<?= $company["ID"] ?>">

                        <div class="form-group">
                            <div class="col-sm-2">Выбранная компания:</div>
                            <div class="col-sm-10"><b><?= $company["TITLE"] ?> [ID <?= $company["ID"] ?>]</b></div>
                        </div>
                        <hr>
                        <p>Было найдено <?= count($companyContacts) ?> контактов. Выберите нужный</p>
                        <div class="form-group">
                            <label class="control-label col-sm-2" for="contact_id">Выберите контакт:</label>
                            <div class="col-sm-10">
                                <select class="form-control" id="contact_id" name="contact_id" required>
                                    <option value="">Выберите Контакт</option>
                                    <?php
                                    $i = 0;
                                    foreach ($companyContacts as $key => $value) {

                                        $option =
                                            "<option value=\"".$value["ID"]."\">".$value["NAME"]." ".$value["LAST_NAME"].
                                            " [".$value["PHONE"][0]["VALUE"]."]</option>";
                                        echo $option;
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="dropdown div-inline">
                                <a class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">Дополнительные действия
                                <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="https://b24.next.kz/forms/company_contact.php?auth_id=<?= $auth_id ?>&action=<?=$action?>&action_performed=search_contact&company_id=<?= $companyId ?>">Найти контакт</a>
                                    </li>
                                    <li>
                                        <?php
                                        $url = "https://b24.next.kz/forms/company_contact.php?auth_id=$auth_id&action=$action&action_performed=create_contact&company_id=$companyId";
                                        ?>
                                        <a href="<?=$url?>">Создать контакт</a>
                                    </li>
                                    <li>
                                        <a href="../forms/index.php?auth_id=<?= $auth_id ?>">Отменить</a>
                                    </li>
                                </ul>
                            </div>
                            <button id="submit" type="submit" class="btn btn-primary">Далее</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <?php
        break;

    case "search_contact":

        $company = BitrixHelper::getCompany($companyId, $adminAuthToken);
        
        require_once($_SERVER["DOCUMENT_ROOT"]."/forms/header.php");
        ?>
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <h1>Корпоративная продажа</h1>

                    <h3>Поиск контакта по телефону</h3>
                    <p>Выбранная компания: <b><?= $company["TITLE"] ?> [ID <?= $company["ID"] ?>]</b></p>
                    <hr>


                    <p>Введите номер телефона контакта для поиска в системе CRM Б24</p>
                    <form id="form" class="form-horizontal" method="post" action="company_contact.php">

                        <input type="hidden" name="action" value="<?= $action ?>">
                        <input type="hidden" name="auth_id" value="<?= $auth_id ?>">
                        <input type="hidden" name="action_performed" value="contact_found">
                        <input type="hidden" name="admin_token" value="<?= $adminAuthToken ?>">
                        <input type="hidden" name="company_id" value="<?= $companyId ?>">

                        <div class="form-group">
                            <label class="control-label col-sm-2" for="contact_phone">Номер телефона:</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <input type="tel" class="form-control" id="contact_phone" name="contact_phone" pattern="^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$"
                                           required placeholder="8(701)111-2233">
                                    <span class="input-group-addon"> <i class="glyphicon glyphicon-earphone"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="dropdown div-inline">
                                <a class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">Дополнительные действия
                                <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="https://b24.next.kz/forms/company.php?auth_id=<?= $auth_id ?>&action=<?=$action?>&action_performed=choose_company">Выбрать компанию</a>
                                    </li>
                                    <li>
                                        <?php
                                        $url = "https://b24.next.kz/forms/company_contact.php?auth_id=$auth_id&action=$action&action_performed=company_defined&company_id=$companyId";
                                        ?>
                                        <a href="<?=$url?>">Выбрать контакт</a>
                                    </li>
                                    <li>
                                        <?php
                                        $url = "https://b24.next.kz/forms/company_contact.php?auth_id=$auth_id&action=$action&action_performed=create_contact&company_id=$companyId";
                                        ?>
                                        <a href="<?=$url?>">Создать контакт</a>
                                    </li>
                                    <li>
                                        <a href="../forms/index.php?auth_id=<?= $auth_id ?>">Отменить</a>
                                    </li>
                                </ul>
                            </div>
                            <button id="submit" type="submit" class="btn btn-primary">Найти контакт</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
        <?php
        break;

    case "contact_found":
        $contacts =  BitrixHelper::searchContact(BitrixHelper::formatPhone($_REQUEST["contact_phone"]) ,$adminAuthToken);

        //log_debug("PHONE = ".$_REQUEST["contact_phone"].". Count=".count($contacts)." ".var_export($contacts, true));
        if (count($contacts) == 0) {
            $url = "../forms/company_contact.php?".
                "auth_id=$auth_id&".
                "action=$action&".
                "action_performed=create_contact&".
                "company_id=$companyId&".
                "error=<strong>Внимание!</strong> Контакт по номеру телефона не найден";
            redirect($url);
        }

        $contact = $contacts[0];
        log_debug(var_export($contact, true));
        $url = "../forms/company_contact.php?".
            "auth_id=$auth_id&".
            "action=$action&".
            "action_performed=contact_defined&".
            "contact_id=".$contact["ID"]."&".
            "company_phone=".$_REQUEST["contact_phone"]."&".
            "company_id=$companyId&".
            "success=Контакт найден. Подтердите верность введенной информации";
        redirect($url);
        break;

    case "create_contact":
        $company = BitrixHelper::getCompany($companyId, $adminAuthToken);
        require_once($_SERVER["DOCUMENT_ROOT"]."/forms/header.php");
        ?>
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <h1>Корпоративная продажа</h1>

                    <h3>Создание нового контакта</h3>
                    <p>Выбранная компания: <b><?= $company["TITLE"] ?> [ID <?= $company["ID"] ?>]</b></p>
                    <hr>


                    <p>Введите необходимые данные для создания нового контакта в системе CRM Б24</p>
                    <form id="form" class="form-horizontal" method="post" action="company_contact.php">

                        <input type="hidden" name="action" value="<?= $action ?>">
                        <input type="hidden" name="auth_id" value="<?= $auth_id ?>">
                        <input type="hidden" name="action_performed" value="contact_created">
                        <input type="hidden" name="admin_token" value="<?= $adminAuthToken ?>">
                        <input type="hidden" name="company_id" value="<?= $companyId ?>">

                        <div class="form-group">
                            <label class="control-label col-sm-2" for="name">Имя:</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="name" name="name" required >
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-sm-2" for="last_name">Фамилия:</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="last_name" name="last_name" required >
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-sm-2" for="contact_phone">Номер телефона:</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <input type="tel" class="form-control" id="contact_phone" name="contact_phone" pattern="^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$"
                                           required value="<?= BitrixHelper::formatPhone($_REQUEST["contact_phone"]) ?>" >
                                    <span class="input-group-addon"> <i class="glyphicon glyphicon-earphone"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="dropdown div-inline">
                                <a class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">Дополнительные действия
                                <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="https://b24.next.kz/forms/company.php?auth_id=<?= $auth_id ?>&action=<?=$action?>&action_performed=choose_company">Выбрать компанию</a>
                                    </li>
                                    <li>
                                        <?php
                                        $url = "https://b24.next.kz/forms/company_contact.php?auth_id=$auth_id&action=$action&action_performed=company_defined&company_id=$companyId";
                                        ?>
                                        <a href="<?=$url?>">Выбрать контакт</a>
                                    </li>
                                    <li>
                                        <a href="../forms/index.php?auth_id=<?= $auth_id ?>">Отменить</a>
                                    </li>
                                </ul>
                            </div>
                            <button id="submit" type="submit" class="btn btn-primary">Создать контакт</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php
        break;

    case "contact_created":
        $params = array(
            "fields[NAME]" => $_REQUEST["name"],
            "fields[LAST_NAME]"=> $_REQUEST["last_name"],
            "fields[OPENED]" => "Y",
            "fields[SOURCE_ID]" => "SELF",
            "fields[TYPE_ID]" => "CLIENT",
            "fields[PHONE][0][VALUE]" => $_REQUEST["contact_phone"],
            "fields[PHONE][0][VALUE_TYPE]" => "WORK",
            "fields[ASSIGNED_BY_ID]" => $userId,
            "auth" => $adminAuthToken
        );
        $createResult = BitrixHelper::callMethod("crm.contact.add", $params);
        $contactId = $createResult["result"];

        $url = "../forms/company_contact.php?".
            "auth_id=$auth_id&".
            "action=$action&".
            "action_performed=contact_defined&".
            "company_id=$companyId&".
            "contact_id=$contactId&".
            "success=Создан новый контакт ".$_REQUEST["name"]." ".$_REQUEST["last_name"]." [$contactId]";
        redirect($url);

        break;

    case "contact_defined":
        $contact = BitrixHelper::getContact($_REQUEST["contact_id"], $adminAuthToken);
        $company = BitrixHelper::getCompany($_REQUEST["company_id"], $adminAuthToken);

        switch ($action) {
            case 'school':
                $formAction = "school.php";
                break;
            case 'corporate':
                $formAction = "corporate.php";
                break;
            default:
                $formAction = "";
                break;
        }

        require_once($_SERVER["DOCUMENT_ROOT"]."/forms/header.php");
        ?>
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <h1>Корпоративная продажа</h1>

                    <h3>Подтверждение контактов</h3>
                    <p>Проверьте верность выбранных контактных данных: компанию и контакт из CRM</p>

                    <div class="row">
                        <div class="col-sm-4">
                            <h3><?= $company["TITLE"]?></h3>
                            <p>Компания <a href="https://next.bitrix24.kz/crm/company/show/<?= $company["ID"]?>/">ID<?= $company["ID"]?></a></p>
                            <p>Телефон: <?= $company["PHONE"][0]["VALUE"]?></p>
                        </div>
                        <div class="col-sm-4 col-md-offset-2">
                            <h3><?= $contact["NAME"]?> <?= $contact["LAST_NAME"]?></h3>
                            <p>Контакт <a href="https://next.bitrix24.kz/crm/contact/show/<?= $contact["ID"]?>/">ID<?= $contact["ID"]?></a></p>
                            <p>Телефон: <?= $contact["PHONE"][0]["VALUE"]?></p>
                        </div>
                    </div>

                    <form id="form" class="form-horizontal" method="post" action="<?= $formAction ?>">

                        <input type="hidden" name="action" value="<?= $action ?>">
                        <input type="hidden" name="auth_id" value="<?= $auth_id ?>">
                        <!--input type="hidden" name="action_performed" value="contact_defined"-->
                        <input type="hidden" name="admin_token" value="<?= $adminAuthToken ?>">

                        <input type="hidden" name="company_id" value="<?= $company["ID"] ?>">
                        <input type="hidden" name="contact_id" value="<?= $contact["ID"] ?>">

                        <div class="form-group">
                            <div class="dropdown div-inline">
                                <a class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">Дополнительные действия
                                <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="https://b24.next.kz/forms/company.php?auth_id=<?= $auth_id ?>&action=<?=$action?>&action_performed=choose_company">Выбрать компанию</a>
                                    </li>
                                    <li>
                                        <?php
                                        $url = "https://b24.next.kz/forms/company_contact.php?auth_id=$auth_id&action=$action&action_performed=company_defined&company_id=$companyId";
                                        ?>
                                        <a href="<?=$url?>">Выбрать контакт</a>
                                    </li>
                                    <li>
                                        <a href="../forms/index.php?auth_id=<?= $auth_id ?>">Отменить</a>
                                    </li>
                                </ul>
                            </div>
                            <button id="submit" type="submit" class="btn btn-primary">Далее</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <?php


        break;

    default:
        echo "actionPerformed (".$actionPerformed.") hast not been found";
        break;

}
?>

<?php




?>


<script>
    $("#contact_id").select2();
    $('#form').submit(function(){
        $("#submit").prop('disabled',true);
        $("a").addClass('disabled');
    });
</script>
<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/forms/footer.php");
?>