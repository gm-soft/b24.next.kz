<?php

	if (!isset($_SESSION)) session_start();
	require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
	require($_SERVER["DOCUMENT_ROOT"]."/include/help.php");
	require($_SERVER["DOCUMENT_ROOT"]."/Helpers/BitrixHelperClass.php");




	$authId = isset($_REQUEST["AUTH_ID"]) ? $_REQUEST["AUTH_ID"] : null;
	$authId = isset($_REQUEST["authId"]) ? $_REQUEST["authId"] : $authId;

	if (is_null($authId) || $authId == ""){
	    $_GET["error"] = "Нет авторизации через CRM Битрикс24!";
	} else {
        $curr_user = BitrixHelper::getCurrentUser($authId);
        $_SESSION["user_name"] =  $curr_user["EMAIL"];
        $_SESSION["user_id"] =  $curr_user["ID"];
        $userId = $_SESSION["user_id"];
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
                                <a class="btn btn-default" href="/sales/contact.php?action=preorder&authId=<?= $authId?>" role="button">Предзаказник аренды</a>
                                <a class="btn btn-default" href="/sales/contact.php?action=booth&authId=<?= $authId?>" role="button">Продажа буса</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6">

                    <div class="action-card panel panel-default">
                        <div class="panel-heading">Операции над заказами</div>
                        <div class="panel-body">
                            <div class="btn-group">
                                <a class="btn btn-default" href="/sales/post/closeOrder.php?action=closeOrder&authId=<?= $authId?>" role="button">Закрыть аренду</a>
                                <a class="btn btn-default" href="/sales/post/paymentOrder.php?action=paymentOrder&authId=<?= $authId?>" role="button">Внести оплату</a>
                                <a class="btn btn-default" href="/sales/post/cancelOrder.php?action=cancelOrder&authId=<?= $authId?>" role="button">Отменить заказ</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">

                    <div class="action-card panel panel-default">
                        <div class="panel-heading">Корпоративные продажи</div>
                        <div class="panel-body">
                            <?php
                            $displayCondition =
                                $userId == "30" ||
                                $userId == "1" ||
                                $userId == "10" ||
                                $userId == "98";
                            ?>
                            <div class="btn-group">
                                <a class="btn btn-default <?= $displayCondition == true ? "" : "disabled" ?>"
                                   href="/sales/company.php?action=school&authId=<?= $authId?>" role="button">Школы</a>
                                <a class="btn btn-default <?= $displayCondition == true ? "" : "disabled" ?>"
                                   href="/sales/findOrder.php?action=school&authId=<?= $authId?>" role="button">Школы (редактирование)</a>
                                <a class="btn btn-default disabled" href="/sales/company.php?action=corporate&authId=<?= $authId?>" role="button" >Корп.продажа</a>

                                <a class="btn btn-default" href="/sales/search/company.php?action=corporate&authId=<?= $authId?>" role="button" >Выбор компании</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>




	</div>
	

	

	<?php require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/footer.php"); ?>