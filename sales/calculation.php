<?php
    require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
    require($_SERVER["DOCUMENT_ROOT"]."/include/help.php");
    require($_SERVER["DOCUMENT_ROOT"]."/Helpers/BitrixHelperClass.php");
    require($_SERVER["DOCUMENT_ROOT"]."/Helpers/OrderHelperClass.php");
    require($_SERVER["DOCUMENT_ROOT"]."/include/order_helper.php");


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
                case 'basepack':
                    $_REQUEST["packName"] = "Базовый";
                    break;
                case 'standartpack':
                    $_REQUEST["packName"] = "Стандартный";
                    break;
                case 'newyear':
                    $_REQUEST["packName"] = "Новогодний";
                    break;
                case 'allinclusive':
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

            $_REQUEST["pupilCount"] = intval($_REQUEST["pupilCount"]);
            $_REQUEST["teacherCount"] = intval($_REQUEST["teacherCount"]);

            // $pupilAge = $_REQUEST["pupilAge"];

            $_REQUEST["packCost"] = $_REQUEST["packagePrice"] * $_REQUEST["pupilCount"];
            $_REQUEST["teacherPackCost"] = $_REQUEST["teacherCount"] * TEACHERPACK_COST;

            if ($_REQUEST["hasFood"] == "yes") {
                $_REQUEST["foodPackCost"] = $_REQUEST["pupilCount"] * FOODPACK_COST;
            } else {
                $_REQUEST["foodPackCost"] = 0;
            }

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

                $adminToken = get_access_data(true);
                $order = OrderHelper::ConstructSchoolOrder($_REQUEST, $adminToken);
                /*
                if ($_REQUEST["orderId"] == "" ){
                    $url = "https://script.google.com/macros/s/AKfycbxjyTPPbRdVZ-QJKcWLFyITXIeQ1GwI7fAi0FgATQ0PsoGKAdM/exec";
                    $idData = query("GET", $url, array(
                        "event" => "OnIdIncrementedRequested"
                    ));

                    $id = $idData["result"];
                    $order = array();
                    $order["Id"] = $id;



                } else {
                    $id = $_REQUEST["orderId"];
                    $data = queryGoogleScript(array(
                        "event" => "OnOrderRequested",
                        "id" => $id
                    ));
                    $order = $data["result"];
                    //$order["Id"] = $id;
                }
                log_debug(var_export($order , true));

                switch ($_REQUEST["status"]){
                    case "initiated":
                        $status = "Заказ подтвержден";
                        break;
                    case "conducted":
                        $status = "Аренда проведена";
                        break;
                    case "closed":
                        $status = "Сделка закрыта";
                        break;
                    case "canceled":
                        $status = "Аренда отменена";
                        break;

                    default:
                        $status = "Заказ подтвержден";
                        break;
                }
                $order["Status"] = "Заказ подтвержден";

                $order["DealId"] = $_REQUEST["dealId"];
                $order["ContactId"] = $_REQUEST["contactId"];
                $order["CompanyId"] = $_REQUEST["companyId"];
                //--------------------------------------------
                $order["ClientName"] = $_REQUEST["contactName"];
                $order["KidName"] = $_REQUEST["companyName"];
                $order["Phone"] = $_REQUEST["contactPhone"];
                $order["Center"] = $centerName;

                $datetime = BitrixHelper::constructDatetime($_REQUEST["date"], $_REQUEST["time"]);
                $ts = BitrixHelper::constructTimestamp($_REQUEST["date"], $_REQUEST["time"]);

                $order["ts"] = $ts;
                $order["DateOfEvent"] = $datetime; //sourceOrder["DateOfEvent"];
                $time = strtotime($datetime);
                $time = $time - (3 * 3600);


                $order["Date"] = str_replace(" ", "T", formatDate($datetime, "Y-m-d H:i:s+06:00"));
                $order["DateAtom"] = str_replace(" ", "T", formatDate($datetime, "Y-m-d H:i:s+06:00"));

                $order["TotalCost"] = $_REQUEST["moneyToCash"];
                $order["UserId"] = $_REQUEST["userId"];
                $order["User"] = $_REQUEST["userFullname"];
                $order["FullPriceType"] = isDateHoliday($datetime);
                //------------------------------------
                $event = array(
                    'Event' => "Школа/лагерь",
                    'Zone' => "Без зоны",
                    'Date' => $ts,
                    'StartTime' => str_replace(":", "-", $_REQUEST["time"]),
                    'Duration' => $_REQUEST["duration"],
                    'GuestCount' => $_REQUEST["pupilCount"],
                    'Cost' => $_REQUEST["moneyToCash"],
                    //------------------------------
                    // дополнительные данные для школ
                    'TeacherCount' => $_REQUEST["teacherCount"],
                    'Pack' => $_REQUEST["pack"],
                    'PackPrice' => $_REQUEST["packPrice"],
                    'PupilCount' => $_REQUEST["pupilCount"],
                    'PupilAge' => $_REQUEST["pupilAge"],
                    'Subject' => $_REQUEST["subject"],

                    'HasTransfer' => $_REQUEST["hasTransfer"],
                    'HasFood' => $_REQUEST["hasTransfer"],
                    'TransferCost' => $_REQUEST["transferCost"],

                    'TeacherBribePercent' => $_REQUEST["bribePercent"],
                    'TeacherBribe' => $_REQUEST["bribe"],

                    'Comment' => $_REQUEST["comment"],
                    
                );
                $order["Event"] = $event;
                //----------------------------
                $clientInfo = array(
                    'Id' => $id,
                    'ClientName' => $order["ClientName"],
                    'KidName' => $order["KidName"],
                    'Code' => "No code",
                    'Status' => "Не требуется",
                    'Date' => "",
                    'CompanyId' => $_REQUEST["companyId"],
                );
                $order["VerifyInfo"] = $clientInfo;
                //----------------------------
                $paymentDate = formatDate($datetime, "Y-m-d H:i");
                $financeInfo = array (
                    'Id' => $id,
                    'Remainder' =>  0,
                    'Payed' => $_REQUEST["moneyToCash"],
                    'PaymentsView' => "[".$paymentDate."] Сумма ".$_REQUEST["moneyToCash"]."\n",
                    'Payments' => array(),
                    'TotalDiscount' => $_REQUEST["discount"],
                    'Increase' => 0,
                    'IncreaseComment' => "",
                    'Discount' => $_REQUEST["discount"],
                    'DiscountComment' => $_REQUEST["discountComment"],
                    'LoyaltyCode' => "",
                    'LoyaltyDiscount' => 0,
                    'AgentCode' => "",
                    'AgentDiscount' => 0,
                );
                if ($financeInfo["DiscountComment"] == "") $financeInfo["Discount"] = 0;
                $order["FinanceInfo"] = $financeInfo;
                //--------------------------------------------
                if ($_REQUEST["has_food"] == "yes") {

                    if (is_null($order["BanquetInfo"])) {
                        $isNew = "1";
                        $tzfId = "";
                    }
                    else {
                        $isNew = "0";
                        $tzfId = $order["BanquetInfo"]["BanquetId"];
                    }

                    $tzfData = queryGoogleScript(array(
                        "event" => "OnTzfSchoolCreateRequested",
                        "user" => $_REQUEST["userFullname"],
                        "itemCount" => $_REQUEST["pupilCount"],
                        "center" => $centerNameRu,
                        "date" => str_replace(" ", ".", formatDate($datetime, "d m Y")),
                        "orderId" => $id,
                        "isNew" => $isNew,
                    ));
                    $tzf = $tzfData["result"];
                    log_debug(var_export($tzf, true));
                    if (is_null($tzf)) $order["BanquetInfo"] = null;
                    else {

                        if ($isNew == "1") $tzfId = $tzf["tzfId"];
                        $banquet = array(
                            'BanquetId' => $tzfId,
                            'Comment' => "",
                            'TranscriptStr' => "Фуд пакет для школ (цена ".$tzf["price"].", кол-во ".$_REQUEST["pupilCount"].") - ".$tzf["cost"],
                            'Items' => array(
                                0 => array(
                                    'name' => 'Фуд пакет для школ',
                                    'price' => $tzf["price"],
                                    'measure' => 'шт',
                                    'note' => '',
                                    'increasePercent' => 0,
                                    'itemId' => $tzf["bitrixId"],
                                    'count' => $_REQUEST["pupilCount"],
                                    'cost' => $tzf["cost"],
                                )
                            ),
                            'Cost' => $tzf["cost"],
                            'Cake' => "",
                            'Candybar' => "",
                            'Pinata' => "",
                            'Date' => date("d.m.Y", $datetime),

                            'CandybarCost' => 0,
                            'CakeCost' => 0,
                            'PinataCost' => 0,

                        );

                        $order["BanquetInfo"] = $banquet;
                    }


                } else {
                    $order["BanquetInfo"] = null;
                }

                $order["OptionalInfo"] = null;

                $comment = $_REQUEST["comment"] != "" ? $_REQUEST["comment"]."\n" : "";
                $comment .= "--- Служебная информация ---\n";
                $comment .= "Возраст детей: ".$_REQUEST["pupilAge"]."\n";
                $comment .= "Тема урока: ".$_REQUEST["subject"]."\n";
                $comment .= "Выбранный пакет: ".$_REQUEST["packName"]."\n";

                if ($_REQUEST["has_food"] == "yes"){
                    $comment .= "Фуд-пакет, наличие: есть\n";
                    $comment .= "Фуд-пакет, стоимость: ".$_REQUEST["foodPackCost"]."\n";
                } else $comment .= "Фуд-пакет, наличие: отсутствует\n";


                $comment .= "Процент учителю: ".$_REQUEST["bribe"]."\n";
                $comment .= "Стоимость трансфера: ".$_REQUEST["transferCost"]."\n";

                $order["Comment"] = $comment;
                //--------------------------------
                $order["CreatedAt"] = str_replace(" ", "T", date("Y-m-d H:i:s+06:00", time() + 3600*6));
                $order["UpdatedAt"] = $order["CreatedAt"];
                $order["TaskId"] = null;

                $admin_token = get_access_data(true);

                $contact = BitrixHelper::getContact($_REQUEST["contactId"], $admin_token);
                $order["ContactSource"] = !is_null($contact) ?  BitrixHelper::getInstanceSource($contact["ID"], "contact", $admin_token) : "Ошибка. Контакт не существует";
                $order["LeadSource"] = !is_null($contact) && !is_null($contact["LEAD_ID"]) ?  BitrixHelper::getInstanceSource($contact["LEAD_ID"], "lead", $admin_token) : "Лид отсутствует";
                */


                if ($_REQUEST["dealId"] != "") {
                    $updateResult = updateOrderDeal($order, $adminToken, true, $_REQUEST["dealId"]);
                    
                } else {
                    $order["DealId"] = updateOrderDeal($order, $adminToken);
                }
                
                $updateProductsResult = updateDealProductSet($order, $adminToken);
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