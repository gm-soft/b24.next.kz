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
    "ASSIGNED_BY_ID",
    "STAGE_ID"
];
$filterValues = [
    $userId,
    //"24",
    "2"
];

$openDeals = BitrixHelper::getDeals($filterFields, $filterValues, $_REQUEST["adminToken"]);
$actionPerformed = isset($_REQUEST["actionPerformed"]) ? $_REQUEST["actionPerformed"] : "initiated";
switch ($actionPerformed){
    case "initiated":

        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
        ?>
        <div class="container">
            <h1>Закрытие аренды и подсчет доп.заказа</h1>

            <div>
                <form id="form" class="form-horizontal" method="post" action="closeOrder.php">

                    <input type="hidden" name="actionPerformed" value="dealSelected">
                    <input type="hidden" name="action" value="<?= $_REQUEST["authId"] ?>">
                    <input type="hidden" name="authId" value="<?= $_REQUEST["authId"] ?>">
                    <input type="hidden" name="adminToken" value="<?= $_REQUEST["adminToken"] ?>">

                    <div class="form-group">
                        <label class="control-label col-sm-3" for="dealSelect">Выберите заказ аренды</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <select class="form-control" id="dealSelect" name="dealSelect" required>
                                        <?php
                                        foreach ($openDeals as $deal){
                                            echo "<option value='".$deal["ID"]."'>".$deal["TITLE"]."</option>\n";
                                        }
                                        ?>

                                    </select>
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-building"></span></span>
                                </div>
                            </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-3">
                            <a href="https://b24.next.kz/sales/index.php?authId=<?= $_REQUEST["authId"] ?>" id="back" class="btn btn-default">Отменить</a>
                            <button type="submit" id="submit-btn" class="btn btn-primary">Закрыть заказ</button>
                        </div>
                    </div>

                </form>
            </div>

            <div id="alert"></div>
        </div>


        <?php

        break;

    case "dealSelected":

        $deal = BitrixHelper::getDeal($_REQUEST["dealSelect"], $_REQUEST["adminToken"]);
        $title = $deal["TITLE"];
        $orderId = substr($title, 0, strpos($title, " "));
        $orderId = substr($orderId, 2);

        $params = [
            "event" => "CloseOrder",
            "orderId" => $orderId
        ];
        $closeResponse = queryGoogleScript($params);
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
                    <dl class="dl-horizontal">
                        <dt>Стоиомсть заказа</dt> <dd><?= $totalCost ?></dd>
                        <dt>Оплачено</dt> <dd><?= $payed ?></dd>
                        <dt>Остаток по оплате</dt> <dd><?= $remainder ?></dd>
                        <dt>Доп.заказов, кол-во</dt> <dd><?= $barItemsCount ?></dd>
                    </dl>
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
                    <a href="#" id="print" class="btn btn-primary">Печать</a>
                    <a href="https://b24.next.kz/sales/index.php?authId=<?= $_REQUEST["authId"] ?>" id="back" class="btn btn-default">В главное меню</a>
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
