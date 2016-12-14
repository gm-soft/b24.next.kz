<?php

/**
 * Created by PhpStorm.
 * User: Next
 * Date: 14.12.2016
 * Time: 8:40
 */
class OrderHelper
{

    public static function ConstructOrder(array $request, $adminToken){}

    public static function ConstructSchoolOrder(array $request, $adminToken){
        if ($request["orderId"] == "" ){
            $url = "https://script.google.com/macros/s/AKfycbxjyTPPbRdVZ-QJKcWLFyITXIeQ1GwI7fAi0FgATQ0PsoGKAdM/exec";
            $idData = query("GET", $url, array(
                "event" => "OnIdIncrementedRequested"
            ));

            $id = $idData["result"];
            $order = array();
            $order["Id"] = $id;



        } else {
            $id = $request["orderId"];
            $data = queryGoogleScript(array(
                "event" => "OnOrderRequested",
                "id" => $id
            ));
            $order = $data["result"];
            //$order["Id"] = $id;
        }
        log_debug(var_export($order , true));

        switch ($request["status"]){
            case "initiated":
                $order["Status"] = "Заказ подтвержден";
                break;
            case "conducted":
                $order["Status"] = "Аренда проведена";
                break;
            case "closed":
                $order["Status"] = "Сделка закрыта";
                break;
            case "canceled":
                $order["Status"] = "Аренда отменена";
                break;

            default:
                $order["Status"] = "Заказ подтвержден";
                break;
        }

        $order["DealId"] = $request["dealId"];
        $order["ContactId"] = $request["contactId"];
        $order["CompanyId"] = $request["companyId"];
        //--------------------------------------------
        $order["ClientName"] = $request["contactName"];
        $order["KidName"] = $request["companyName"];
        $order["Phone"] = $request["contactPhone"];
        $order["Center"] = $request["centerName"];

        $datetime = BitrixHelper::constructDatetime($request["date"], $request["time"]);
        $ts = BitrixHelper::constructTimestamp($request["date"], $request["time"]);

        $order["ts"] = $ts;
        $order["DateOfEvent"] = $datetime; //sourceOrder["DateOfEvent"];
        $time = strtotime($datetime);
        $time = $time - (3 * 3600);


        $order["Date"] = str_replace(" ", "T", formatDate($datetime, "Y-m-d H:i:s+06:00"));
        $order["DateAtom"] = str_replace(" ", "T", formatDate($datetime, "Y-m-d H:i:s+06:00"));

        $order["TotalCost"] = $request["moneyToCash"];
        $order["UserId"] = $request["userId"];
        $order["User"] = $request["userFullname"];
        $order["FullPriceType"] = isDateHoliday($datetime);
        //------------------------------------
        $event = array(
            'Event' => "Школа/лагерь",
            'Zone' => "Без зоны",
            'Date' => $ts,
            'StartTime' => str_replace(":", "-", $request["time"]),
            'Duration' => $request["duration"],
            'GuestCount' => $request["pupilCount"],
            'Cost' => $request["moneyToCash"],
            //------------------------------
            // дополнительные данные для школ
            'TeacherCount' => $request["teacherCount"],
            'Pack' => $request["pack"],
            'PackPrice' => $request["packagePrice"],
            'PupilCount' => $request["pupilCount"],
            'PupilAge' => $request["pupilAge"],
            'Subject' => $request["subject"],

            'HasTransfer' => $request["hasTransfer"],
            'HasFood' => $request["hasTransfer"],
            'TransferCost' => $request["transferCost"],

            'TeacherBribePercent' => $request["bribePercent"],
            'TeacherBribe' => $request["bribe"],

            'Comment' => $request["comment"],

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
            'CompanyId' => $request["companyId"],
        );
        $order["VerifyInfo"] = $clientInfo;
        //----------------------------
        $paymentDate = formatDate($datetime, "Y-m-d H:i");
        $financeInfo = array (
            'Id' => $id,
            'Remainder' =>  0,
            'Payed' => $request["moneyToCash"],
            'PaymentsView' => "[".$paymentDate."] Сумма ".$request["moneyToCash"]."\n",
            'Payments' => array(),
            'TotalDiscount' => $request["discount"],
            'Increase' => 0,
            'IncreaseComment' => "",
            'Discount' => $request["discount"],
            'DiscountComment' => $request["discountComment"],
            'LoyaltyCode' => "",
            'LoyaltyDiscount' => 0,
            'AgentCode' => "",
            'AgentDiscount' => 0,
        );
        if ($financeInfo["DiscountComment"] == "") $financeInfo["Discount"] = 0;
        $order["FinanceInfo"] = $financeInfo;
        //--------------------------------------------
        if ($request["hasFood"] == "yes") {

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
                "user" => $request["userFullname"],
                "itemCount" => $request["pupilCount"],
                "center" => $request["centerNameRu"],
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
                    'TranscriptStr' => "Фуд пакет для школ (цена ".$tzf["price"].", кол-во ".$request["pupilCount"].") - ".$tzf["cost"],
                    'Items' => array(
                        0 => array(
                            'name' => 'Фуд пакет для школ',
                            'price' => $tzf["price"],
                            'measure' => 'шт',
                            'note' => '',
                            'increasePercent' => 0,
                            'itemId' => $tzf["bitrixId"],
                            'count' => $request["pupilCount"],
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

        $comment = $request["comment"] != "" ? $request["comment"]."\n" : "";
        $comment .= "--- Служебная информация ---\n";
        $comment .= "Возраст детей: ".$request["pupilAge"]."\n";
        $comment .= "Тема урока: ".$request["subject"]."\n";
        $comment .= "Выбранный пакет: ".$request["packName"]."\n";

        if ($request["hasFood"] == "yes"){
            $comment .= "Фуд-пакет, наличие: есть\n";
            $comment .= "Фуд-пакет, стоимость: ".$request["foodPackCost"]."\n";
        } else $comment .= "Фуд-пакет, наличие: отсутствует\n";


        $comment .= "Процент учителю: ".$request["bribe"]."\n";
        $comment .= "Стоимость трансфера: ".$request["transferCost"]."\n";

        $order["Comment"] = $comment;
        //--------------------------------
        $order["CreatedAt"] = str_replace(" ", "T", date("Y-m-d H:i:s+06:00", time() + 3600*6));
        $order["UpdatedAt"] = $order["CreatedAt"];
        $order["TaskId"] = null;

        $contact = BitrixHelper::getContact($request["contactId"], $adminToken);
        $order["ContactSource"] = !is_null($contact) ?  BitrixHelper::getInstanceSource($contact["ID"], "contact", $adminToken) : "Ошибка. Контакт не существует";
        $order["LeadSource"] = !is_null($contact) && !is_null($contact["LEAD_ID"]) ?  BitrixHelper::getInstanceSource($contact["LEAD_ID"], "lead", $adminToken) : "Лид отсутствует";

        return $order;
    }


}