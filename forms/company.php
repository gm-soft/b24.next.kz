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
    //$userId = "72";

    $curr_user = BitrixHelper::getCurrentUser($auth_id);
    $_SESSION["user_name"] =  $curr_user["EMAIL"];
    $_SESSION["user_id"] =  $curr_user["ID"];
    $userId = $curr_user["ID"];

    $actionPerformed = isset($_REQUEST["action_performed"]) ? $_REQUEST["action_performed"] : "choose_company";
    switch ($actionPerformed) {


        case "choose_company":
        case "all_companies":


            $form_action = "company_contact.php";

            $companies = BitrixHelper::getCompanies($userId, $adminAuthToken);
            $header = "Мои компании";
            $url = "https://b24.next.kz/forms/company.php?auth_id=$auth_id&action=$action&action_performed=all_companies";
            $urlHeader = "Все компании";
            $desc = "В списке представлены компании/школы, где ответственный - Вы.";

            if ($actionPerformed == "all_companies" || count($companies) == 0){
                $companies = BitrixHelper::getCompanies(null, $adminAuthToken);
                $header = "Все компании";
                $desc = "В списке представлены все компании/школы, которые есть в Битрикс24";
                $url = "https://b24.next.kz/forms/company.php?auth_id=$auth_id&action=$action&action_performed=choose_company";
                $urlHeader = "Мои компании";
            }
            if (count($companies) == 0) {

                $_SESSION["errors"] = array("Компаний, где Вы назначены ответственным, не найдено");

                $url = "../forms/company.php?".
                    "auth_id=$auth_id&".
                    "action=$action&".
                    "action_performed=company_create";
                redirect($url);
            }
            // Если нет компаний в списке вообще, то редиректнуть на себя же, а в параметрах указать key = company_create

            require_once($_SERVER["DOCUMENT_ROOT"]."/forms/header.php");
            ?>
            <div class="container">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        <h1>Корпоративная продажа</h1>

                        <h3><?=$header ?></h3>
                        <p>Выберите компанию/школу из списка. <?= $desc ?></p>
                        <form id="form" class="form-horizontal" method="post" action="company_contact.php">

                            <input type="hidden" name="action" value="<?= $action ?>">
                            <input type="hidden" name="auth_id" value="<?= $auth_id ?>">
                            <input type="hidden" name="action_performed" value="company_defined">
                            <input type="hidden" name="admin_token" value="<?= $adminAuthToken ?>">

                            <p>Было найдено <?= count($companies) ?> компаний. Выберите нужную</p>
                            <div class="form-group">
                                <label class="control-label col-sm-2" for="company_id">Выберите компанию:</label>
                                <div class="col-sm-10">
                                    <select class="form-control" id="company_id" name="company_id" required>
                                    <option value="">Выберите компанию</option>
                                        <?php
                                        $i = 0;
                                        foreach ($companies as $key => $value) {

                                            $option =
                                                "<option value='".$value["ID"]."'>".$value["TITLE"].
                                                " [ID ".$value["ID"]."]</option>";
                                            echo $option;
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class=" form-group">
                                <div class="dropdown div-inline">
                                    <a class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">Дополнительные действия
                                    <span class="caret"></span></a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="https://b24.next.kz/forms/company.php?auth_id=<?= $auth_id ?>&action=<?=$action?>&action_performed=company_create">Создать новую компанию</a>
                                        </li>
                                        <li>
                                            <a href="<?= $url ?>"><?= $urlHeader ?></a>
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

        case "company_create":
            $form_action = "company.php";
            require_once($_SERVER["DOCUMENT_ROOT"]."/forms/header.php");
            ?>
            <div class="container">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        <h1>Корпоративная продажа</h1>

                        <h3>Создание компании/школы</h3>
                        <p>Введите необходимую информацию для создания новой компании</p>
                        <form id="form" class="form-horizontal" method="post" action="company.php">

                            <input type="hidden" name="action" value="<?= $action ?>">
                            <input type="hidden" name="auth_id" value="<?= $auth_id ?>">
                            <input type="hidden" name="action_performed" value="company_created">
                            <input type="hidden" name="admin_token" value="<?= $adminAuthToken ?>">

                            <div class="form-group">
                                <label class="control-label col-sm-2" for="company_title">Название компании: </label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="company_title" name="company_title" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-sm-2" for="phone">Контактный номер телефона:</label>
                                <div class="col-sm-10">
                                    <div class="input-group">
                                        <input type="tel" class="form-control" id="phone" name="phone" pattern="^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$" required placeholder="8(701)111-2233">
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
                                            <a href="https://b24.next.kz/forms/company.php?auth_id=<?= $auth_id ?>&action=<?=$action?>&action_performed=initiated">Мои компании</a>
                                        </li>
                                        <li>
                                            <a href="https://b24.next.kz/forms/company.php?auth_id=<?= $auth_id ?>&action=<?=$action?>&action_performed=all_companies">Выбрать компанию</a>
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

        case "company_created":
            $params = array(
                "fields[TITLE]" => $_REQUEST["company_title"],
                "fields[COMPANY_TYPE]"=> "CUSTOMER",
                "fields[OPENED]" => "Y",
                "fields[ASSIGNED_BY_ID]" => $userId,
                "fields[PHONE]" => $_REQUEST["phone"],
                "auth" => $adminAuthToken
            );
            $createResult = BitrixHelper::callMethod("crm.company.add", $params);
            $companyId = $createResult["result"];

            $url = "../forms/company_contact.php?".
                "auth_id=".$auth_id."&".
                "action=".$action."&".
                "action_performed=company_defined&".
                "company_id=".$companyId;
            redirect($url);

            break;

    }
?>

<?php




?>


<script>
    $("#company_id").select2();
    $('#form').submit(function(){
        $("#submit").prop('disabled',true); //
        $("a").addClass('disabled');
    });
</script>
<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/forms/footer.php");
?>
