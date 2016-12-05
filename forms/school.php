<?php
    require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
    require($_SERVER["DOCUMENT_ROOT"]."/include/help.php");
    require($_SERVER["DOCUMENT_ROOT"]."/Helpers/BitrixHelperClass.php");

    $action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
    $auth_id = isset($_REQUEST["auth_id"]) ? $_REQUEST["auth_id"] : null;

    if (is_null($action)) {
        redirect("../forms/index.php?auth_id=".$auth_id);
    }

    $adminAuthToken = isset($_REQUEST["admin_token"]) ? $_REQUEST["admin_token"] : get_access_data(true);



    $curr_user = BitrixHelper::getCurrentUser($auth_id);
    $_SESSION["user_name"] =  $curr_user["EMAIL"];
    $_SESSION["user_id"] =  $curr_user["ID"];
    $userId = $curr_user["ID"];

    $contacts = BitrixHelper::searchContact(BitrixHelper::formatPhone($_REQUEST["contact_phone"]), $adminAuthToken);


    //-------------------------------------------------------------------------
    //-------------------------------------------------------------------------
    $actionPerformed = isset($_REQUEST["action_performed"])  ? $_REQUEST["action_performed"] : "initiated";
    $contactId = $_REQUEST["contact_id"];
    $companyId = $_REQUEST["company_id"];

    switch ($actionPerformed) {
        case "initiated":
            $company = BitrixHelper::getCompany($companyId, $adminAuthToken);
            $contact = BitrixHelper::getContact($contactId, $adminAuthToken);
            require_once($_SERVER["DOCUMENT_ROOT"]."/forms/header.php");


            ?>
            <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <h1>Школы и лагеря</h1>

                    <div class="row">
                        <div class="col-sm-2"><b>Компания:</b></div>
                        <div class="col-sm-10"><?= $company["TITLE"]?> (<a href="https://next.bitrix24.kz/crm/company/show/<?= $company["ID"]?>/">ID<?= $company["ID"]?></a>)</div>
                    </div>

                    <div class="row">
                        <div class="col-sm-2"><b>Контакт:</b></div>
                        <div class="col-sm-10"><?= $contact["NAME"]." ".$contact["LAST_NAME"]?> (<a href="https://next.bitrix24.kz/crm/contact/show/<?= $contact["ID"]?>/">ID<?= $contact["ID"]?></a>)</div>
                    </div>

                    <form id="form" class="form-horizontal" method="post" action="school.php">
                        <input type="hidden" name="action_performed" value="pack_created">
                        <input type="hidden" name="action" value="<?= $action ?>">
                        <input type="hidden" name="auth_id" value="<?= $auth_id ?>">
                        <input type="hidden" name="admin_token" value="<?= $adminAuthToken ?>">

                        <input type="hidden" name="contact_id" value="<?= $contactId ?>">
                        <input type="hidden" name="company_id" value="<?= $companyId?>">

                        <input type="hidden" name="contact_name" value="<?= $contact["NAME"]." ".$contact["LAST_NAME"] ?>">
                        <input type="hidden" name="contact_phone" value="<?= $contact["PHONE"][0]["VALUE"]?>">
                        <input type="hidden" name="company_name" value="<?= $company["TITLE"]?>">

                        <h3>Информация о пакетах</h3>

                        <div class="form-group">
                            <label class="control-label col-sm-2" for="pack">Выберите пакет:</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <select class="form-control" id="pack" name="pack" required>
                                        <option value="">Выберите из списка</option>
                                        <option value="basepack">Базовый</option>
                                        <option value="standartpack">Стандартный</option>
                                        <option value="allinclusive">Все включено</option>
                                    </select>
                                    <span class="input-group-addon"><i class="glyphicon glyphicons-gift"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-sm-2" for="pupil_count">Количество голов учеников:</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <input type="number" step="1" min="0" class="form-control" id="pupil_count" name="pupil_count" required placeholder="Введите целое число">
                                    <span class="input-group-addon"><i class="glyphicon glyphicons-fire"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-sm-2" for="teacher_count">Количество учителей:</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <input type="number" step="1" min="0" class="form-control" id="teacher_count" name="teacher_count" required placeholder="Введите целое число">
                                    <span class="input-group-addon"><i class="glyphicon glyphicons-parents"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-sm-2" for="subject">Тема урока:</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="subject" name="subject" required placeholder="Введите тему урока">
                                    <span class="input-group-addon"><i class="glyphicon glyphicons-book"></i></span>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h3>Дата, время, продолжительность, местоположение аренды</h3>

                        <div class="form-group">
                            <label class="control-label col-sm-2" for="center">Центр:</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <select class="form-control" id="center" name="center" required>
                                        <option value="">Выберите из списка</option>
                                        <option value="next_ese">NEXT Esentai</option>
                                        <option value="next_apo">NEXT Aport</option>
                                        <option value="next_pro">NEXT Promenade</option>
                                    </select>
                                    <span class="input-group-addon"><i class="glyphicon glyphicons-building"></i></span>
                                </div>
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="control-label col-sm-2" for="date">Дата:</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <input type="date" class="form-control" id="date" name="date" required placeholder="Выберите дату">
                                    <span class="input-group-addon"><i class="glyphicon glyphicons-calendar"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-sm-2" for="time">Время:</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <input type="time" class="form-control" id="time" name="time" required
                                           placeholder="Выберите время">
                                    <span class="input-group-addon"><i class="glyphicon glyphicons-clock"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-sm-2" for="duration">Время аренды (часов):</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <input type="number" step="1" min="0" class="form-control" id="duration" name="duration" required placeholder="Введите кол-во часов">
                                    <span class="input-group-addon"><i class="glyphicon glyphicons-clock"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-2">
                                <button type="submit" id="submit-btn" class="btn btn-primary">Далее</button>
                            </div>
                        </div>
                    </form>
                    <div id="alert"></div>
                </div>
            </div>
            <script>
                $('#form').submit(function(){
                    //$(this).find('input[type=submit]').prop('disabled', true);

                    $("#submit-btn").prop('disabled',true);
                    $('#alert').addClass("alert alert-warning").append("Идет обрабокта информации");
                });

            </script>

            <?php


            break;

        case "pack_created":
            $company = BitrixHelper::getCompany($companyId, $adminAuthToken);
            $contact = BitrixHelper::getContact($contactId, $adminAuthToken);
            require_once($_SERVER["DOCUMENT_ROOT"]."/forms/header.php");
            ?>
            <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <h1>Школы и лагеря</h1>

                    <div class="row">
                        <div class="col-sm-2">Компания:</div>
                        <div class="col-sm-10"><?= $company["TITLE"]?> (<a href="https://next.bitrix24.kz/crm/company/show/<?= $company["ID"]?>/">ID<?= $company["ID"]?></a>)</div>
                    </div>

                    <div class="row">
                        <div class="col-sm-2">Контакт:</div>
                        <div class="col-sm-10"><?= $contact["NAME"]." ".$contact["LAST_NAME"]?> (<a href="https://next.bitrix24.kz/crm/contact/show/<?= $contact["ID"]?>/">ID<?= $contact["ID"]?></a>)</div>
                    </div>

                    <form id="form" class="form-horizontal" method="post" action="school.php">
                        <input type="hidden" name="action_performed" value="order_created">
                        <input type="hidden" name="action" value="<?= $action ?>">
                        <input type="hidden" name="auth_id" value="<?= $auth_id ?>">
                        <input type="hidden" name="admin_token" value="<?= $adminAuthToken ?>">

                        <input type="hidden" name="contact_id" value="<?= $contactId ?>">
                        <input type="hidden" name="company_id" value="<?= $companyId?>">

                        <input type="hidden" name="contact_name" value="<?= $contact["NAME"]." ".$contact["LAST_NAME"] ?>">
                        <input type="hidden" name="contact_phone" value="<?= $contact["PHONE"][0]["VALUE"]?>">
                        <input type="hidden" name="company_name" value="<?= $company["TITLE"]?>">

                        <input type="hidden" name="pack" value="<?= $_REQUEST["pack"]?>">
                        <input type="hidden" name="pupil_count" value="<?= $_REQUEST["pupil_count"]?>">
                        <input type="hidden" name="teacher_count" value="<?= $_REQUEST["teacher_count"]?>">
                        <input type="hidden" name="center" value="<?= $_REQUEST["center"]?>">
                        <input type="hidden" name="date" value="<?= $_REQUEST["date"]?>">
                        <input type="hidden" name="time" value="<?= $_REQUEST["time"]?>">
                        <input type="hidden" name="duration" value="<?= $_REQUEST["duration"]?>">
                        <input type="hidden" name="subject" value="<?= $_REQUEST["subject"]?>">

                        <?php
                        if ($_REQUEST["pack"] == "standartpack" || $_REQUEST["pack"] == "allinclusive"){
                        ?>
                        <hr>
                        <h3>Фуд-пакеты</h3>
                        <div class="form-group">
                            <label class="control-label col-sm-2" for="foodpack_count">Количество фуд-пакетов:</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <input type="number" step="1" min="0" class="form-control" id="foodpack_count" name="foodpack_count" required placeholder="Введите целое число">
                                    <span class="input-group-addon"><i class="glyphicon glyphicons-fast-food"></i></span>
                                </div>
                            </div>
                        </div>
                        <?php
                        } else {
                        ?>
                        <input type="hidden" name="foodpack_count" value="0">
                        <?php
                        }
                        ?>

                        <?php
                        if ($_REQUEST["pack"] == "allinclusive"){
                        ?>
                        <hr>
                        <h3>Трансфер</h3>
                        <div class="form-group">
                            <label class="control-label col-sm-2" for="transfer_cost">Полная стоимость трансфера:</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <input type="number" step="0.1" min="0" class="form-control" id="transfer_cost" name="transfer_cost" required placeholder="Введите стоимость">
                                    <span class="input-group-addon"><i class="glyphicon glyphicons-bus"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-sm-2" for="driver_cost">Деньги водителю:</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <input type="number" step="0.1" min="0" class="form-control" id="driver_cost" name="driver_cost" required placeholder="Введите стоимость">
                                    <span class="input-group-addon"><i class="glyphicon glyphicons-businessman"></i></span>
                                </div>
                            </div>
                        </div>
                        <?php
                        } else {
                        ?>
                            <input type="hidden" name="transfer_cost" value="0">
                            <input type="hidden" name="driver_cost" value="0">
                        <?php
                        }
                        ?>


                        <hr>
                        <h3>Финансовая информация</h3>
                        <div class="form-group">
                            <label class="control-label col-sm-2" for="bribe_percent">Процент X:</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <input type="number" step="1" min="0" max="100" class="form-control" id="bribe_percent" name="bribe_percent" required placeholder="Введите процент от 1 до 100">
                                    <span class="input-group-addon"><i class="glyphicon glyphicons-money"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-sm-2" for="discount">Скидка:</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <input type="number" step="1" min="0" class="form-control" id="discount" name="discount" value="0">
                                    <span class="input-group-addon"><i class="glyphicon glyphicons-heart-empty"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-sm-2" for="discount_comment">Комментарий к скидке:</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="discount_comment" name="discount_comment" placeholder="Введите комментарий к скидке" maxlength="150">
                                    <span class="input-group-addon"><i class="glyphicon glyphicons-heart-empty"></i></span>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h3>Общая информация</h3>
                        <div class="form-group">
                            <label class="control-label col-sm-2" for="comment">Комментарий к заказу:</label>
                            <div class="col-sm-10">
                                <textarea class="form-control" id="comment" name="comment" ></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-2">
                                <button type="submit" id="submit-btn" class="btn btn-primary">Рассчитать стоимость</button>
                            </div>
                        </div>
                    </form>
                    <div id="alert"></div>
                </div>
            </div>
            <script>
                $('#form').submit(function(){
                    //$(this).find('input[type=submit]').prop('disabled', true);

                    $("#submit-btn").prop('disabled',true);
                    $('#alert').addClass("alert alert-warning").append("Идет обрабокта информации");
                });

            </script>



            <?php
            break;

        case "order_created":
        case "order_confirmed":
            $packName = "";
            switch ($_REQUEST["pack"]) {
                case 'basepack':
                    $packName = "Базовый";
                    break;
                case 'standartpack':
                    $packName = "Стандартный";
                    break;
                default:
                    $packName = "Все включено";
                    break;
            }
            switch ($_REQUEST["center"]) {
                case 'next_ese':
                    $centerName = "NEXT Esentai";
                    break;
                case 'next_apo':
                    $centerName = "NEXT Aport";
                    break;

                case 'next_pro':
                    $centerName = "NEXT Promenade";
                    break;
                default:
                    $centerName = "Не известен";
                    break;
            }

            $url = "http://b24.next.kz/forms/calculation.php";
            $parameters = array(
                "action" => $actionPerformed == "order_created" ? "schoolGetCost" : "schoolGetCostSave",
                "pack" => $_REQUEST["pack"],
                "contact_id" => $_REQUEST["contact_id"],
                "company_id" => $_REQUEST["company_id"],
                "company_name" => $_REQUEST["company_name"],
                "center" => $_REQUEST["center"],

                "date" => $_REQUEST["date"],
                "time" => $_REQUEST["time"],
                "duration" => $_REQUEST["duration"],

                "pupil_count" => $_REQUEST["pupil_count"],
                "teacher_count" => $_REQUEST["teacher_count"],
                "foodpack_count" => $_REQUEST["foodpack_count"],
                "transfer_cost" => $_REQUEST["transfer_cost"],
                "driver_cost" => $_REQUEST["driver_cost"],
                "discount" => $_REQUEST["discount"],

                "discount_comment" => $_REQUEST["discount_comment"],
                "bribe_percent" => $_REQUEST["bribe_percent"],

                "contact_name" => $_REQUEST["contact_name"],
                "contact_phone" => $_REQUEST["contact_phone"],

                "comment" => $_REQUEST["comment"],
                "subject" => $_REQUEST["subject"],

                "user_id" => $_SESSION["user_id"],
                "user_fullname" => $curr_user["LAST_NAME"]." ".$curr_user["NAME"],
            );

            $response = query("POST", $url, $parameters);
            $costs = $response["result"];
            $order = isset($response["order"]) ? $response["order"] : null;

            $contact = BitrixHelper::getContact($_REQUEST["contact_id"], $adminAuthToken);
            $company = BitrixHelper::getCompany($_REQUEST["company_id"], $adminAuthToken);

            $contactName = $contact["NAME"]." ".$contact["LAST_NAME"];
            $companyTitle = $company["TITLE"];

            require_once($_SERVER["DOCUMENT_ROOT"]."/forms/header.php");
            ?>
            <div class="container">
                <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <h1>Школы и лагеря</h1>
                    <h3>Подтверждение заказа</h3>

                    <?php
                    if ($actionPerformed == "order_confirmed"){
                    ?>
                        <div>
                            <a href="#" id="print" type="button" class="btn btn-default">Печать</a>
                            <a href="../forms/index.php?auth_id=<?= $_REQUEST["auth_id"]?>" id="print" type="button" class="btn btn-primary">В главное меню</a>
                        </div>

                    <?php } ?>

                    <div id="tableToPrint" class="table-responsive">
                        <table class="table table-striped">

                            <tr><th>Информация о сделке</th><td></td></tr>
                            <?php
                            if ($actionPerformed == "order_confirmed"){
                                echo "<tr><td>ID заказа аренды (Консолидация 9-1)</td><td><b>".$order["Id"]."<b></td></tr>";
                                echo "<tr><td>Номер сделки</td><td><a href='https://next.bitrix24.kz/crm/deal/show/".$order["DealId"]."/'>".$order["DealId"]."</a><b></td></tr>";
                                echo "<tr><td>Компания</td><td><a href='https://next.bitrix24.kz/crm/company/show/".$_REQUEST["company_id"]."/' target='_blank'>".$contactName."</a></td></tr>";
                                echo "<tr><td>Контакт</td><td><a href='https://next.bitrix24.kz/crm/contact/show/".$_REQUEST["contact_id"]."/' target='_blank'>".$companyTitle."</a></td></tr>";
                            }
                            ?>
                            <tr><td>Тема урока</td><td><?= $_REQUEST["subject"]?></td></tr>
                            <tr><td>Пакет</td><td><?= $packName?></td></tr>
                            <tr><td>Кол-во учеников</td><td><?= $_REQUEST["pupil_count"]?></td></tr>
                            <tr><td>Кол-во учителей</td><td><?= $_REQUEST["teacher_count"]?></td></tr>
                            <tr><td>Дата</td><td><?= $_REQUEST["date"]?></td></tr>
                            <tr><td>Время</td><td><?= $_REQUEST["time"]?></td></tr>
                            <tr><td>Продолжительность</td><td><?= $_REQUEST["duration"]?></td></tr>
                            <tr><td>Комментарий к заказу</td><td><?= $_REQUEST["comment"]?></td></tr>
                            <tr><td>Стоимость пакетов</td><td><?= $costs["packCost"]?></td></tr>

                    <?php
                    if ($_REQUEST["pack"] == "standartpack" || $_REQUEST["pack"] == "allinclusive"){
                        ?>
                            <tr><th>Фуд-пакеты</th><td></td></tr>
                            <tr><td>Кол-во фуд-пакетов</td><td><?= $_REQUEST["foodpack_count"]?></td></tr>
                            <tr><td>Стоимость фуд-пакетов</td><td><?= $costs["foodCost"]?></td></tr>

                    <?php } ?>

                    <?php
                    if ($_REQUEST["pack"] == "allinclusive"){
                        ?>
                            <tr><th>Трансфер</th><td></td></tr>
                            <tr><td>Стоимость трансфера</td><td><?= $costs["transferCost"]?></td></tr>
                            <tr><td>Деньги водителю</td><td><?= $costs["driverCost"]?></td></tr>
                            <tr><td>Трансфер в кассу</td><td><?= $costs["transferToCash"]?></td></tr>
                    <?php } ?>
                            <tr><th>Информация о скидке</th><td></td></tr>
                            <tr><td>Скидка</td><td><?= $_REQUEST["discount"]?></td></tr>
                            <tr><td>Комментарий к скидке</td><td><?= $_REQUEST["discount_comment"]?></td></tr>

                            <tr><th>Финансовая информация</th><td></td></tr>

                            <tr><td>Процент Х</td><td><?= $costs["bribe"]?></td></tr>
                            <tr><td>Полная стоимость заказа</td><td><?= $costs["totalCost"]?></td></tr>
                            <tr><td>Полная стоимость заказа (с учетом скидки)</td><td><?= $costs["totalCostDiscount"]?></td></tr>
                            <tr><td>Стоимость заказа (в кассу)</td><td><?= $costs["moneyToCash"]?></td></tr>
                        </table>
                    </div>

                    <?php
                    if ($actionPerformed == "order_created"){
                        ?>
                        <form id="form" method="post" action="school.php">
                            <input type="hidden" name="action_performed" value="order_confirmed">
                            <input type="hidden" name="action" value="<?= $action ?>">
                            <input type="hidden" name="auth_id" value="<?= $auth_id ?>">
                            <input type="hidden" name="admin_token" value="<?= $adminAuthToken ?>">

                            <input type="hidden" name="contact_id" value="<?= $_REQUEST["contact_id"] ?>">
                            <input type="hidden" name="company_id" value="<?= $_REQUEST["company_id"]?>">

                            <input type="hidden" name="contact_name" value="<?= $_REQUEST["contact_name"] ?>">
                            <input type="hidden" name="contact_phone" value="<?= $_REQUEST["contact_phone"]?>">
                            <input type="hidden" name="company_name" value="<?= $_REQUEST["company_name"]?>">

                            <input type="hidden" name="pack" value="<?= $_REQUEST["pack"]?>">
                            <input type="hidden" name="pupil_count" value="<?= $_REQUEST["pupil_count"]?>">
                            <input type="hidden" name="teacher_count" value="<?= $_REQUEST["teacher_count"]?>">
                            <input type="hidden" name="center" value="<?= $_REQUEST["center"]?>">
                            <input type="hidden" name="date" value="<?= $_REQUEST["date"]?>">
                            <input type="hidden" name="time" value="<?= $_REQUEST["time"]?>">
                            <input type="hidden" name="duration" value="<?= $_REQUEST["duration"]?>">

                            <input type="hidden" name="foodpack_count" value="<?= $_REQUEST["foodpack_count"]?>">
                            <input type="hidden" name="transfer_cost" value="<?= $_REQUEST["transfer_cost"]?>">
                            <input type="hidden" name="driver_cost" value="<?= $_REQUEST["driver_cost"]?>">


                            <input type="hidden" name="bribe_percent" value="<?= $_REQUEST["bribe_percent"]?>">
                            <input type="hidden" name="discount" value="<?= $_REQUEST["discount"]?>">
                            <input type="hidden" name="discount_comment" value="<?= $_REQUEST["discount_comment"]?>">
                            <input type="hidden" name="comment" value="<?= $_REQUEST["comment"]?>">
                            <input type="hidden" name="subject" value="<?= $_REQUEST["subject"]?>">
                            <input type="hidden" name="user_id" value="<?= $userId?>">

                            <div class="form-group">
                                <div class="col-sm-offset-2">
                                    <button type="submit" id="submit-btn" class="btn btn-primary">Создать заказ</button>
                                </div>
                            </div>
                        </form>
                        <div id="alert"></div>
                        <?php
                    }
                    ?>
            </div>
            <script>
                $('#form').submit(function(){
                    //$(this).find('input[type=submit]').prop('disabled', true);

                    $("#submit-btn").prop('disabled',true);
                    $('#alert').addClass("alert alert-warning").append("Идет обработка информации");
                });

                $('#print').click(function(){
                    printContent("tableToPrint");
                });

            </script>

            <?php

            break;

    }
    require_once($_SERVER["DOCUMENT_ROOT"]."/forms/footer.php");

    ?>