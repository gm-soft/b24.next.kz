<?php

    function createOrderDeal($order, $admin_token) {
        $payload = array();
        $payload["fields[CONTACT_ID]"] = $order["ContactId"];
        switch ($order["Event"]["Event"]){
            case "Аренда зоны":
                $payload["fields[TYPE_ID]"] = "SALE";
                break;

            case "Happy Birthday Pack":
                $payload["fields[TYPE_ID]"] = "1";
                break;

            case "Продажа буса":
                $payload["fields[TYPE_ID]"] = "5";
                break;
        }
        $payload["fields[ASSIGNED_BY_ID]"] = $order["UserId"];
        $payload["fields[TITLE]"] = "ID".$order["Id"]." ".$order["Event"]["Event"]." (".$order["Event"]["Zone"].")";
        switch($order["Status"]) {
            //
            case "Заказ подтвержден":
                $payload["fields[STAGE_ID]"] = "2"; // Подготовка аренды
                break;
            case "Аренда проведена":
                $payload["fields[STAGE_ID]"] = "7"; // Аренда проведена
                break;
            case "Аренда отменена":
                $payload["fields[STAGE_ID]"] = "4"; // Аренда отменена / сделка закрыта
                break;
            case "Сделка закрыта":
                $payload["fields[STAGE_ID]"] = "WON"; // оплата в нуле
                break;
        }
        $payload["fields[OPPORTUNITY]"] = $order["TotalCost"]; // UF_CRM_1474887988
        $payload["fields[UF_CRM_1474887988]"] = $order["TotalCost"];

        $payload["fields[UF_CRM_1467690712]"] = $order["Date"]; // string-UF_CRM_1474793606 datetime-UF_CRM_1467690712
        $payload["fields[UF_CRM_1474793606]"] = $order["DateAtom"];
        $payload["fields[UF_CRM_1467878967]"] = $order["Event"]["GuestCount"];

        $centerB24 = "128"; // NEXT Aport по дефолту UF_CRM_1468830187
        if ($order["Center"] == "NEXT Esentai") $centerB24 = "130";
        if ($order["Center"] == "NEXT Promenade") $centerB24 = "132";
        $payload["fields[UF_CRM_1468830187]"] = $centerB24;

        $payload["fields[UF_CRM_1471942476]"] = $order["FinanceInfo"]["Payed"];
        $payload["fields[UF_CRM_1473564600]"] = $order["FinanceInfo"]["Remainder"];

        $count = 0;
        foreach ($order["FinanceInfo"]["Payments"] as $key => $payment) {
            $paymentText = "[".$payment["receiptDate"]."] ".$payment["paymentValue"];
            $payload["fields[UF_CRM_1474861311][".$count."]"] = $paymentText;
            $count++;
        }
        $payload["fields[COMMENTS]"] = $order["Comment"];

        $banquetStr = !is_null($order["BanquetInfo"]) ? $order["BanquetInfo"]["TranscriptStr"] : "Информации по фуршету нет";
        $optionalStr = !is_null($order["OptionalInfo"]) ? $order["OptionalInfo"]["TranscriptStr"] : "Информации по опционалке нет";
        $payload["fields[UF_CRM_1474794453]"] = $banquetStr;
        $payload["fields[UF_CRM_1474794474]"] = $optionalStr;

        $banquetId = !is_null($order["BanquetInfo"]) ? $order["BanquetInfo"]["BanquetId"] : "Без фуршета";
        $optionalId = !is_null($order["OptionalInfo"]) ? $order["OptionalInfo"]["OptionalId"] : "Без опционалки";
        $payload["fields[UF_CRM_1474795341]"] = $banquetId;
        $payload["fields[UF_CRM_1474795352]"] = $optionalId;

        $payload["fields[UF_CRM_1474794962]"] = $order["FinanceInfo"]["TotalDiscount"];
        $payload["fields[UF_CRM_1474794983]"] = $order["FinanceInfo"]["LoyaltyDiscount"];

        $discountIndex = 0;
        //UF_CRM_1467881660 - [] КОды на скидку
        if ($order["FinanceInfo"]["LoyaltyDiscount"] != 0) {
            $payload["fields[UF_CRM_1467881660][".$discountIndex."]"] = $order["FinanceInfo"]["LoyaltyCode"];
            $discountIndex++;
        }
        if ($order["FinanceInfo"]["AgentDiscount"] != 0) {
            //
            $payload["fields[UF_CRM_1467881660][".$discountIndex . "]"] = $order["FinanceInfo"]["AgentCode"];
            $discountIndex++;
        }

        $payload["fields[UF_CRM_1474794998]"] = $order["FinanceInfo"]["AgentDiscount"];
        $payload["fields[UF_CRM_1474795020]"] = $order["FinanceInfo"]["Discount"];
        $payload["fields[UF_CRM_1474795029]"] = $order["FinanceInfo"]["DiscountComment"];

        $payload["fields[UF_CRM_1474795045]"] = $order["FinanceInfo"]["Increase"];
        $payload["fields[UF_CRM_1474797140]"] = $order["FinanceInfo"]["IncreaseComment"];

        foreach ($payload as $key => $item) {//
            if (gettype($item) !== 'string') continue;
            $item = str_replace("\n", ". ", $item);
            $item = str_replace("\"", "'", $item);
            $payload[$key] = $item;
        }
        $payload["auth"] = $admin_token;

        $createResult = call("crm.deal.add", $payload);
        $dealId = $createResult["result"];
        return $dealId;

    }

    function updateDealProductSet(array $order, $token = null){
        $token = is_null($token) ? get_access_data(true) : $token;
        $eventId = 0;
        // $banquetItems = [];
        // $optionalItems = [];

        //----------------------
        $event = $order["Event"];
        $zone = $event["Zone"];
        $eventPrice = $order["Event"]["Cost"];
        switch($event["Event"]) {
            /*
            case  "Аренда зоны":
                $prices = GetAreaPrices();
                foreach ($prices as $key => $value) {
                if ($value["Zone"] != $zone) continue;
                $eventId = $value["ProductId"];
                break;
            }
            break;*/

            case  "Happy Birthday Pack":
                $eventId = 1636;
                break;
            case  "Корпоративное мероприятие":
                $eventId = 1638;
                break;
            case  "Школы и лагеря":
                $eventId = 1640;
                break;
        }
        //--------------------------------
        // $banquetItems = $order["BanquetInfo"] != null ? $order["BanquetInfo"]["Items"] : null;
        // $optionalItems = $order["OptionalInfo"] != null ? $order["OptionalInfo"]["Items"] : null;

        $payload = array();
        $payload["id"] = $order["DealId"];
        $payload["rows[0][PRODUCT_ID]"] = $eventId;
        $payload["rows[0][PRICE]"] = $eventPrice;
        $payload["rows[0][QUANTITY]"] = "1";
        $payload["auth"] = $token;
        //-----------------------------------------
        /*
        $itemIndex = 1;
        if ($banquetItems != null) {
          //
          for (var i in $banquetItems) {
              //
                $itemId = $banquetItems[i]["itemId"];
                $itemPrice = $banquetItems[i]["price"];
                $itemCount = $banquetItems[i]["count"];
              if ($itemId == "" || $itemId == 0) {
                  //
                  var $searchUrl = "https://next.bitrix24.kz/rest/crm.product.list";
                  var $payload = {
                      'filter[NAME]': banquetItems[i]["name"],
              'auth' : auth
            };
            var searchResult = PostJsonResponse(searchUrl, payload);
            if (searchResult != null && searchResult["total"] > 0) {
                //
                itemId = searchResult["result"][0]["ID"];
            }
            else {
                //
                var addResult = AddOrUpdateProduct(banquetItems[i], "208", auth); // 208 - неучтенные товары
                itemId = addResult != null ? addResult["result"] : null;
            }

          }
              if (itemId == null) continue;
              payload["rows["+itemIndex+"][PRODUCT_ID]"] = itemId;
              payload["rows["+itemIndex+"][PRICE]"] = itemPrice;
              payload["rows["+itemIndex+"][QUANTITY]"] = itemCount;
              itemIndex++;
          }
        }
        //---------------------------------------------------

        if (optionalItems != null) {
          for (var i in optionalItems) {
              //
              var itemId = optionalItems[i]["itemId"];
              var itemPrice = optionalItems[i]["price"];
              var itemCount = optionalItems[i]["count"];
              if (itemId == "" || itemId == 0) {
                  var addResult = AddOrUpdateProduct(optionalItems[i], "208", auth); // 208 - неучтенные товары
                  itemId = addResult != null ? addResult["result"] : null;
              }
              if (itemId == null) continue;
              payload["rows["+itemIndex+"][PRODUCT_ID]"] = itemId;
              payload["rows["+itemIndex+"][PRICE]"] = itemPrice;
              payload["rows["+itemIndex+"][QUANTITY]"] = itemCount;
              itemIndex++;
          }
        }
        */
        $productUpdate = call("crm.deal.productrows.set", $payload);
        return $productUpdate;
    }

    /**
     * Проверка, является ли дата выходным днем
     *
     * @param $date
     * @return bool
     */
    function isDateHoliday($date) {
        try {
            if (gettype($date) == "string") $date = new DateTime($date);
            if (is_null($date)) return true;
            $timestamp = $date->getTimestamp();
            $dayOfWeek = date("N", $timestamp);
            log_debug(var_export($dayOfWeek, true));
            return $dayOfWeek == 6 || $dayOfWeek == 7;

        } catch (Exception $ex){
            process_error(var_export($ex, true));
        }
        return true;
    }









