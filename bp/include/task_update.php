<?php
require($_SERVER['DOCUMENT_ROOT'] ."/include/config.php");
require($_SERVER['DOCUMENT_ROOT'] ."/bp/include/bp_config.php");

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