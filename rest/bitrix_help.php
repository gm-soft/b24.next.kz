<?php



    /**
     * Добавляет к каждому элементу прикрепленных товаров к сделке категорию соответствующего ему товара по PRODUCT_ID
     *
     * @param $attached_productrows - прикрепленные товары к сделке
     * @param $access_token - токен авторизации
     * @return mixed
     */
    function add_category_to_productrow($attached_productrows, $access_token) {
        //Получаю список товаров, которые дсотупны из прикрепленных по полю PRODUCT_ID,
        //так как польвоталельское поле КАТЕГОРИИ не доступно в списочных товарах
        $attached_products = request_products_by_productrows($attached_productrows, $access_token);

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
    function create_or_update_checklist($products, $task_id, $access_token) {
        $current_checklist = get_task_checklist($task_id, $access_token);
        $checklist = construct_checklist($products);
        $commands = array();
        if (count($current_checklist) > 0)  {
            $checklist = get_array_difference($checklist, $current_checklist, null, "TITLE");
        }
        if (count($checklist) > 0) {
            //$commands[] = "task.checklistitem.add?TASKID=".$task_id."&FIELDS[TITLE]=--- Добавлен/обновлен список ".format_atom_date(time()+(60*60*6))." ---&FIELDS[IS_COMPLETE]=N";
            foreach ($checklist as $item) {
                $commands[] = "task.checklistitem.add?TASKID=".$task_id."&FIELDS[TITLE]=".$item."&FIELDS[IS_COMPLETE]=N";
            }
        }

        $batch_result = batch_commands($commands, $access_token);
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
    function create_checklist_commands($checklist, $task_id) {
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
            // TOGGLED_BY
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
    function filter_products_by_category($category, $products) {
        $result = array();
        foreach ($products as $prod) {
            if ($prod["PROPERTY_180"]["value"] != $category) continue;
            $result[] = $prod;
        }
        return $result;
    }

    function extract_checklist_item($title){
        $result = trim(substr($title, 0, strpos($title, " - "))) ;
        return $result;
    }


    function get_name_and_count($checklist_item) {

        if (!isset($checklist_item["TITLE"])) return null;
        $product_name = extract_checklist_item($checklist_item["TITLE"]);
        $count = substr($checklist_item["TITLE"], strpos($checklist_item["TITLE"], " - ") + 2);
        $count = intval(substr($count, strpos($count, " ")));

        return array(
            "product_name" => $product_name,
            "count" => $count
        );
    }


    function clear_checklist($task_id, $access_token) {
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
    function get_updated_checklist($current_checklist, $products, $task_id) {
        $new_checklist = array();
        $changed_items = array();
        $removed_items = array();
        $subtracted_items = array();
        $added_items = array();
        $debug_content = "";

        $debug_content = "Current checklist: ".var_export($current_checklist, true);
        if (!is_null($current_checklist)) {
            foreach ($current_checklist as $key => $value) {

                $name_and_count = get_name_and_count($value);
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
                    $value["TITLE"] = format_checklist_item($product);
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
                "TITLE" => format_checklist_item($value),
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
        $checklist_commands = create_checklist_commands($new_checklist, /*array_merge($new_checklist, $removed_items),*/ $task_id);
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
    function get_array_difference($first, $second, $first_field_name = null, $second_field_name = null){
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
    function create_or_update_task($deal, $task_id_field, $task_id, $task_array, $access_token){

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
    function get_task_checklist($task_id, $access_token){
        //$result = call("task.checklistitem.getlist", array(0=> $task_id, "auth" => $access_token, ));
        //$result = query("POST", "http://next.bitrix24.kz/rest/task.checklistitem.getlist", array(0=> $task_id, "auth" => $access_token, ));
        //next.bitrix24.kz/rest/task.checklistitem.getlist?auth=ey71gcw561pagvghzlxi6iacldsus0p3&0=1044
        
        //$result = query("GET", "http://next.bitrix24.kz/rest/task.checklistitem.getlist?0=".$task_id."&auth=".$access_token);
        $result = call("task.checklistitem.getlist" , array(0 => $task_id, "auth" => $access_token));
        $result = isset($result["result"]) ? $result["result"] : $result;
        return $result;
    }

    /**
     * Запрос и получение списка товаров(product) по прикрепленным товарам к сделке(productrows)
     * @param $productrows - список прикрепленных товаров
     * @param $access_token - токен авторизации
     * @return array
     */
    function request_products_by_productrows($productrows, $access_token){
        $commands = array();
        for ($i = 0; $i < count($productrows); $i++) {
            $commands[] = "crm.product.get?id=".$productrows[$i]["PRODUCT_ID"];
        }

        $result = batch_commands($commands, $access_token);
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
    function construct_task($deal, $title, $responsible_id, $parent_id = null){
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

    function construct_checklist($items){
        if (is_null($items)) return null;
        $result = array();
        foreach ($items as $item){
            $result[] = format_checklist_item($item);
        }
        return $result;
    }


    function format_checklist_item($item) {
        return $item['PRODUCT_NAME']." - ".$item['QUANTITY']." ".$item['MEASURE_NAME'];
    }

    function get_attached_productrows($deal_id, $access_token) {
        $attached_productrows = call("crm.deal.productrows.get", array("id" => $deal_id, "auth" => $access_token));
        $attached_productrows = isset($attached_productrows["result"]) ? $attached_productrows["result"] : NULL ;
        $attached_productrows = add_category_to_productrow($attached_productrows, $access_token);
        return $attached_productrows;
    }
