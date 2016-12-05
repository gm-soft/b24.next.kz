<?php
require($_SERVER['DOCUMENT_ROOT'] ."/include/config.php");
require($_SERVER['DOCUMENT_ROOT'] ."/bp/include/bp_config.php");
$start_date = $_REQUEST["properties"]["startDate"];
$end_date = $_REQUEST["properties"]["endDate"];

/*Получение массива товаров из сделки*/
$productrows = call($domain, "crm.deal.productrows.get", array(
	"id" => $deal_id,
	"auth" => $auth)
);
$prod_arr = objectToArray($productrows);

/*Получение массива данных о сделке*/
$deal_info = call($domain, "crm.deal.get", array(
	"id" => $deal_id,
	"auth" => $auth)
);
$deal_arr = objectToArray($deal_info);

/*Форматирование даты проведения сделки и задание ответственного администратора*/
$banquete_date = date("d.m - H:i",strtotime($deal_arr["result"]["UF_CRM_1467690712"])+(60*60*6));
$responsible_admin = $deal_arr["result"]["UF_CRM_1474604947"];

/*Передаём в переменную центр на основании данных сделки*/
switch ($deal_arr["result"]["UF_CRM_1468830187"]) {
   case '128':
      $center = "NEXT Aport";
      break;

   case '130':
      $center = "NEXT Esentai";
      break;

   case '132':
      $center = "NEXT Promenade";
      break;

   default:
      $center = "Не указан";
      break;
}


/*Создание и наполнение массивов данных по позициям из сделки*/
foreach ($prod_arr['result'] as $key => $value) {
   $prod_name = $prod_arr['result'][$key]['PRODUCT_NAME'];
   if (search_in_array($prod_name, $banquete_file)) {
      $banquete_txt[] = $prod_arr['result'][$key]['PRODUCT_NAME']." - ".$prod_arr['result'][$key]['QUANTITY']." ".$prod_arr['result'][$key]['MEASURE_NAME'];
   }
   if (search_in_array($prod_name, $souvenirs_file)) {
      $souvenirs_txt[] = $prod_arr['result'][$key]['PRODUCT_NAME']." - ".$prod_arr['result'][$key]['QUANTITY']." ".$prod_arr['result'][$key]['MEASURE_NAME'];
   }
   if (search_in_array($prod_name, $design_file)) {
      $design_txt[] = $prod_arr['result'][$key]['PRODUCT_NAME']." - ".$prod_arr['result'][$key]['QUANTITY']." ".$prod_arr['result'][$key]['MEASURE_NAME'];
   }
   if (search_in_array($prod_name, $zones_file)) {
      $zone = $prod_arr['result'][$key]['PRODUCT_NAME'];
   }
}

/*Формирование описания для задач*/
$description_txt = "[b]Информация по мероприятию:[/b]\nДата и время проведения: [i]".$banquete_date."[/i]\nЦентр: [i]".$center."[/i]\nЗона: [i]".$zone."[/i]";

/*Создание основной задачи*/
$main_task_id = call($domain, "task.item.add", array( 
      "auth" => $auth, 
      0 => array(
         "TITLE" => "Подготовка аренды для сделки ".$deal_arr["result"]["TITLE"],
         "DESCRIPTION" => $description_txt,
         "START_DATE_PLAN" => $start_date,
         "END_DATE_PLAN" => $end_date,
         "AUDITORS" => array(
            0 => $deal_arr["result"]["ASSIGNED_BY_ID"]),
         "ALLOW_CHANGE_DEADLINE" => "N",
         "RESPONSIBLE_ID" => $responsible_admin,
         "CREATED_BY" => 72,
         "UF_CRM_TASK" => array(
            0 => "D_".$deal_id)
      )
   )
);

/*Создание задачи с чеклистом для пищеблока*/
if (isset($banquete_txt)) {
   $banquete_task_id = call($domain, "task.item.add", array( 
         "auth" => $auth, 
         0 => array(
            "TITLE" => "Подготовка фуршета для сделки ".$deal_arr["result"]["TITLE"],
            "DESCRIPTION" => $description_txt,
            "START_DATE_PLAN" => $start_date,
            "END_DATE_PLAN" => $end_date,
            "AUDITORS" => array(
               0 => $deal_arr["result"]["ASSIGNED_BY_ID"]),
            "ALLOW_CHANGE_DEADLINE" => "N",
            "PARENT_ID" => $main_task_id["result"],
            "RESPONSIBLE_ID" => $coordinator_id,
            "CREATED_BY" => 72,
            "UF_CRM_TASK" => array(
               0 => "D_".$deal_id)
         )
      )
   );

   foreach ($banquete_txt as $key => $value) {

      call($domain, "task.checklistitem.add", array(
         "auth" => $auth,
         "TASKID" => $banquete_task_id["result"],
         "FIELDS" => array(
            "TITLE" => $value,
            "IS_COMPLETE" => "N"
            )
      ));
   }
}

/*Создание задачи с чеклистом для оформителя*/
if (isset($design_txt)) {
   $design_task_id = call($domain, "task.item.add", array( 
         "auth" => $auth, 
         0 => array(
            "TITLE" => "Подготовка оформления для сделки ".$deal_arr["result"]["TITLE"],
            "DESCRIPTION" => $description_txt,
            "START_DATE_PLAN" => $start_date,
            "END_DATE_PLAN" => $end_date,
            "AUDITORS" => array(
               0 => $deal_arr["result"]["ASSIGNED_BY_ID"]),
            "ALLOW_CHANGE_DEADLINE" => "N",
            "PARENT_ID" => $main_task_id["result"],
            "RESPONSIBLE_ID" => $designer_id,
            "CREATED_BY" => 72,
            "UF_CRM_TASK" => array(
               0 => "D_".$deal_id)
         )
      )
   );

   foreach ($design_txt as $key => $value) {

      call($domain, "task.checklistitem.add", array(
         "auth" => $auth,
         "TASKID" => $design_task_id["result"],
         "FIELDS" => array(
            "TITLE" => $value,
            "IS_COMPLETE" => "N"
            )
      ));
   }
}