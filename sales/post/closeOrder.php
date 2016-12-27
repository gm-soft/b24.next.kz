<?php
require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/help.php");
require($_SERVER["DOCUMENT_ROOT"]."/Helpers/BitrixHelperClass.php");

$_REQUEST["action"] = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
$_REQUEST["authId"] = isset($_REQUEST["authId"]) ? $_REQUEST["authId"] : null;

if (is_null($_REQUEST["authId"])) {
    redirect("../sales/index.php");
}

$_REQUEST["adminToken"] = isset($_REQUEST["adminToken"]) ? $_REQUEST["adminToken"] : get_access_data(true);



$curr_user = BitrixHelper::getCurrentUser($_REQUEST["authId"]);
$_SESSION["user_name"] =  $curr_user["EMAIL"];
$_SESSION["user_id"] =  $curr_user["ID"];
$userId = $curr_user["ID"];

$filterFields = [
    "STAGE_ID"
];
$filterValues = [
    $userId,
    //"24",
    "2"
];

//$openDeals = BitrixHelper::getDeals(array("STAGE_ID"), array("2"), $_REQUEST["adminToken"]);
//$closedOrders = BitrixHelper::getDeals(array("STAGE_ID"), array("7"), $_REQUEST["adminToken"]);

$actionPerformed = isset($_REQUEST["actionPerformed"]) ? $_REQUEST["actionPerformed"] : "initiated";
switch ($actionPerformed){
    case "initiated":

        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
        ?>
        <div class="container">
            <h1>Закрытие аренды и подсчет доп.заказа</h1>
            <div>
                Выберите из списка нужный номер заказа и осуществите подсчет доп.заказа, чтобы узнать, какие заказы были совершены на баре и сколько необходимо взять денег с клиента.
            </div>

            <div>
                <form id="form" class="form-horizontal" method="post" action="closeOrder.php">

                    <input type="hidden" name="actionPerformed" value="dealSelected">
                    <input type="hidden" name="action" value="<?= $_REQUEST["authId"] ?>">
                    <input type="hidden" name="authId" value="<?= $_REQUEST["authId"] ?>">
                    <input type="hidden" name="adminToken" value="<?= $_REQUEST["adminToken"] ?>">

                    <div class="form-group">
                        <label class="control-label col-sm-3" for="filterSelect">Выберите центр</label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <select class="form-control" id="filterSelect" name="filterSelect">
                                    <option value="">Центр</option>
                                    <option value="128">NEXT Aport</option>
                                    <option value="130">NEXT Esentai</option>
                                    <option value="132">NEXT Promenade</option>

                                </select>
                                <span class="input-group-addon"><span class="glyphicon glyphicon-building"></span></span>
                            </div>
                        </div>
                    </div>


                    <div class="form-group">
                        <label class="control-label col-sm-3" for="dealSelect">Выберите заказ аренды</label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <select class="form-control" id="dealSelect" name="dealSelect" required>
                                    <option value="">Нет данных</option>
                                </select>
                                <span class="input-group-addon"><span class="glyphicon glyphicon-building"></span></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">

                        <div class="col-sm-9 col-sm-offset-3">
                            <div class="checkbox">
                                <label><input type="checkbox" id="confirmAction" name="confirmAction" required> Подтвердить закрытие</label>
                            </div>
                        </div>

                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-3">
                            <a href="https://b24.next.kz/sales/index.php?authId=<?= $_REQUEST["authId"] ?>" id="back" class="btn btn-default">Отменить</a>
                            <button type="submit" id="submit-btn" class="btn btn-primary">Закрыть аренду</button>
                        </div>
                    </div>



                </form>
            </div>

            <div id="alert"></div>
        </div>

        <script>
            $('#dealSelect').change(function(){
                $('#confirmAction').prop('checked', false);
            });

            $('#filterSelect').change(function(){

                var filterSelect = $('#filterSelect');
                var dealSelect = $('#dealSelect');
                var filterValue = filterSelect.val();

                filterSelect.prop("disabled", "disabled");
                dealSelect.prop("disabled", "disabled");

                $.ajax({
                    type: 'POST',
                    url: 'https://b24.next.kz/rest/bitrix.php',

                    // UF_CRM_1468830187
                    data: {
                        'action': 'center.deals.get',
                        'center' : filterValue,
                        //"period" : 14
                    },
                    success: function(res){

                        //var deals = res["result"];
                        var total = res["total"];
                        var openOrders = res["openOrders"];
                        var closedOrders = res["closedOrders"];

                        dealSelect.find('optgroup').remove().end();
                        dealSelect.find('option').remove().end();
                        dealSelect.append('<option value="">Выберите заказ</option>').val('');
                        //----------------------------------
                        var optGroup;
                        var deal;
                        var option;
                        //----------------
                        optGroup = $('<optgroup></optgroup>').attr("label", "Заказ подтвержден");
                        for (var i = 0; i < openOrders.length; i++){

                            deal = openOrders[i];
                            option = $("<option></option>").attr("value", deal["ID"]).text(deal["TITLE"]);
                            optGroup.append(option);
                        }
                        dealSelect.append(optGroup);
                        //---------------------------------
                        optGroup = $('<optgroup></optgroup>').attr("label", "Аренда проведена");
                        for (var i = 0; i < closedOrders.length; i++){

                            deal = closedOrders[i];
                            option = $("<option></option>").attr("value", deal["ID"]).text(deal["TITLE"]);
                            optGroup.append(option);
                        }
                        dealSelect.append(optGroup);
                        //---------------------------------

                        filterSelect.prop("disabled", false);
                        dealSelect.prop("disabled", false);
                    }
                });
            });

        </script>


        <?php

        break;

    case "dealSelected":

        $deal = BitrixHelper::getDeal($_REQUEST["dealSelect"], $_REQUEST["adminToken"]);
        $title = $deal["TITLE"];
        $orderId = substr($title, 0, strpos($title, " "));
        $orderId = substr($orderId, 2);
        /*
        $params = [
            "event" => "CloseOrder",
            "orderId" => $orderId,
            "userId" => $userId
        ];
        $closeResponse = queryGoogleScript($params);
        $closeResponse = $closeResponse["result"];
        */
        $params = [
            "action" => "order.rent.close",
            "orderId" => $orderId,
            "userId" => $userId
        ];
        $closeResponse = query("POST", "http://b24.next.kz/rest/order.php", $params);
        $closeResponse = $closeResponse["result"];

        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
        if ($closeResponse["saveResult"] == true){
            $remainder = $closeResponse["remainder"];
            $barItems = $closeResponse["barItems"];
            $payed = $closeResponse["payed"];
            $totalCost = $closeResponse["totalCost"];

            $barItemsCount = count($barItems);

            ?>
            <div class="container">
                <h1>Результат подсчета доп.заказа</h1>
                <div id="toPrint">
                    <h3>Заказ ID<?= $orderId?></h3>

                    <div class="text-center">
                        <p>Стоимость заказа: <?= $totalCost ?></p>
                        <p>Оплачено: <?= $payed ?></p>
                        <h3>Остаток по оплате: <?= $remainder ?></h3>
                        <p>Статус заказа: <i><?= $closeResponse["status"] ?></i></p>
                        <p>Доп.заказов, кол-во: <?= $barItemsCount ?></p>
                    </div>

                    <!--dl class="dl-horizontal">
                        <dt>Стоиомсть заказа</dt> <dd><?= $totalCost ?></dd>
                        <dt>Оплачено</dt> <dd><?= $payed ?></dd>
                        <dt>Остаток по оплате</dt> <dd><?= $remainder ?></dd>
                        <dt>Доп.заказов, кол-во</dt> <dd><?= $barItemsCount ?></dd>
                    </dl-->
                    <?php
                    if ($barItemsCount > 0){
                        ?>
                        <table class="table table-striped">
                            <tr>
                                <th>№</th>
                                <th>Название</th>
                                <th>Цена</th>
                                <th>Кол-во</th>
                                <th>Стоимость</th>
                            </tr>
                            <?php
                            $barCost = 0;
                            for ($i = 0; $i < $barItemsCount; $i++){

                                $name = $barItems[$i]["name"];
                                $count = $barItems[$i]["count"];
                                $price = $barItems[$i]["price"];
                                $cost = $barItems[$i]["cost"];
                                $barCost += $cost;
                                echo "\t\t"."<tr>";
                                echo "<td>$i</td><td>$name</td><td>$price</td><td>$count</td><td>$cost</td>";
                                echo "<tr>"."\n";
                            }
                            ?>
                            <tr>
                                <td></td>
                                <td>Итого</td>
                                <td></td>
                                <td></td>
                                <td><?= $barCost ?></td>
                            </tr>


                        </table>

                        <?php
                    }

                    ?>

                </div>

                <div class="text-center">
                    <a href="#" id="print" class="btn btn-default">Печать</a>
                    <a href="https://b24.next.kz/sales/index.php?authId=<?= $_REQUEST["authId"] ?>" id="back" class="btn btn-default">В главное меню</a>
                    <?php
                    if ($remainder > 0){
                        $url = "https://b24.next.kz/sales/index.php?".
                            "authId=".$_REQUEST["authId"]."&".
                            "orderId=".$orderId."&".
                            "dealId=".$_REQUEST["dealSelect"]."";

                    }
                    ?>
                </div>

            </div>




            <?php
        } else {
            ?>
            <div class="container">
                <h1>Результат подсчета доп.заказа</h1>
                <div>
                    Заказ не был закрыт по неизвестно ошибке<br>
                    <pre>
                        <?= var_export($closeResponse, true) ?>
                    </pre>
                </div>
            </div>

            <?php
        }

        break;
}
?>
<script>
    $("#dealSelect").select2();
    $('#form').submit(function(){
        $("#submit-btn").prop('disabled',true); //
        $("a").addClass('disabled');
    });

    $('#print').click(function(){
        printContent("toPrint");
    });
    </script>
<?php
    require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/footer.php");
?>
