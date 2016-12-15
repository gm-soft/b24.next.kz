<?php
require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/help.php");
require ($_SERVER["DOCUMENT_ROOT"]."/Helpers/BitrixHelperClass.php");

$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
$authId = isset($_REQUEST["authId"]) ? $_REQUEST["authId"] : null;
if (is_null($action)) {
    redirect("../sales/index.php?authId=<?= $authId ?>");
}



$adminAuthToken = isset($_REQUEST["adminToken"]) ? $_REQUEST["adminToken"] : get_access_data(true);

$curr_user = BitrixHelper::getCurrentUser($authId);
$_SESSION["user_name"] =  $curr_user["EMAIL"];
$_SESSION["user_id"] =  $curr_user["ID"];
$userId = $curr_user["ID"];

/*if (!isset($_REQUEST["company_id"])) {
    $url = "../sales/company.php?".
                "authId=$authId&".
                "action=$action&".
                "actionPerformed=choose_company";
    redirect($url);
}*/


$companyId = $_REQUEST["company_id"];
$actionPerformed = isset($_REQUEST["actionPerformed"]) ? $_REQUEST["actionPerformed"] : "initiated";
switch ($actionPerformed) {


    case "company_defined":
    case "choose_contact":
        $company = BitrixHelper::getCompany($companyId, $adminAuthToken);

        if ($actionPerformed == "choose_contact") {
            $contacts = [];
            foreach ($_REQUEST["contact_id"] as $key => $value) {
                $contacts[] = BitrixHelper::getContact($value, $adminAuthToken);
            }
            $desc = "В списке представлены контакты, найденные по номеру телефона. Выберите нужный";
        } else {
            $contacts = BitrixHelper::getContactsOfTheCompany($companyId, $adminAuthToken);
            $desc = "В списке представлены контакты, которые закреплены к выбранной компании";

            if (count($contacts) == 0) {
                $url = "../sales/company_contact.php?".
                    "authId=$authId&".
                    "action=$action&".
                    "actionPerformed=search_contact&".
                    "company_id=$companyId&".
                    "warning=<strong>Внимание!</strong> В компании нет прикрепленных контактов. Выберите нужный из списка или найдите по номеру телефона";
                redirect($url);
            }
        }


        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
        ?>
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <h1>Корпоративная продажа</h1>

                    <h3>Выбор контакта</h3>
                    <p>Выберите контакт из списка. <?= $desc ?></p>
                    <form id="form" class="form-horizontal" method="post" action="company_contact.php">

                        <input type="hidden" name="action" value="<?= $action ?>">
                        <input type="hidden" name="authId" value="<?= $authId ?>">
                        <input type="hidden" name="actionPerformed" value="contact_defined">
                        <input type="hidden" name="adminToken" value="<?= $adminAuthToken ?>">

                        <input type="hidden" name="company_id" value="<?= $company["ID"] ?>">

                        <div class="form-group">
                            <div class="col-sm-2">Выбранная компания:</div>
                            <div class="col-sm-10"><b><?= $company["TITLE"] ?> [ID <?= $company["ID"] ?>]</b></div>
                        </div>
                        <hr>
                        <p>Было найдено <?= count($contacts) ?> контактов. Выберите нужный</p>
                        <div class="form-group">
                            <label class="control-label col-sm-2" for="contact_id">Выберите контакт:</label>
                            <div class="col-sm-10">
                                <select class="form-control" id="contact_id" name="contact_id" required>
                                    <option value="">Выберите Контакт</option>
                                    <?php
                                    $i = 0;
                                    foreach ($contacts as $key => $value) {

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
                                        <a href="https://b24.next.kz/sales/company_contact.php?authId=<?= $authId ?>&action=<?=$action?>&actionPerformed=search_contact&company_id=<?= $companyId ?>">Найти контакт</a>
                                    </li>
                                    <li>
                                        <?php
                                        $url = "https://b24.next.kz/sales/company_contact.php?authId=$authId&action=$action&actionPerformed=create_contact&company_id=$companyId";
                                        ?>
                                        <a href="<?=$url?>">Создать контакт</a>
                                    </li>
                                    <li>
                                        <a href="/sales/index.php?authId=<?= $authId ?>">Отменить</a>
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
        
        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
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
                        <input type="hidden" name="authId" value="<?= $authId ?>">
                        <input type="hidden" name="actionPerformed" value="contact_found">
                        <input type="hidden" name="adminToken" value="<?= $adminAuthToken ?>">
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
                                        <a href="https://b24.next.kz/sales/company.php?authId=<?= $authId ?>&action=<?=$action?>&actionPerformed=choose_company">Выбрать компанию</a>
                                    </li>
                                    <li>
                                        <?php
                                        $url = "https://b24.next.kz/sales/company_contact.php?authId=$authId&action=$action&actionPerformed=company_defined&company_id=$companyId";
                                        ?>
                                        <a href="<?=$url?>">Выбрать контакт</a>
                                    </li>
                                    <li>
                                        <?php
                                        $url = "https://b24.next.kz/sales/company_contact.php?authId=$authId&action=$action&actionPerformed=create_contact&company_id=$companyId";
                                        ?>
                                        <a href="<?=$url?>">Создать контакт</a>
                                    </li>
                                    <li>
                                        <a href="/sales/index.php?authId=<?= $authId ?>">Отменить</a>
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
        $phone = isset($_REQUEST["contact_phone"]) ? BitrixHelper::formatPhone($_REQUEST["contact_phone"]) : "";
        //log_debug("PHONE = ".$_REQUEST["contact_phone"].". Count=".count($contacts)." ".var_export($contacts, true));
        if (count($contacts) == 0) {
            $url = "../sales/company_contact.php?".
                "authId=$authId&".
                "action=$action&".
                "actionPerformed=create_contact&".
                "company_id=$companyId&".
                "contact_phone=$phone&" .
                "error=<strong>Внимание!</strong> Контакт по номеру телефона не найден";
            redirect($url);
        }

        //$contact = $contacts[0];

        if (count($contacts) > 1){
            $url = "../sales/company_contact.php?".
                "authId=$authId&".
                "action=$action&".
                "actionPerformed=choose_contact&".
                // "contact_id=".$contact["ID"]."&".
                "company_phone=".$_REQUEST["contact_phone"]."&".
                "company_id=$companyId";
            for ($i = 0; $i < count($contacts); $i++){
                $contact = $contacts[$i];
                $url .= "&contact_id[$i]=".$contact["ID"];
            }
        } else {
            $url = "../sales/company_contact.php?".
                "authId=$authId&".
                "action=$action&".
                "actionPerformed=contact_defined&".
                "contact_id=".$contacts[0]["ID"]."&".
                "company_phone=".$_REQUEST["contact_phone"]."&".
                "company_id=$companyId";
        }


        redirect($url);
        break;

    case "create_contact":
        $company = BitrixHelper::getCompany($companyId, $adminAuthToken);
        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
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
                        <input type="hidden" name="authId" value="<?= $authId ?>">
                        <input type="hidden" name="actionPerformed" value="contact_created">
                        <input type="hidden" name="adminToken" value="<?= $adminAuthToken ?>">
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
                                        <a href="https://b24.next.kz/sales/company.php?authId=<?= $authId ?>&action=<?=$action?>&actionPerformed=choose_company">Выбрать компанию</a>
                                    </li>
                                    <li>
                                        <?php
                                        $url = "https://b24.next.kz/sales/company_contact.php?authId=$authId&action=$action&actionPerformed=company_defined&company_id=$companyId";
                                        ?>
                                        <a href="<?=$url?>">Выбрать контакт</a>
                                    </li>
                                    <li>
                                        <a href="/sales/index.php?authId=<?= $authId ?>">Отменить</a>
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

        $url = "../sales/company_contact.php?".
            "authId=$authId&".
            "action=$action&".
            "actionPerformed=contact_defined&".
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
                $formAction = "../sales/school/school.php";
                break;
            case 'corporate':
                $formAction = "../sales/corporate/corporate.php";
                break;
            default:
                $formAction = "";
                break;
        }

        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
        ?>
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <h1>Корпоративная продажа</h1>

                    <h3>Подтверждение контактов</h3>
                    <p>Проверьте верность выбранных контактных данных: компанию и контакт из CRM</p>

                    <div class="row">
                        <div class="col-sm-5">

                            <div class="panel panel-primary">
                                <div class="panel-heading panel-custom"><i><?= $company["TITLE"]?></i></div>
                                <div class="panel-body">
                                    Компания <a href="https://next.bitrix24.kz/crm/company/show/<?= $company["ID"]?>/">ID<?= $company["ID"]?></a><br>
                                    Телефон: <?= $company["PHONE"][0]["VALUE"]?>
                                </div>
                            </div>

                        </div>
                        <div class="col-sm-5 col-md-offset-2">

                            <div class="panel panel-primary">
                                <div class="panel-heading panel-custom"><i><?= $contact["NAME"]?> <?= $contact["LAST_NAME"]?></i></div>
                                <div class="panel-body">
                                    Контакт <a href="https://next.bitrix24.kz/crm/contact/show/<?= $contact["ID"]?>/">ID<?= $contact["ID"]?></a><br>
                                    Телефон: <?= $contact["PHONE"][0]["VALUE"]?>
                                </div>
                            </div>

                        </div>
                    </div>

                    <form id="form" class="form-horizontal" method="post" action="<?= $formAction ?>">

                        <input type="hidden" name="action" value="<?= $action ?>">
                        <input type="hidden" name="authId" value="<?= $authId ?>">
                        <!--input type="hidden" name="actionPerformed" value="contact_defined"-->
                        <input type="hidden" name="adminToken" value="<?= $adminAuthToken ?>">

                        <input type="hidden" name="companyId" value="<?= $company["ID"] ?>">
                        <input type="hidden" name="contactId" value="<?= $contact["ID"] ?>">

                        <div class="form-group">
                            <div class="dropdown div-inline">
                                <a class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">Дополнительные действия
                                <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="https://b24.next.kz/sales/company.php?authId=<?= $authId ?>&action=<?=$action?>&actionPerformed=choose_company">Выбрать компанию</a>
                                    </li>
                                    <li>
                                        <?php
                                        $url = "https://b24.next.kz/sales/company_contact.php?authId=$authId&action=$action&actionPerformed=company_defined&company_id=$companyId";
                                        ?>
                                        <a href="<?=$url?>">Выбрать контакт</a>
                                    </li>
                                    <li>
                                        <a href="/sales/index.php?authId=<?= $authId ?>">Отменить</a>
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
require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/footer.php");
?>
