<?php
require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");

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


$actionPerformed = isset($_REQUEST["actionPerformed"]) ? $_REQUEST["actionPerformed"] : "initiated";
switch ($actionPerformed){
    case "initiated":
        $value = isset($_REQUEST["orderId"]) ? $_REQUEST["orderId"] : "";
        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
        ?>

        <div class="container">
            <h1>Редактирование структуры заказа</h1>
            <div class="">
                <form id="form" class="form-horizontal" method="post" action="">
                    <input type="hidden" name="actionPerformed" value="orderSave">
                    <input type="hidden" name="action" value="<?= $_REQUEST["action"] ?>">
                    <input type="hidden" name="authId" value="<?= $_REQUEST["authId"] ?>">
                    <input type="hidden" name="adminToken" value="<?= $_REQUEST["adminToken"] ?>">

                    <div class="row">
                        <div class="col-sm-4">
                            <fieldset>
                                <legend>Поиск заказа</legend>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="orderId" name="orderId" required placeholder="Номер (9-1)" value="<?= $value ?>">
                                            <div class="input-group-btn">
                                                <button id='searchOrder' class='btn btn-primary' type='button'>
                                                    Найти
                                                </button>
                                            </div>
                                        </div>

                                    </div>

                                </div>

                            </fieldset>

                            <div class="form-group">
                                <div class="col-sm-12">
                                    <div class="radio">
                                        <label><input type="radio" name="saveType" checked>Пересохранить заказ</label>
                                    </div>
                                    <div class="radio">
                                        <label><input type="radio" name="saveType">Рекалькуляция</label>
                                    </div>
                                </div>

                            </div>

                            <div class="form-group">
                                <div class="col-sm-12">
                                    <div class="checkbox">
                                        <label><input type="checkbox" id="confirm" name="confirm">Подтвердить изменения</label>
                                    </div>
                                </div>


                            </div>

                            <hr>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button type="submit" id="submit-btn"  class="btn btn-primary" disabled >Сохранить структуру заказа</button>
                                </div>
                            </div>


                        </div>


                        <div class="col-sm-8">
                            <fieldset>
                                <legend>Структура заказа</legend>
                                <div class="form-group">
                                    <textarea class="form-control" id="orderStructure" name="orderStructure" disabled></textarea>
                                </div>
                            </fieldset>
                        </div>

                    </div>
                </form>
            </div>
        </div>
        <script>

            var submitBtn = $("#submit-btn");
            var searchOrderBtn = $("#searchOrder");
            var orderStructure = $("#orderStructure");
            var confirm = $('#confirm');

            searchOrderBtn.on("click", function(){
                var orderId = $('#orderId').val();
                if (orderId == "") return;

                orderStructure.prop("disabled", true);

                var url = "https://b24.next.kz/rest/bitrix.php";
                var data = {
                    "action" : "order.get.google",
                    "id" : orderId
                };
                var request = $.ajax({
                    type: "POST",
                    url: url,
                    data: data
                });

                request.done(function(res){
                    var order = res["result"];
                    console.log(res);
                    orderStructure.val(JSON.stringify(order, undefined, 4));
                    orderStructure.prop("disabled", false);
                    resizeTextArea(orderStructure);
                });
            });

            confirm.change(function(){
                if (orderStructure.prop("disabled") == true) {
                    this.checked = false;
                    return;
                }
                var disableState = !this.checked;
                submitBtn.prop('disabled', disableState);
            });


            $('#form').submit(function(){
                //$(this).find('input[type=submit]').prop('disabled', true);

                submitBtn.prop('disabled',true);
                $("a").addClass('disabled');
                $('#alert').addClass("alert alert-warning").append("Идет обработка информации");
            });

            function resizeTextArea(element) {

                var windowHeight = $(window).height();
                var contentHeight = element[0].scrollHeight;

                element.height(windowHeight - 300);
            }

        </script>


        <?php

        break;

    case "orderSave":
        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
        $order = json_decode($_REQUEST["orderStructure"]);
        ?>
        <pre><?= var_export($_REQUEST, true)?></pre>
        <pre><?= var_export($order, true)?></pre>

        <?php
        break;

    default:
        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
        ?>
        default switch of editOrder.php
        <?php
        break;
}

//<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/footer.php");