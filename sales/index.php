<?php

	if (!isset($_SESSION)) session_start();
	require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");


	$authId = isset($_REQUEST["AUTH_ID"]) ? $_REQUEST["AUTH_ID"] : null;
	$authId = isset($_REQUEST["authId"]) ? $_REQUEST["authId"] : $authId;

	if (is_null($authId) || $authId == ""){
	    $_GET["error"] = "Нет авторизации через CRM Битрикс24!";
        $displayCondition = false;
	} else {
        $curr_user = BitrixHelper::getCurrentUser($authId);
        $_SESSION["user_name"] =  $curr_user["EMAIL"];
        $_SESSION["user_id"] =  $curr_user["ID"];
        $userId = $_SESSION["user_id"];

        $displayCondition =
            $userId == "72" ||
            $userId == "1" ||
            $userId == "10" ||
            $userId == "100" ||
            $userId == "88" ||
            $userId == "98";
    }



	require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
?>


	<div class="container">
        <div id="action-cards">
            <div class="row">

                <div class="col-sm-6">
                    <div class="action-card panel panel-default">
                        <div class="panel-heading">Продажи</div>
                        <div class="panel-body">
                            <div class="btn-group">
                                <a class="btn btn-default" href="../sales/preorder.php?authId=<?= $authId?>" role="button">Предзаказник аренды</a>
                                <a class="btn btn-default" href="../sales/booth.php?authId=<?= $authId?>" role="button">Продажа буса</a>

                            </div>

                            <div class="btn-group">
                                <a class="btn btn-default" href="../sales/orders/createGoogleForm.php?authId=<?= $authId?>" role="button">Создание заказа</a>
                                <a class="btn btn-default" href="../sales/orders/changeGoogleForm.php?authId=<?= $authId?>" role="button">Изменение заказа</a>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6">

                    <div class="action-card panel panel-default">
                        <div class="panel-heading">Заказы аренды</div>
                        <div class="panel-body">
                            <div class="btn-group">
                                <a class="btn btn-default <?= $displayCondition == true ? "" : "disabled" ?>"
                                   href="/sales/orders/create.php?authId=<?= $authId?>" role="button">Создать заказ</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6">

                    <div class="action-card panel panel-default">
                        <div class="panel-heading">Операции над заказами</div>
                        <div class="panel-body">
                            <div class="btn-group">
                                <!--a class="btn btn-default disabled" href="/sales/post/closeOrder.php?authId=<?= $authId?>" role="button">Закрыть аренду</a>
                                <a class="btn btn-default disabled" href="/sales/post/paymentOrder.php?authId=<?= $authId?>" role="button">Внести оплату</a-->
                                <a class="btn btn-default" href="/sales/post/cancelOrder.php?authId=<?= $authId?>" role="button">Отменить заказ</a>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-sm-6">
                    <div class="action-card panel panel-default">
                        <div class="panel-heading">Корпоративные продажи</div>
                        <div class="panel-body">
                            <div class="btn-group">
                                <a class="btn btn-default <?= $displayCondition == true ? "" : "disabled" ?>"
                                   href="/sales/school/school.php?&authId=<?= $authId?>" role="button">Школы</a>

                                <a class="btn btn-default <?= $displayCondition == true ? "" : "disabled" ?>"
                                   href="/sales/school/school.php?actionPerformed=edit&authId=<?= $authId?>" role="button">Школы (редактирование)</a>

                                <a class="btn btn-default disabled" href="/sales/company2.php?action=corporate&authId=<?= $authId?>" role="button" >Корп.продажа</a>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                if ($userId == "72"){
                    ?>
                    <div class="col-sm-6">
                        <div class="action-card panel panel-warning">
                            <div class="panel-heading">Админские функции</div>
                            <div class="panel-body">
                                <div class="btn-group">
                                    <a class="btn btn-default"
                                       href="/sales/orders/editOrder.php?&authId=<?= $authId?>" role="button">Структура заказа</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                }
                ?>


            </div>
        </div>




	</div>
	

	

	<?php require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/footer.php"); ?>