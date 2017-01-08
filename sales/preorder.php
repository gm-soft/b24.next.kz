<?php
    require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");

    $action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
    $authId = isset($_REQUEST["authId"]) ? $_REQUEST["authId"] : null;

    if (is_null($authId)) {
        
        redirect("../sales/index.php");
    }

    $_REQUEST["adminToken"] = isset($_REQUEST["adminToken"]) ? $_REQUEST["adminToken"] : get_access_data(true);

    $curr_user = BitrixHelper::getCurrentUser($authId);
    $_SESSION["user_name"] =  $curr_user["EMAIL"];
    $_SESSION["user_id"] =  $curr_user["ID"];
    $userId = $curr_user["ID"];



    $actionPerformed = isset($_REQUEST["actionPerformed"]) ? $_REQUEST["actionPerformed"] : "initiated";
    //-------------------------------------------------------------------------
    //-------------------------------------------------------------------------
    switch ($actionPerformed) {

        case "initiated":
            $url = "../sales/contact2.php?action=preorder&authId=$authId";
            redirect($url);
            break;

        case "contact_defined":
        case "contactDefined":
            $contacts = BitrixHelper::searchContact(BitrixHelper::formatPhone($_REQUEST["contact_phone"]), $_REQUEST["adminToken"]);
            $curr_user = isset($curr_user) ? $curr_user : BitrixHelper::getCurrentUser($authId);
            $url = "https://script.google.com/macros/s/AKfycbxjyTPPbRdVZ-QJKcWLFyITXIeQ1GwI7fAi0FgATQ0PsoGKAdM/exec";

            $parameters = array(
                "event" => "OnPreorderCreateRequested",
                "contact_id" => $_REQUEST["contactId"],
                "contact_name" => "",
                "last_name" => "",
                "contact_phone" => "",
                "parent" => "",
                "birthday" => "",

                "user_id" => $curr_user["ID"],
                "user_name" => $curr_user["LAST_NAME"]." ".$curr_user["NAME"],
            );

            $process_data = query("GET", $url, $parameters);
            $process_data = isset($process_data["result"]) ? $process_data["result"] : $process_data;
            require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
            if (!is_null($process_data) ) {

                $deal_id = $process_data["deal_id"];
                $order_id = $process_data["order_id"];
                $contact_id = $process_data["contact_id"];

                $contact_created = $process_data["contact_created"];
                $contact_full_name = $process_data["contact_full_name"];

                ?>
                <div class="container">
                    <h2>Результат операции "Создание предзаказника"</h2>
                    <p><?= $contact_created == true ? "Были созданы контакт и сделка" : "Была создана сделка";  ?></p>

                    <table class="table table-striped">
                        <tbody>
                        <tr>
                            <td>Номер заказа консолидации 9-1</td>
                            <td><b>ID<?= $order_id?></b></td>
                        </tr>
                        <tr>
                            <td>Клиент</td>
                            <td><a href="https://next.bitrix24.kz/crm/contact/show/<?= $contact_id ?>/" target="_blank"><?= $contact_full_name ?></a></td>
                        </tr>
                        <tr>
                            <td>Сделка</td>
                            <td><a href="https://next.bitrix24.kz/crm/deal/show/<?= $deal_id ?>/" target="_blank"><?= $deal_id ?></a></td>
                        </tr>
                        </tbody>
                    </table>

                </div>

                <?php
            } else {
                ?>
                <div class="container">
                    <div class="alert alert-danger">
                        <strong>Внимание!</strong> Возникла какая-то ошибка. Повторите попытку позднее
                    </div>
                </div>

                <?php
            }
            break;

        default:
            echo $actionPerformed;
            break;
    }

    ?>

        
    <?php

    require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/footer.php");

    ?>