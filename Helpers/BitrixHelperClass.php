<?php

/**
 * Created by PhpStorm.
 * User: Next
 * Date: 04.12.2016
 * Time: 17:18
 */
class BitrixHelper
{

    public static function getAuth($tokenOnly = false) {
        $url = "http://b24.next.kz/rest/control.php";
        $params = array("action" => "getAccessToken");
        $result = query("GET", $url, $params);
        $result = isset($result["access_token"]) && $tokenOnly ? $result["access_token"] : $result;
        return $result;
    }

    /**
     * Вызов метода REST.
     *
     * @param string $method вызываемый метод
     * @param array $params параметры вызова метода
     *
     * @return array
     */
    public static function callMethod($method, $params)
    {
        return query("POST", "https://next.bitrix24.kz/rest/".$method, $params);
    }

    /**
     * Вызов методов REST способом BATCH.
     *
     * @param string $commands массив GET-запросов
     * @param string $access_token токен авторизации
     *
     * @return array
     */
    public static function batch($commands, $access_token){
        $batch_params = array("auth" => $access_token, "halt" => 0, "cmd" => $commands);
        $call_result = BitrixHelper::callMethod("batch", $batch_params);
        return $call_result;
    }

    /**
     * @param $commands
     * @param $access_token
     * @return array|null
     */
    public static function batch_commands($commands, $access_token){
        $result = array();
        $command_to_execute = array();
        $temp_array = array();

        for ($i = 0; $i < count($commands); $i++) {
            $temp_array[] = $commands[$i];

            if (count($temp_array) == 49){
                $command_to_execute[] = $temp_array;
                $temp_array = array();
            }
            if ($i == (count($commands) -1)) $command_to_execute[] = $temp_array;
        }

        foreach ($command_to_execute as $cmd) {
            $batch_result = BitrixHelper::batch($cmd, $access_token);
            $data = isset($batch_result["result"]) ? $batch_result["result"] : $batch_result;
            $result = array_merge($result, $data);
        }
        return count($result) > 0 ? $result : null;
    }


    /**
     * Добавляет к каждому элементу прикрепленных товаров к сделке категорию соответствующего ему товара по PRODUCT_ID
     *
     * @param $attached_productrows - прикрепленные товары к сделке
     * @param $access_token - токен авторизации
     * @return mixed
     */
    public static function addCategoryToProductrow($attached_productrows, $access_token) {
        //Получаю список товаров, которые дсотупны из прикрепленных по полю PRODUCT_ID,
        //так как польвоталельское поле КАТЕГОРИИ не доступно в списочных товарах
        $attached_products = BitrixHelper::requestProductsByProductrows($attached_productrows, $access_token);

        //Пробегаюсь по списочным товарам, чтобы найти соответствующий ему товар из CRM
        //по полю productrows[PRODUCT_ID] = product[ID]
        //и добавляю новое поле в списочный товар CATEGORY_VALUE, чтобы в дальнейшем отсортировать по нему
        foreach ($attached_productrows as $key => $value) {
            $searchable_field = "ID";
            $product = search_item_in_array($attached_productrows[$key]["PRODUCT_ID"], $searchable_field, $attached_products);
            if (is_null($product)) continue;
            $attached_productrows[$key]["CATEGORY_VALUE"] = $product["PROPERTY_180"]["value"];
            $attached_productrows[$key]["PREPARE_VALUE"] = $product["PROPERTY_182"]["value"];
        }
        return $attached_productrows;
    }

    /**
     * Функция создает или обновляет чек-лист, а именно добавляет позиции, если их нет в уже соществующем чек-листе.
     * В этом случае также добавляется элемент чек-листа в виде "Добавлен/обновлен список <date>"
     *
     * @param $products - список товаров для генерации чек-листа
     * @param $task_id - ID задачи, к которой привязывается чек-лист
     * @param $access_token - токен авторизации
     * @return array
     */
    public static function createOrUpdateChecklist($products, $task_id, $access_token) {
        $current_checklist = BitrixHelper::getTaskChecklist($task_id, $access_token);
        $checklist = BitrixHelper::constructChecklist($products);
        $commands = array();
        if (count($current_checklist) > 0)  {
            $checklist = BitrixHelper::getArrayDifference($checklist, $current_checklist, null, "TITLE");
        }
        if (count($checklist) > 0) {
            //$commands[] = "task.checklistitem.add?TASKID=".$task_id."&FIELDS[TITLE]=--- Добавлен/обновлен список ".format_atom_date(time()+(60*60*6))." ---&FIELDS[IS_COMPLETE]=N";
            foreach ($checklist as $item) {
                $commands[] = "task.checklistitem.add?TASKID=".$task_id."&FIELDS[TITLE]=".$item."&FIELDS[IS_COMPLETE]=N";
            }
        }

        $batch_result = BitrixHelper::batch_commands($commands, $access_token);
        return $batch_result;
    }

    /**
     * Функция создает или обновляет чек-лист, а именно добавляет позиции, если их нет в уже соществующем чек-листе.
     * В этом случае также добавляется элемент чек-листа в виде "Добавлен/обновлен список <date>"
     *
     * @param $checklist
     * @param $task_id - ID задачи, к которой привязывается чек-лист
     * @return array
     */
    public static function createChecklistCommands($checklist, $task_id) {
        $commands = array();

        if (count($checklist) == 0) return null;
        foreach ($checklist as $item) {
            //$cmd = "task.checklistitem.add?TASKID=".$task_id."&FIELDS[TITLE]=".$item["TITLE"]."&FIELDS[IS_COMPLETE]=".$item["IS_COMPLETE"];
            $cmd = "task.checklistitem.add?0=".$task_id."&1[TITLE]=".$item["TITLE"]."&1[IS_COMPLETE]=".$item["IS_COMPLETE"];
            //if ($item["IS_COMPLETE"]=="Y")
            //{
            //   $cmd = $cmd."&1[TOGGLED_BY]=".$item["TOGGLED_BY"]."&1[TOGGLED_DATE]=".$item["TOGGLED_DATE"];
            //}

            $commands[] = $cmd ;
        }

        // $batch_result = batch_commands($commands, $access_token);
        // return $batch_result;
        return $commands;
    }

    /**
     * @param $products - массив продуктов сделки
     * @param $category - номер категории
     * @return array
     */
    public static function filterProductsByCategory($category, $products) {
        $result = array();
        foreach ($products as $prod) {
            if ($prod["PROPERTY_180"]["value"] != $category) continue;
            $result[] = $prod;
        }
        return $result;
    }

    public static function extractChecklistItem($title){
        $result = trim(substr($title, 0, strpos($title, " - "))) ;
        return $result;
    }


    public static function getNameAndCount($checklist_item) {

        if (!isset($checklist_item["TITLE"])) return null;
        $product_name = BitrixHelper::extractChecklistItem($checklist_item["TITLE"]);
        $count = substr($checklist_item["TITLE"], strpos($checklist_item["TITLE"], " - ") + 2);
        $count = intval(substr($count, strpos($count, " ")));

        return array(
            "product_name" => $product_name,
            "count" => $count
        );
    }


    public static function clearChecklist($task_id, $access_token) {
        $result = array();
        $curr_list = query("GET", "https://next.bitrix24.kz/rest/task.checklistitem.getlist", array("auth" => $access_token, 0 => $task_id));
        $current_list = isset($curr_list["result"]) ? $curr_list["result"] : $curr_list;


        if (is_null($current_list)) return 0;
        if (count($current_list) == 0) return 0;
        foreach ($current_list as $value) {
            $command = "task.checklistitem.delete?0=".intval($value["TASK_ID"])."&1=".intval($value["ID"]);
            $result[] = $command;

            //$result[] = call("task.checklistitem.delete", array(0 => intval($value["TASK_ID"]), 1 => intval($value["ID"]), "auth" => $access_token));
        }

        $batch = batch_commands($result, $access_token);
        return $batch;

    }

    /**
     * Генерирует обновленный чеклист, возвращая список команд для батч-запроса, массивы добавленных,
     * удаленных или отнимаемых товаров.
     * Changed = список измененных позиций.
     * Added - список добавленных позиций, которых ранее не было.
     * Removed - список удаленных позиций из сделки, но которые были включены в чеклист. Учитываются только выполненные чеклист-позиции.
     * Subtracted - список позиций, кол-во которых было уменьшено
     *
     * @param $current_checklist - текущий чеклист, прикрепленный к задаче
     * @param $products - список прикрепленных товаров к сделке, отсортированный по категории
     * @param $task_id - ID задачи
     * @return array(
    "checklist_commands" => $checklist_commands,
    "changed" => $changed_items,
    "added" => $added_items,
    "removed" => $removed_items,
    "subtracted" => $subtracted_items,
    "checklist" => $new_checklist,
     * )
     */
    public static function getUpdatedChecklist($current_checklist, $products, $task_id) {
        $new_checklist = array();
        $changed_items = array();
        $removed_items = array();
        $subtracted_items = array();
        $added_items = array();
        $debug_content = "";

        $debug_content = "Current checklist: ".var_export($current_checklist, true);
        if (!is_null($current_checklist)) {
            foreach ($current_checklist as $key => $value) {

                $name_and_count = getNameAndCount($value);
                if (is_null($name_and_count)) continue;

                $value["PRODUCT_NAME"] = $name_and_count["product_name"];
                $value["OLD_QUANTITY"] = $name_and_count["count"];
                $value["CHANGED_QUANTITY"] = 0;

                $product = search_item_in_array($value["PRODUCT_NAME"], "PRODUCT_NAME", $products);

                // если товар из чеклиста не был найден в списке товаров, прикрепленных к сделке
                if (is_null($product)) {

                    $removed_items[] = $value;
                    if ($value["IS_COMPLETE"] == "N") {
                        unset($current_checklist[$key]);
                    }

                } else {
                    $value["TITLE"] = formatChecklistItem($product);
                    $value["QUANTITY"] = $product["QUANTITY"];
                    $value["CHANGED_QUANTITY"] = $product["QUANTITY"] - $value["OLD_QUANTITY"];
                    $value["QUANTITY"] = $product["QUANTITY"];

                    if ($value["CHANGED_QUANTITY"] != 0 ) $changed_items[] = $value;

                    if ($product["QUANTITY"] > $value["OLD_QUANTITY"]) {
                        $value["IS_COMPLETE"] = "N";
                    }
                    else if ($product["QUANTITY"] < $value["OLD_QUANTITY"] && $value["IS_COMPLETE"] == "Y") {

                        $value["IS_COMPLETE"] = "N";
                        $subtracted_items[] = $value;
                    }


                    $new_checklist[] = $value;

                }
            }
        }

        $debug_content = $debug_content."\nnew_checklist before: ".var_export($new_checklist, true);

        foreach ($products as $key => $value){

            $search_item = search_item_in_array($value["PRODUCT_NAME"], "PRODUCT_NAME", $new_checklist);
            if (!is_null($search_item)) continue;
            $checklist_item = array(
                "TITLE" => formatChecklistItem($value),
                "PRODUCT_NAME" => $value["PRODUCT_NAME"],
                "QUANTITY" => $value["QUANTITY"],
                "OLD_QUANTITY" => 0,
                "CHANGED_QUANTITY" => $value["QUANTITY"],
                "IS_COMPLETE" => "N",
            );
            $added_items[] = $checklist_item;
            $changed_items[] = $checklist_item;
            $new_checklist[] = $checklist_item;
        }
        $checklist_commands = createChecklistCommands($new_checklist, /*array_merge($new_checklist, $removed_items),*/ $task_id);
        $result = array(
            "checklist_commands" => $checklist_commands,
            "changed" => $changed_items,
            "added" => $added_items,
            "removed" => $removed_items,
            "subtracted" => $subtracted_items,
            "checklist" => $new_checklist,
        );
        $debug_content = $debug_content."\n new_checklist after: ".var_export($new_checklist, true);
        log_debug($debug_content);
        return $result;
    }





    /**
     * Возвращает элементы первого массива, которые не включены во второй массив. Разность множеств.
     * Можно указать поля, если массивы содержат другие ассоциативные массивы в роли своих элементов
     *
     * @param $first
     * @param $second
     * @param null $first_field_name
     * @param null $second_field_name
     * @return array
     */
    public static function getArrayDifference($first, $second, $first_field_name = null, $second_field_name = null){
        $result = array();
        foreach ($first as $key => $value) {

            // $searchable, $array, $field_name = null
            $searchable = is_null($first_field_name) ? $value : $value[$first_field_name];
            $array = $second;
            $field_name = !is_null($second_field_name) ? $second_field_name : null;

            $search_result = search_in_array($searchable, $array, $field_name);
            if ($search_result) continue;
            $result[] = $value;
        }
        return $result;
    }

    /**
     * Создает или обновляет задачу, если ранее она была создана. Так же обновляет сделку, а именно поле, где хранится ID задачи.
     * Возвратит ID задачи, созданной или обновленной
     *
     * @param $deal - сделка для обновления
     * @param $task_id_field - юзер-поле для хранения id задачи
     * @param $task_id - id задачи. Может быть "пустым", в т.ч. равным нулю
     * @param $task_array - массив полей задачи
     * @param $access_token - токен авторизации
     * @return array
     */
    public static function createOrUpdateTask($deal, $task_id_field, $task_id, $task_array, $access_token){

        $method = NULL;
        $update_params = NULL;
        $exist_task = query("GET", "https://next.bitrix24.kz/rest/task.item.getdata", array("auth" => $access_token, 0 => $task_id));
        //$exist_task = call("task.item.getdata", array("auth" => $access_token, 0 => $task_id));
        $update_result = array();

        if (isset($exist_task["result"])) {
            $method = "task.item.update";
            $update_params = array(
                "auth" => $access_token,
                0 => $task_id,
                1 => $task_array,
            );
        }
        else /*(isset($exist_task["error"]))*/ {
            $method = "task.item.add";
            $update_params = array( 0 => $task_array, "auth" => $access_token);

        }
        $update_result["method"] = $method;
        $task = query("GET", "https://next.bitrix24.kz/rest/".$method, $update_params);
        //$task = call($method, $update_params);
        //$update_result["task"] = $task;

        if ($method == "task.item.add" && isset($task["result"])) {
            $update_result["deal_update"] = call("crm.deal.update", array(
                "auth" => $access_token,
                "id" => $deal["ID"],
                "fields" => array($task_id_field => $task["result"]),
                "params" => array("REGISTER_SONET_EVENT" => "Y"),
            ));
            $task_id = isset($task["result"]) ? $task["result"] : $task;
        }
        $update_result["task_id"] = $task_id;
        return $update_result;
    }


    /**
     * @param $task_id
     * @param $access_token
     * @return array
     */
    public static function getTaskChecklist($task_id, $access_token){
        //$result = call("task.checklistitem.getlist", array(0=> $task_id, "auth" => $access_token, ));
        //$result = query("POST", "http://next.bitrix24.kz/rest/task.checklistitem.getlist", array(0=> $task_id, "auth" => $access_token, ));
        //next.bitrix24.kz/rest/task.checklistitem.getlist?auth=ey71gcw561pagvghzlxi6iacldsus0p3&0=1044
        
        //$result = query("GET", "http://next.bitrix24.kz/rest/task.checklistitem.getlist?0=".$task_id."&auth=".$access_token);
        $result = BitrixHelper::callMethod("task.checklistitem.getlist" , array(0 => $task_id, "auth" => $access_token));
        $result = isset($result["result"]) ? $result["result"] : $result;
        return $result;
    }

    /**
     * Запрос и получение списка товаров(product) по прикрепленным товарам к сделке(productrows)
     * @param $productrows - список прикрепленных товаров
     * @param $access_token - токен авторизации
     * @return array
     */
    public static function requestProductsByProductrows($productrows, $access_token){
        $commands = array();
        for ($i = 0; $i < count($productrows); $i++) {
            $commands[] = "crm.product.get?id=".$productrows[$i]["PRODUCT_ID"];
        }

        $result = BitrixHelper::batch_commands($commands, $access_token);
        $result = isset($result["result"]) ? $result["result"] : $result;
        foreach ($result as $key => $value) {
            $result[$key] = isset($value["result"]) ? $value["result"] : $value;
        }
        return $result;
    }

    /**
     * Конструирует задачу
     * @param $deal - Сделка
     * @param $title - заголовок задачи
     * @param $responsible_id - ID ответственного
     * @param null $parent_id - ID родительской задачи
     * @return array
     */
    public static function constructTask($deal, $title, $responsible_id, $parent_id = null){
        $event_date = formatDate($deal["UF_CRM_1474793606"]);
        $description = "[b]Информация по мероприятию:[/b]\nДата и время проведения: [i]".$event_date."[/i]";

        $task = array(
            "TITLE" => $title,
            "DESCRIPTION" => $description,
            "DEADLINE" => $deal["UF_CRM_1474793606"],
            "AUDITORS" => array(
                0 => $deal["ASSIGNED_BY_ID"],
            ),
            "ALLOW_CHANGE_DEADLINE" => "N",
            "RESPONSIBLE_ID" => $responsible_id,
            "CREATED_BY" => 72,
            "UF_CRM_TASK" => array(
                0 => "D_".$deal["ID"],
                1 => "C_".$deal["CONTACT_ID"],
            ),
        );
        if (!is_null($parent_id)) $task["PARENT_ID"] = $parent_id;
        return $task;
    }

    /**
     * Функция собирает массив чеклист-заголовков из массива айтемов
     *
     * @param $items
     * @return array|null
     */
    public static function constructChecklist($items){
            if (is_null($items)) return null;
            $result = array();
            foreach ($items as $item){
                $result[] = BitrixHelper::formatChecklistItem($item);
            }
            return $result;
        }


    /**
     * Функция форматирует заголовок айтема-продукта
     *
     * @param $item
     * @return string
     */
    public static function formatChecklistItem($item) {
            return $item['PRODUCT_NAME']." - ".$item['QUANTITY']." ".$item['MEASURE_NAME'];
        }

    /**
     * Функция возвращает список товаров, прикрепленных к сделке, добавляя к каждому соответствующую ему категорию
     *
     * @param $deal_id
     * @param $access_token
     * @return array|mixed|null
     */
    public static function getAttachedProductrows($deal_id, $access_token) {
            $attached_productrows = BitrixHelper::callMethod("crm.deal.productrows.get", array("id" => $deal_id, "auth" => $access_token));
            $attached_productrows = isset($attached_productrows["result"]) ? $attached_productrows["result"] : NULL ;
            $attached_productrows = BitrixHelper::addCategoryToProductrow($attached_productrows, $access_token);
            return $attached_productrows;
        }

    /**
     * Функция возвращает компанию как ассоциативный массив
     *
     * @param $companyId
     * @param null $auth
     * @return null
     */
    public static function getCompany($companyId, $auth = null) {
        $auth = is_null($auth) ? get_access_data(true) : $auth;
        $params = array(
            "id" => $companyId,
            "auth" => $auth
        );
        $data = BitrixHelper::callMethod("crm.company.get", $params);
        if (isset($data["result"])) {
            return $data["result"];
        }
        return null;
    }

    /**
     * Функция возвращает контакт как ассоциативный массив
     *
     * @param $contactId
     * @param null $auth
     * @return null
     */
    public static function getContact($contactId, $auth = null) {
        $auth = is_null($auth) ? get_access_data(true) : $auth;
        $params = array(
            "id" => $contactId,
            "auth" => $auth
        );
        $data = BitrixHelper::callMethod("crm.contact.get", $params);
        if (isset($data["result"])) {
            return $data["result"];
        }
        return null;
    }

    /**
     * Функция возвращает сделку как ассоциативный массив
     *
     * @param $dealId
     * @param null $auth
     * @return null
     */
    public static function getDeal($dealId, $auth = null) {
        $auth = is_null($auth) ? get_access_data(true) : $auth;
        $params = array(
            "id" => $dealId,
            "auth" => $auth
        );
        $data = BitrixHelper::callMethod("crm.deal.get", $params);
        if (isset($data["result"])) {
            return $data["result"];
        }
        return null;
    }

    /**
     * Функция возвращает лид как ассоциативный массив
     *
     * @param $leadId
     * @param null $auth
     * @return null
     */
    public static function getLead($leadId, $auth = null) {
        $auth = is_null($auth) ? get_access_data(true) : $auth;
        $params = array(
            "id" => $leadId,
            "auth" => $auth
        );
        $data = BitrixHelper::callMethod("crm.lead.get", $params);
        if (isset($data["result"])) return $data["result"];
        return null;
    }

    /**
     * Поиск контакта по номеру телефона
     *
     * @param $phone
     * @param null $auth
     * @return array
    */
    public static function searchContact($phone, $auth = null){

        $auth = is_null($auth) ? get_access_data(true) : $auth;
        $phone_array = array(
            $phone,
            substr_replace($phone, "+7", 0, 1),
            substr_replace($phone, "7", 0, 1),
            substr_replace($phone, "", 0, 1),
        );
        foreach ($phone_array as $key => $value) {
            $data = BitrixHelper::callMethod("crm.contact.list", array(
                    "select[0]" => "ID",
                    "select[1]" => "NAME",
                    "select[2]" => "LAST_NAME",
                    "select[3]" => "PHONE",
                    "filter[PHONE]" => $phone,
                    "auth" => $auth
                )
            );
            if (isset($data["total"]) && $data["total"] > 0) break;
        }
        $data = isset($data["result"]) ? $data["result"] : $data;
        return $data;
    }

    /**
     * Возвращает список компаний в CRM. Если передан UserId, то вернет список компаний конкретного ответственного
     *
     * @param null $filterByUser - id юзера в Б24
     * @param null $auth
     * @return array
     */
    public static function getCompanies($filterByUser = null, $auth = null){
        $auth = is_null($auth) ? get_access_data(true) : $auth;

        $params = array(
            "select[0]" => "ID",
            "select[1]" => "TITLE",
            "select[2]" => "PHONE",
            //"filter[ASSIGNED_BY_ID]" => $userId,
            "auth" => $auth
        );
        if (!is_null($filterByUser)) $params["filter[ASSIGNED_BY_ID]"] = $filterByUser;
        $data = BitrixHelper::callMethod("crm.company.list", $params );


        $data = isset($data["result"]) ? $data["result"] : $data;
        return $data;
    }

    /**
     * Форматирование телефона. Убирает спец-символы
     *
     * @param $phone
     * @return mixed|null
     */
    public static function formatPhone($phone){
        if (is_null($phone)) return null;
        $phone = str_replace("+7", "8", $phone);
        $phone = str_replace("(", "", $phone);
        $phone = str_replace(")", "", $phone);
        $phone = str_replace(" ", "", $phone);
        $phone = str_replace("-", "", $phone);
        return $phone;
    }


    /**
     * Возвращает список контактов, которые относятся к конкретной компании
     *
     * @param $companyId
     * @param null $token
     * @return array
     */
    public static function getContactsOfTheCompany($companyId, $token = null){
        $token = is_null($token) ? get_access_data(true) : $token;
        $params = array(
            "select[0]" => "ID",
            "select[1]" => "NAME",
            "select[2]" => "LAST_NAME",
            "select[3]" => "PHONE",
            "filter[COMPANY_ID]" => $companyId,
            "auth" => $token
        );
        $data = BitrixHelper::callMethod("crm.contact.list", $params );
        $data = isset($data["result"]) ? $data["result"] : $data;
        return $data;
    }

    public static function constructTimestamp($date, $time = null, $format = null){
        try {
            $parsed_date = new DateTime($date);
            if (!is_null($time) && gettype($time) == "string"){
                $split = explode(":", $time);
                $hours = intval($split[0]);
                $minutes = intval($split[1]);
                $parsed_date->setTime($hours, $minutes, 0);
            }
            if (is_null($format)) return $parsed_date;
            return $parsed_date->getTimestamp();

        } catch (Exception $ex){
            process_error(var_export($ex, true));
        }
        return null;
    }



    public static function constructDatetime($date, $time = null, $format = null){
        try {
            $parsed_date = new DateTime($date);
            if (!is_null($time) && gettype($time) == "string"){
                $split = explode(":", $time);
                $hours = intval($split[0]);
                $minutes = intval($split[1]);
                $parsed_date->setTime($hours, $minutes, 0);
            }
            if (is_null($format)) return $parsed_date;
            $timestamp = $parsed_date->getTimestamp();
            return date($format, $timestamp);

        } catch (Exception $ex){
            process_error(var_export($ex, true));
        }
        return null;
    }



    /**
     * Получение источника сущности: контакта, лида
     *
     * @param $id
     * @param $type
     * @param null $auth
     * @return null|string
     */
    public static function getInstanceSource($id, $type, $auth = null) {
        $auth = is_null($auth) ? get_access_data(true) : $auth;
        switch ($type){
            case "contact":
                $instance = BitrixHelper::getContact($id, $auth);
                break;

            case "lead":
                $instance = BitrixHelper::getLead($id, $auth);
                break;
            default:
                $instance = null;
                break;
        }
        if (is_null($instance)) return null;
        $source = $instance["SOURCE_ID"];
        switch($source) {
            //
            case "SELF": $sourceText = "Свой контакт";  break;
            case "CALL": $sourceText = "звонок";  break;
            case "16": $sourceText = "Без источника";  break;
            case "EMAIL": $sourceText = "Электронная почта";  break;
            case "17": $sourceText = "Были гостями";  break;
            case "WEB": $sourceText = "Веб-сайт";  break;
            //-------------------------
            case "18": $sourceText = "Сами справляли";  break;
            case "ADVERTISING": $sourceText = "Реклама";  break;
            case "15": $sourceText = "Интернет";  break;
            case "RECOMMENDATION": $sourceText = "По рекомендации";  break;
            case "11": $sourceText = "Лэндинг next-birthday.kz";  break;
            //------------------------------------------
            case "TRADE_SHOW": $sourceText = "Выставка";  break;
            case "1": $sourceText = "Facebook";  break;
            case "WEBFORM": $sourceText = "CRM-форма";  break;
            case "13": $sourceText = "VK.com (Вконтакте)";  break;
            case "8": $sourceText = "Instagram";  break;
            case "14": $sourceText = "Pandaland.kz";  break;
            case "9": $sourceText = "Блоггеры";  break;
            case "10": $sourceText = "Алматы сегодня / Давай сходим";  break; // PARTNER

            case "PARTNER": $sourceText = "Клиент из базы CPC";  break;
            case "19": $sourceText = "Конкурс 'ДР мечты'";  break;
            case "20": $sourceText = "Форма-приглашение";  break;

            case "12": $sourceText = "База по ДР";  break;
            case "OTHER": $sourceText = "Другое";  break;

            default:
                $sourceText = $source;
                break;
        }

        return $sourceText;

    }

    /**
     *  Возвращает залогиненного пользователя либо null, если нет авторизации
     * @param $token
     * @return array|null
     */
    public static function getCurrentUser($token) {
        $data = call("user.current", array("auth" => $token));
        return  isset($data["result"]) ? $data["result"] : NULL;
    }

    /**
     * Высылает уведомление указанному пользователю.
     *
     * @param $user_id
     * @param $text
     * @param $access_token
     * @param string $type
     * @return array
     */
    public static function notifyUser($user_id, $text, $access_token, $type = "USER"){
        $result = call("im.notify", array(
                "to" => $user_id,
                "message" => $text,
                "type" => $type,
                "auth" => $access_token,
            )
        );
        if (isset($result["error"])) process_error("Ошибка отправки уведомления: ".var_export($result));
        return $result;
    }
}