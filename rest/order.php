<?php
require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");

header('Content-Type: application/json');

$response = array(
    "result" => false,
    "message" => ""
);
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : NULL;

if (is_null($action)) {
    $response["message"] = "Действие не было передано";
    echo json_encode($response);
    die();
}
$logText = "action=".$action.". Request: ".json_encode($_REQUEST);
$processed = false;
// -------------------------------------
$adminToken = get_access_data(true);
$orderId = $_REQUEST["orderId"];
$order = OrderHelper::GetOrder($orderId);

if (is_null($order)) {

    $response["message"] = "Заказ под номером ".$orderId." не найден";
    echo json_encode($response);
    die();
}

switch ($action) {
    case "payment.add":

        $payment = $_REQUEST["payment"];
        $response["result"] = OrderHelper::PaymentAdd($order, $payment, $adminToken);
        $processed = true;
        break;

    case "order.rent.close":
        $response["result"] = OrderHelper::CloseOrderRent($order, $adminToken);
        $processed = true;
        break;

    case "order.cancel":
        $response["result"] = OrderHelper::CancelOrder($order, $adminToken);
        $processed = true;
        break;

    case "":

        break;
}

$logText .= $processed == true ? ". Result=true" : ". Result=false";
log_event($logText, "/log/order.php.log");
echo json_encode($response);


//-----------------------------
//-----------------------------
//-----------------------------
