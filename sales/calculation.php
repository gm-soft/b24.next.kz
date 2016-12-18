<?php
    require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
    require($_SERVER["DOCUMENT_ROOT"]."/include/help.php");
    require($_SERVER["DOCUMENT_ROOT"]."/Helpers/BitrixHelperClass.php");
    require($_SERVER["DOCUMENT_ROOT"]."/Helpers/OrderHelperClass.php");


    $action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
    $response = array("result" => null);

    if (is_null($action)) {
        $response["error"] = "not_implemented";
        header('Content-Type: application/json');
        echo json_encode($response);
        die();
    }

    switch ($action) {
        case 'schoolGetCost':
        case 'schoolGetCostSave':
            include $_SERVER["DOCUMENT_ROOT"]."/sales/shared/prices.php";


            switch ($_REQUEST["pack"]) {
                case 'basePack':
                    $_REQUEST["packName"] = "Базовый";
                    break;
                case 'standardPack':
                    $_REQUEST["packName"] = "Стандартный";
                    break;
                case 'newYear':
                    $_REQUEST["packName"] = "Новогодний";
                    break;
                case 'allInclusive':
                    $_REQUEST["packName"] = "Все включено";
                    break;
                default:
                    $_REQUEST["packName"] = "Пакет не определен";
                    break;
            }

            switch ($_REQUEST["center"]) {
                case 'nextEse':
                    $_REQUEST["centerName"]  = "NEXT Esentai";
                    $_REQUEST["centerNameRu"] = "Есентай";
                    break;
                case 'nextApo':
                    $_REQUEST["centerName"]  = "NEXT Aport";
                    $_REQUEST["centerNameRu"] = "Апорт";
                    break;

                case 'nextPro':
                    $_REQUEST["centerName"] = "NEXT Promenade";
                    $_REQUEST["centerNameRu"] = "Променад";
                    break;
            }
            $_REQUEST["packagePrice"] = floatval($_REQUEST["packagePrice"]);
            $_REQUEST["foodPackPrice"] = floatval($_REQUEST["foodPackPrice"]);

            $_REQUEST["pupilCount"] = intval($_REQUEST["pupilCount"]);
            $_REQUEST["teacherCount"] = intval($_REQUEST["teacherCount"]);
            $_REQUEST["foodPackCount"] = intval($_REQUEST["foodPackCount"]);


            $_REQUEST["packCost"] = $_REQUEST["packagePrice"] * $_REQUEST["pupilCount"];
            $_REQUEST["teacherPackCost"] = $_REQUEST["teacherCount"] * TEACHERPACK_COST;

            $_REQUEST["foodPackCost"] = $_REQUEST["foodPackCount"] * FOODPACK_COST;


            /*if ($_REQUEST["hasFood"] == "yes") {

                //$_REQUEST["foodPackCost"] = $_REQUEST["pupilCount"] * FOODPACK_COST;

            } else {
                $_REQUEST["foodPackCost"] = 0;
            }*/

            $_REQUEST["transferCost"] = floatval($_REQUEST["transferCost"]);

            $_REQUEST["discount"] = floatval($_REQUEST["discount"]);
            //---------------------------------------------------------
            $_REQUEST["orderCost"] = $_REQUEST["packCost"] - $_REQUEST["discount"];
            $_REQUEST["orderCost"] = $_REQUEST["orderCost"] > 0 ? $_REQUEST["orderCost"] : 0;

            $_REQUEST["bribePercent"] = floatval($_REQUEST["bribePercent"]);
            $_REQUEST["bribePercent"] = $_REQUEST["bribePercent"] > $_REQUEST["packagePrice"] ? $_REQUEST["packagePrice"] : $_REQUEST["bribePercent"]; // вдруг ввели скидку больше, чем нужно представить
            $_REQUEST["bribe"] = $_REQUEST["pupilCount"] * $_REQUEST["bribePercent"];


            $_REQUEST["totalCost"] = $_REQUEST["orderCost"] - $_REQUEST["transferCost"];
            $_REQUEST["moneyToCash"] = $_REQUEST["totalCost"] - $_REQUEST["bribe"];

            log_debug(var_export($_REQUEST, true));
            $result = array(
                "totalCost" => $_REQUEST["totalCost"] + $_REQUEST["discount"],
                "totalCostDiscount" => $_REQUEST["totalCost"],
                "moneyToCash" => $_REQUEST["moneyToCash"],
                //"driverCost" => $driverCost,
                "foodCost" => $_REQUEST["foodPackCost"] + $_REQUEST["teacherPackCost"],
                "orderCost" => $_REQUEST["orderCost"],
                "packCost" => $_REQUEST["packCost"],
                "packPrice" => $_REQUEST["packPrice"],
                "transferCost" => $_REQUEST["transferCost"],
                "bribe" => $_REQUEST["bribe"]
            );
            $response["result"] = $result;


            if ($action == "schoolGetCostSave") {

                $currentOrder = null;
                if (isset($_REQUEST["orderId"]) && !empty($_REQUEST["orderId"])){
                    $url = "http://b24.next.kz/rest/bitrix.php";
                    $params = array(
                        "action" => "order.get.google",
                        "id" => $_REQUEST["orderId"]
                    );
                    $data = query("GET", $url, $params);
                    $currentOrder = isset($data["result"]) ? $data["result"] : null;
                }

                $adminToken = get_access_data(true);
                $order = OrderHelper::ConstructSchoolOrder($_REQUEST, $adminToken);

                if (!is_null($currentOrder)){
                    $order["FinanceInfo"] = $currentOrder["FinanceInfo"];
                }

                if ($_REQUEST["dealId"] != "") {
                    $updateResult = OrderHelper::updateOrderDeal($order, $adminToken, true, $_REQUEST["dealId"]);
                    
                } else {
                    $order["DealId"] = OrderHelper::updateOrderDeal($order, $adminToken);
                }
                
                $updateProductsResult = OrderHelper::updateDealProductSet($order, $adminToken);
                $response["order"] = $order;
                


                $saveResult = queryGoogleScript(array(
                    "event" => "OnOrderSaveRequested",
                    "orderJson" => json_encode($order),
                    "order" => $order,
                ));

                $response["saveResult"] = $saveResult;
            }
            break;
        
    }




    header('Content-Type: application/json');
    echo json_encode($response);



/*
 * order["Id"] = id;
  order["Status"] = "Заказ подтвержден";
  order["DealId"] = sourceOrder["DealId"];
  order["ContactId"] = sourceOrder["ContactId"];
  //--------------------------------------------
  order["ClientName"] = sourceOrder["ClientName"];
  order["KidName"] = sourceOrder["KidName"];
  order["Phone"] = sourceOrder["Phone"];
  order["Center"] = hash[3];
  order["DateOfEvent"] = hash[6]; //sourceOrder["DateOfEvent"];

  order["TotalCost"] = 0;
  order["User"] = sourceOrder["User"];
  order["FullPriceType"] = true;
  //------------------------------------
  var event = {
    'Event' : hash[4],
    'Zone' : hash[5],
    'Date' : hash[6],
    'StartTime' : hash[7],
    'Duration' : hash[8],
    'GuestCount' : hash[9],
    'Cost' : 0
  };
  event["Date"] = FormatTime(event["StartTime"], event["Date"]);
  order["Event"] = event;

  var endDateTime = CunstructEndTime(event["Date"], event["Duration"]);
  order["FullPriceType"] = IsFullPrices(endDateTime);
  //----------------------------
  var clientInfo = {
    'Id' : order["Id"],
    'ClientName' : order["ClientName"],
    'KidName' : order["KidName"],
    'Code' : verifyData["Code"],
    'Status' : verifyData["Status"],
    'Date' : verifyData["Date"],
  };
  order["VerifyInfo"] = clientInfo;
  //-----------------------------
  var financeInfo = {
    'Id' : order["Id"],
    'Remainder' :  0,
    'Payed' : 0,
    'PaymentsView' : "",
    'Payments' : [],
    'TotalDiscount' : 0,
    'Increase' : hash[17] != "" ? hash[17] : 0,
    'IncreaseComment' : hash[18],
    'Discount' : hash[14] != "" ? hash[14] : 0,
    'DiscountComment' : hash[15],
    'LoyaltyCode' : hash[12],
    'LoyaltyDiscount' : 0,
    'AgentCode' : hash[13],
    'AgentDiscount' : 0,
  };
  if (financeInfo["IncreaseComment"] == "") financeInfo["Increase"] = 0;
  if (financeInfo["DiscountComment"] == "") financeInfo["Discount"] = 0;

  order["FinanceInfo"] = financeInfo;
  //----------------------------
  var banquetId = hash[10];
  order["BanquetInfo"] = banquetId != "" ? GetBanquetTranscript(banquetId, links) : null;
  var banquetInfo = {
    'BanquetId' : hash[10],
    'Comment' : "",
    'TranscriptStr' : "",
    'Items' : null,
    'Cost' : 0,
    'Cake' : "",
    'Candybar' : "",
    'Pinata' : "",
    'Date' : null
  };

    //-------------------------------
    var optionalId = hash[11];
    order["OptionalInfo"] = optionalId != "" ? GetOptionalTranscript(optionalId, links) : null;

    var optionalInfo = {
      'OptionalId' : hash[11],
      'Comment' : "",
      'TranscriptStr' : "",
      'Items' : null,
      'Cost' : 0,
      'Entertainment' : "",
      'Cameraman' : "",
      'TableBooking' : "",
      'MonitorSaver' : "",
      'Date' : null
    };


    //-------------------------------------
    order["Comment"] = hash[16];
    order["CreatedAt"] = new Date();
    order["UpdatedAt"] = order["CreatedAt"];
    order["TaskId"] = null;
*/