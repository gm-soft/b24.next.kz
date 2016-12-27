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

            include $_SERVER["DOCUMENT_ROOT"]."/sales/shared/prices.php";


            switch ($_REQUEST["pack"]) {
                case 'basePack':
                    $_REQUEST["packType"] = "Базовый";
                    break;
                case 'standardPack':
                    $_REQUEST["packType"] = "Стандартный";
                    break;
                case 'allInclusive':
                    $_REQUEST["packType"] = "Все включено";
                    break;
                default:
                    $_REQUEST["packType"] = "Пакет не определен";
                    break;
            }

            switch ($_REQUEST["packNameCode"]) {
                case 'newYear':
                    $_REQUEST["packName"] = "Новогодний";
                    break;
                case 'holidays':
                    $_REQUEST["packName"] = "Каникулярный";
                    break;
                default:
                    $_REQUEST["packName"] = "Спец-пакет не выбран";
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
                "bribe" => $_REQUEST["bribe"],

                "packName" => $_REQUEST["packName"],
                "packType" => $_REQUEST["packType"],

                "centerName" => $_REQUEST["centerName"],
                "centerNameRu" => $_REQUEST["centerNameRu"]

            );
            $response["result"] = $result;
            break;

        case 'schoolCreate':

            $adminToken = get_access_data(true);
            $url = "https://script.google.com/macros/s/AKfycbxjyTPPbRdVZ-QJKcWLFyITXIeQ1GwI7fAi0FgATQ0PsoGKAdM/exec";
            $idData = query("GET", $url, array(
                "event" => "OnIdIncrementedRequested"
            ));
            $id = $idData["result"];
            $order = OrderHelper::ConstructSchoolOrder($id, null, $_REQUEST, $adminToken);
            $order["DealId"] = OrderHelper::updateOrderDeal($order, $adminToken);
            $updateProductsResult = OrderHelper::updateDealProductSet($order, $adminToken);




            $saveResult = queryGoogleScript(array(
                "event" => "OnOrderSaveRequested",
                "orderJson" => json_encode($order),
                "order" => $order,
            ));
            $response["order"] = $order;
            $response["saveResult"] = $saveResult;
            $response["emailRes"] = sendEmailForFood($order);
            break;

        case 'schoolSaveChanges':
            $url = "http://b24.next.kz/rest/bitrix.php";
            $params = array(
                "action" => "order.get.google",
                "id" => $_REQUEST["orderId"]
            );
            $data = query("GET", $url, $params);
            $order = isset($data["result"]) ? $data["result"] : null;

            $changedOrder = OrderHelper::ConstructSchoolOrder($order["Id"], $_REQUEST, $adminToken, $order);
            $adminToken = get_access_data(true);

            $updateResult = OrderHelper::updateOrderDeal($changedOrder, $adminToken, true);
            $updateProductsResult = OrderHelper::updateDealProductSet($changedOrder, $adminToken);




            $saveResult = queryGoogleScript(array(
                "event" => "OnOrderSaveRequested",
                "orderJson" => json_encode($changedOrder),
                "order" => $changedOrder,
            ));
            $response["order"] = $changedOrder;
            $response["saveResult"] = $saveResult;

            if ($changedOrder["Event"]["FoodPackCount"] != $order["Event"]["FoodPackCount"])
            {
                $response["emailRes"] = sendEmailForFood($changedOrder);
            }
            break;
        
    }




    header('Content-Type: application/json');
    echo json_encode($response);

    function sendEmailForFood($order){
        $content = "<h1>Продажа для школы ID".$order["Id"]."</h1>";
        $content .= "<h2>Сводка фуршета ".$order["BanquetInfo"]["BanquetId"]."</h2>";
        $content .= "<hr>";
        $content .= "<p>Информация о запрошенных фуд-пакетах</p>";

        $content .= "<p>";
        $content .= "<table border=0 cellspacing=0 cellpadding=5> ";
        $content .= "<tr><td>Клиент</td> <td>".$order["Id"]."</td> </tr>";
        $content .= "<tr><td>Номер телефона</td> <td>".$order["Phone"]."</td> </tr>";
        $content .= "<tr><td>Центр проведения</td> <td>".$order["Center"]."</td> </tr>";
        $content .= "<tr><td>Дата мероприятия</td> <td>".$order["DateAtom"]."</td> </tr>";
        $content .= "<tr><td>Начало в</td> <td>".$order["Event"]["StartTime"]."</td> </tr>";
        $content .= "<tr><td>Приглашено гостей</td> <td>".$order["Event"]["GuestCount"]."</td> </tr>";
        $content .= "</table>";
        $content .= "</p>";

        $content .= "<p>Для заказа было запрошено ".$order["Event"]["FoodPackCount"]." фуд-пакетов по заказу ".$order["BanquetInfo"]["BanquetId"]."</p>";

        $content .= "<hr>";
        $content .= "<p><b>Комментарий к заказу:</b> <br>".$order["Comment"]."</p>";

        $subject = "Сводка фуршета ID".$order["Id"];
        $mailTo = "coordinator@next.kz";

        include $_SERVER["DOCUMENT_ROOT"]."/Helpers/SendMailSmtpClass.php";
        $smtp = new SendMailSmtpClass(EMAIL_LOGIN, EMAIL_PASSWORD, EMAIL_SMTP, EMAIL_FROM, EMAIL_PORT);
        $headers= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n"; // кодировка письма
        $headers .= "From: Next.kz <noreply@next.kz>\r\n"; // от кого письмо
        $headers .= "Bcc: m.poyarel@next.kz, y.alimbetova@next.kz, m.gorbatyuk@next.kz\r\n";

        $result =  $smtp->send($mailTo, $subject, $content, $headers);
        return $result;
    }


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