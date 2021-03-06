<?php
	require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");

	header('Content-Type: application/json');

    $response = array(
		"result" => false,
		"request" => $_REQUEST
	);
	$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : NULL;

	if (is_null($action)) {
		echo json_encode($response);
		die();
	}

    $access_data = ApplicationHelper::readAccessData();
    if (is_null($access_data) ) {
        $response["error"] = "No access data available";
        $response["error_description"] = "No access data available";
        ApplicationHelper::processError("No access data available");
        echo json_encode($response);
        die();
    }

    $ip = $_SERVER['REMOTE_ADDR'];
    $browser = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "unknown";
    $req = json_encode($_REQUEST);
    ApplicationHelper::log("bitrix.php: action=".$action.". Client: ".$ip.", (".$req.")");


	switch ($action) {

        case "task.update.all":

            $deal_id = $_REQUEST["deal_id"];
            $deal = BitrixHelper::getDeal($deal_id, $access_data["access_token"] );
            $type = isset($_REQUEST["type"]) ? $_REQUEST["type"] : null;

            $types = array(0 => "admin", 1 => "food", 2 => "design");
            $url = "https://b24.next.kz/rest/bitrix.php";
            $params = array("deal_id" => $_REQUEST["deal_id"]);

            foreach ($types as $key => $value) {
                $params["type"] = $value;
                $params["action"] = "task.update";
                $response[$value."_task"] = query("POST", $url, $params);
                $params["action"] = "task.checklist.update";
                $response[$value."_checklist"] = query("POST", $url, $params);
            }

            //------------------------------

            break;

        case "task.update":

            $deal_id = $_REQUEST["deal_id"];
            $deal = BitrixHelper::getDeal($deal_id, $access_data["access_token"] );
            $type = isset($_REQUEST["type"]) ? $_REQUEST["type"] : null;

            switch ($type) {
                case "food":
                    $task_id_field = "UF_CRM_1476413526";
                    $task_id = $deal[$task_id_field];
                    $responsible_id = $deal["UF_CRM_1476586471"];
                    $category_type = "96";
                    $parent_task_id = $deal["UF_CRM_1476413504"];
                    $task_id_field = "UF_CRM_1476413526";
                    $message_type = "фуршета ";
                    break;

                case "design":
                    $task_id_field = "UF_CRM_1476413556";
                    $task_id = $deal[$task_id_field];
                    $responsible_id = $deal["UF_CRM_1476586451"];
                    $category_type = "98";
                    $parent_task_id = $deal["UF_CRM_1476413504"];
                    $message_type = "оформления ";
                    break;

                case "admin":
                    $task_id_field = "UF_CRM_1476413504";
                    $task_id = $deal[$task_id_field];
                    $responsible_id = $deal["UF_CRM_1474604947"];
                    $category_type = "94";
                    $parent_task_id = null;
                    $message_type = "мероприятия ";
                    break;
                default:
                    $task_id = null;
                    $responsible_id = null;
                    $parent_task_id = null;
                    break;
            }

            if (is_null($task_id)) {
                $response["error"] = "BAD REQUEST";
                $response["error_description"] = "No task_id for pointed type in deal defined";
                break;
            }
            // Получаю список товаров, прикрепленных к сделке
            $attached_productrows = BitrixHelper::getAttachedProductrows($deal_id, $access_data["access_token"]);

            $products = ApplicationHelper::filterByField($category_type, "CATEGORY_VALUE", $attached_productrows);

            if ($type == "admin" ||  (!is_null($products) && count($products) != 0)) {
                $task_array = BitrixHelper::constructTask($deal, "Подготовка ".$message_type."к заказу \"".$deal["TITLE"]."\"", $responsible_id, $parent_task_id);
                $task_update_result = BitrixHelper::createOrUpdateTask($deal, $task_id_field, $task_id, $task_array, $access_data["access_token"]);
                $task_id = $task_update_result["task_id"];


                $response["result"] = $task_id;
                
                $message = "Была обновлена(создана) задача для заказа ".$deal["TITLE"].".\n";
                $message = $message."Список подзадач для выполнения был прикреплен к задаче.\n";
                $message = $message."Проверьте задачу по ссылке https://next.bitrix24.kz/company/personal/user/".$responsible_id."/tasks/task/view/".$task_id."/";
                BitrixHelper::notifyUser($responsible_id, $message, $access_data["access_token"]);
            }
            break;

        case "task.checklist.get":

            $deal_id = $_REQUEST["deal_id"];
            $deal = BitrixHelper::getDeal($deal_id, $access_data["access_token"] );
            $type = isset($_REQUEST["type"]) ? $_REQUEST["type"] : null;

            $parent_task_id = $deal["UF_CRM_1476413504"];

            switch ($type){
                case "food":
                    $task_id = $deal["UF_CRM_1476413526"];
                    $responsible_id = $deal["UF_CRM_1476586471"];
                    $category_type = "96";
                    break;

                case "design":
                    $task_id = $deal["UF_CRM_1476413556"];
                    $responsible_id = $deal["UF_CRM_1476586451"];
                    $category_type = "98";
                    break;

                case "admin":
                    $task_id = $deal["UF_CRM_1476413504"];
                    $responsible_id = $deal["UF_CRM_1474604947"];
                    $category_type = "94";
                    break;
                default:
                    $task_id = null;
                    $responsible_id = null;
                    break;
            }

            if (is_null($task_id)) {
                $response["error"] = "BAD REQUEST";
                $response["error_description"] = "No task_id for pointed type in deal defined";
                break;
            }

            $attached_productrows = BitrixHelper::getAttachedProductrows($deal_id, $access_data["access_token"]);

            $response["attached_productrows"] = $attached_productrows;
            $products = ApplicationHelper::filterByField($category_type, "CATEGORY_VALUE", $attached_productrows);




            if (!is_null($products) && count($products) != 0) {

                $current_checklist = BitrixHelper::getTaskChecklist($task_id, $access_data["access_token"]);
                //log_debug("TASK_ID = ".$task_id."\n");

                $update_result = BitrixHelper::getUpdatedChecklist($current_checklist, $products, $task_id);

                $response["result"] = $update_result;
            }

            break;

        case "task.checklist.update":

            $deal_id = $_REQUEST["deal_id"];
            $deal = BitrixHelper::getDeal($deal_id, $access_data["access_token"] );
            $type = isset($_REQUEST["type"]) ? $_REQUEST["type"] : null;

            switch ($type){
                case "food":
                    $task_id = $deal["UF_CRM_1476413526"];
                    break;

                case "design":
                    $task_id = $deal["UF_CRM_1476413556"];
                    break;

                case "admin":
                    $task_id = $deal["UF_CRM_1476413504"];
                    break;
                default:
                    $task_id = null;
                    $responsible_id = null;
                    break;
            }
            
            $checklist_data = query("POST", "https://b24.next.kz/rest/bitrix.php", array(
               "action" => "task.checklist.get",
                "deal_id" => $deal_id,
                "type" => $type
            ));

            $response["clear_list"] = BitrixHelper::clearChecklist($task_id, $access_data["access_token"]);
            $response["result"] = BitrixHelper::batch_commands($checklist_data["result"]["checklist_commands"], $access_data["access_token"]);

            $response["item_history"] = array(
                "added" => $checklist_data["result"]["added"],
                "subtracted" => $checklist_data["result"]["subtracted"],
                "removed" => $checklist_data["result"]["removed"],
                "changed" => $checklist_data["result"]["changed"],
                "updated_checklist" => $checklist_data["result"]["checklist"],
                "checklist_commands" => $checklist_data["result"]["checklist_commands"],

            );
            $description = "[b]Сводка последних изменений на дату ".ApplicationHelper::formatDate(time()).":[/b]\n";
            //---------------------
            if (count($checklist_data["result"]["changed"]) > 0) {
                $description .= "[i]Измененные товары:[/i]\n";
                foreach ($checklist_data["result"]["changed"] as $key => $value){
                    $description .= $value["PRODUCT_NAME"]." ".$value["OLD_QUANTITY"]." -> ".$value["QUANTITY"]." (Изменение на ".$value["CHANGED_QUANTITY"].")\n";
                }
            }
            //---------------------
            if (count($checklist_data["result"]["removed"]) > 0) {
                $description .= "[i]Удаленные товары:[/i]\n";
                foreach ($checklist_data["result"]["removed"] as $key => $value){
                    $description .= $value["PRODUCT_NAME"]." (".$value["OLD_QUANTITY"].")\n";
                }
            }
            //---------------------
            if (count($checklist_data["result"]["added"]) > 0) {
                $description .= "[i]Добавленные товары:[/i]\n";
                foreach ($checklist_data["result"]["added"] as $key => $value){
                    $description .= $value["PRODUCT_NAME"]." (".$value["QUANTITY"].")\n";
                }
            }


            $task_update = BitrixHelper::callMethod("task.item.update", array(
                "auth" => $access_data["access_token"],
                0 => $task_id,
                1 => array("DESCRIPTION" => $description),
                
            ));

            //$debug_content = "Commands: ".var_export($data["batch_commands"], true);
            //log_debug($debug_content);
            //$response["result"] = $batch_result;
            //$response["result"] = true;
            break;

        case "checklist.clear" :

            $deal_id = $_REQUEST["deal_id"];
            $deal = BitrixHelper::getDeal($deal_id, $access_data["access_token"] );
            $type = isset($_REQUEST["type"]) ? $_REQUEST["type"] : null;

            switch ($type){
                case "food":
                    $task_id = $deal["UF_CRM_1476413526"];
                    break;

                case "design":
                    $task_id = $deal["UF_CRM_1476413556"];
                    break;

                case "admin":
                    $task_id = $deal["UF_CRM_1476413504"];
                    break;
                default:
                    $task_id = null;
                    $responsible_id = null;
                    break;
            }
            //$curr_list = query("GET", "https://next.bitrix24.kz/rest/task.checklistitem.getlist", array("auth" => $access_data["access_token"], 0 => $task_id));
            //$response["curr_list"] = $curr_list;
            $response["result"] = BitrixHelper::clearChecklist($task_id, $access_data["access_token"]);
            break;

        case "task.checklist.current":

            $deal_id = $_REQUEST["deal_id"];
            $deal = BitrixHelper::getDeal($deal_id, $access_data["access_token"] );
            $type = isset($_REQUEST["type"]) ? $_REQUEST["type"] : null;

            switch ($type) {
                case "food":
                    $task_id = $deal["UF_CRM_1476413526"];
                    //$responsible_id = $deal["UF_CRM_1476586471"];
                    //$category_type = "96";
                    break;

                case "design":
                    $task_id = $deal["UF_CRM_1476413556"];
                    //$responsible_id = $deal["UF_CRM_1476586451"];
                    //$category_type = "98";
                    break;

                case "admin":
                    $task_id = $deal["UF_CRM_1476413504"];
                    //$responsible_id = $deal["UF_CRM_1474604947"];
                    //$category_type = "94";
                    break;
                default:
                    $task_id = null;
                    $responsible_id = null;
                    break;
            }
            if (is_null($task_id)) {
                $response["error"] = "task_id is null";
                break;
            }
            $current_checklist = BitrixHelper::callMethod("task.checklistitem.getlist" , array(0 => $task_id, "auth" => $access_data["access_token"]));
            //$current_checklist = get_task_checklist($task_id, $access_data["access_token"]);
            $response["result"] = $current_checklist;
            
            break;

        case "task.setresponsible":
            //$task_responsible = isset($_REQUEST["task_responsible"]) ? $_REQUEST["task_responsible"] : null;
            break;

        case "lead.create":

            if (!isset($_REQUEST["title"]) || !isset($_REQUEST["phone"])) {
                $response["result"] = null;
                $response["error"] = "Отсутствует имя или телефон";
                break;
            }

            $auth = BitrixHelper::getAuth(true);
            $leadName = $_REQUEST["title"];
            $phone = BitrixHelper::formatPhone($_REQUEST["phone"]);
            $sourceId = isset($_REQUEST["source"]) ? $_REQUEST["source"] : "11";
            $sourceDescription = isset($_REQUEST["source_description"]) ? $_REQUEST["source_description"] : "";
            $sourceCenter = isset($_REQUEST["center"]) ? $_REQUEST["center"] : "";
            $comments = isset($_REQUEST["comments"]) ? $_REQUEST["comments"] : "";

            $params = [
                "fields[TITLE]" => $leadName,
                "fields[PHONE][0][VALUE]" => $phone,
                "fields[EMAIL][0][VALUE]" => $_REQUEST["email"],
                "fields[SOURCE_ID]" => $sourceId,
                "fields[SOURCE_DESCRIPTION]" => $sourceDescription,
                "fields[STATUS_ID]"=> "NEW",
                "fields[OPENED]"=> "Y",
                "fields[ASSIGNED_BY_ID]" => "72",
                "fields[CREATED_BY_ID]" => "72",
                "fields[UF_CRM_1495122472]" => $sourceCenter, // Центр
                "fields[COMMENTS]" => $comments,
                "auth" => $auth
            ];
            $result = null;
            for($i = 0; $i < 3; $i++) {
                $result = BitrixHelper::callMethod("crm.lead.add", $params);
                if (!isset($result["error"])) {
                    break;
                }
            }

            if (isset($result["error"]))
            {
                $serverName = isset($_REQUEST["server_name"]) ? $_REQUEST["server_name"] : $sourceId;
                $emailContent = "<h1>Заявка с лендинга $serverName</h1>";
                $emailContent .= "<p>Пришла заявка с лендинга от:<br>";
                $emailContent .= "<b>Имя</b>: ".$_REQUEST["title"]."<br>";
                $emailContent .= "<b>Email</b>: ".$_REQUEST["email"]."<br>";
                $emailContent .= "<b>Телефон</b>: ".$phone."<br></p>";
                $emailContent .= "<b>Причина</b>: ".$result["error"]."<br></p>";

                $receiver = "business@next.kz";
                $subject = "Необработанная заявка с лендинга";
                $result = MailSmtp::SendEmail($receiver, $subject, $emailContent);
            }
            $response["result"] = $result;
            break;

        case "deals.get":
            $filterField = isset($_REQUEST["field"]) ? $_REQUEST["field"] : null;
            $filterValue = isset($_REQUEST["value"]) ? $_REQUEST["value"] : null;

            $fields = !is_null($filterField) ? array($filterField) : array();
            $values = !is_null($filterValue) ? array($filterValue) : array();

            $openDeals = BitrixHelper::getDeals($fields, $values, $access_data["access_token"]);
            $response["total"] = count($openDeals);
            $response["result"] = $openDeals;

            break;

        /*case "deals.multifields.get":
            $filterFields = isset($_REQUEST["fields"]) ? $_REQUEST["fields"] : null;
            $filterValues = isset($_REQUEST["values"]) ? $_REQUEST["values"] : null;

            $fields = !is_null($filterFields) ? $filterFields : array();
            $values = !is_null($filterValues) ? $filterValues : array();

            $openDeals = BitrixHelper::getDeals($fields, $values, $access_data["access_token"]);
            $response["total"] = count($openDeals);
            $response["result"] = $openDeals;

            break;*/

        case "order.get.google":
            $res = queryGoogleScript(array(
                "id" => $_REQUEST["id"],
                "event" => "OnOrderRequested",

                )
            );

            $response["result"] = isset($res["result"]) ? $res["result"] : $res;
            break;

        case "center.deals.get":

            $stage = isset($_REQUEST["stageId"]) ? $_REQUEST["stageId"] : "2";
            $filterFields = isset($_REQUEST["filterFields"]) ? $_REQUEST["filterFields"] : [
                "UF_CRM_1468830187",
                "STAGE_ID"
            ];
            $filterValues = isset($_REQUEST["filterValues"]) ? $_REQUEST["filterValues"] : [
                $_REQUEST["center"],
                $stage
            ];

            $openOrders = BitrixHelper::getDeals($filterFields, $filterValues, $access_data["access_token"]);
            $closedOrders = BitrixHelper::getDeals(array("UF_CRM_1468830187", "STAGE_ID"), array($_REQUEST["center"], "7"), $access_data["access_token"]);

            if (isset($_REQUEST["period"])){

                $period = intval($_REQUEST["period"]) * 24 * 3600;
                $openOrders = filterByPeriod($openOrders, $period);
                $closedOrders = filterByPeriod($closedOrders, $period);
            }

            $response["total"] = count($openOrders);
            $response["result"] = $openOrders;
            $response["openOrders"] = $openOrders;
            $response["closedOrders"] = $closedOrders;

            break;

        case "companies.get":
            $byUser = isset($_REQUEST["byUser"]) ? $_REQUEST["byUser"] : null;
            $companies = BitrixHelper::getCompanies($byUser, $access_data["access_token"]);

            $response["total"] = count($companies);
            $response["result"] = $companies;
            break;

        case "contacts.get":
            $byCompany = isset($_REQUEST["byCompany"]) ? $_REQUEST["byCompany"] : null;
            $byPhone = isset($_REQUEST["byPhone"]) ? $_REQUEST["byPhone"] : null;


            if (!is_null($byPhone)) {
                $contacts = BitrixHelper::searchContact($byPhone, $access_data["access_token"]);
            } elseif(!is_null($byCompany)){
                $contacts = BitrixHelper::getContactsOfTheCompany($byCompany, $access_data["access_token"]);
            } else {
                $contacts = null;
            }

            $response["total"] = count($contacts);
            $response["result"] = $contacts;
            break;

        case "contact.create":
            $birthday = $_REQUEST["birthday"];
            $birthAtom = $birthday."T12:00+03:00";
            $params = array(
                "fields[NAME]" => $_REQUEST["name"],
                "fields[LAST_NAME]"=> $_REQUEST["lastName"],
                "fields[OPENED]" => "Y",
                "fields[SOURCE_ID]" => "SELF",
                "fields[TYPE_ID]" => "CLIENT",
                "fields[BIRTHDATE]" => $birthAtom,
                "fields[UF_CRM_1468207818]" => array(0 => $_REQUEST["parent"]),
                "fields[PHONE][0][VALUE]" => $_REQUEST["phone"],
                "fields[PHONE][0][VALUE_TYPE]" => "WORK",
                "fields[ASSIGNED_BY_ID]" => $_REQUEST["assignedBy"],
                "auth" => $access_data["access_token"]
            );
            $createResult = BitrixHelper::callMethod("crm.contact.add", $params);
            $contactId = $createResult["result"];

            $contact = BitrixHelper::getContact($contactId, $params);
            $response["result"] = $contact;
            break;
    }


	echo json_encode($response);

	function filterByPeriod($orders, $ms){
        $now = time();
        $tmp = [];
        foreach ($orders as $deal){

            $date = new DateTime($deal["BEGINDATE"]); // BEGINDATE UF_CRM_1467690712

            if ($now - $date->getTimestamp() > $ms) continue;
            $tmp[] = $deal;
        }
        $orders = $tmp;
        return $orders;
    }
