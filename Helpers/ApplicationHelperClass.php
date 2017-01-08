<?php

/**
 * Created by PhpStorm.
 * User: Next
 * Date: 08.01.2017
 * Time: 11:43
 */
class ApplicationHelper
{

    function formatCurrentTime($format = "d.m.y - H:i") {

        $now = new DateTime();
        switch ($format) {
            case "atom":
                $result = date("c", $now->getTimestamp());
                break;

            default:
                $result = date("d.m.y - H:i", $now->getTimestamp());
                break;
        }
        return $result;


        /*
        $time = time() + (60*60*6);
        $result = null;
        switch ($format) {
            case "atom":
                $result = date("c", $time);
                break;

            default:
                $result = date("d.m.y - H:i", $time);
                break;
        }
        return $result;*/
    }


    /**
     * Форматирует вывод даты, оформленной в формате atom
     * @param string|DateTime $date - Дата в формате atom
     * @param string $format
     * @return false|string
     */
    public static function formatDate($date, $format = "d.m.y - H:i") {

        $date = gettype($date) == "string" ? new Datetime($date) : $date;
        if ($date == false) return $date;


        $timestamp = $date->getTimestamp();

        $result = date($format, $timestamp);
        return $result;
    }

    /**
     * Возвращает массив авторизационных данных, содержащийся в файле
     *
     * @param bool $token_only
     * @return array|null
     */
    public static function readAccessData($token_only = false) {
        $access_source = ApplicationHelper::readFromFile(AUTH_FILENAME);
        if ($access_source == "null") return null;
        $access_data = ApplicationHelper::toJson($access_source);

        if ($token_only == true) {
            $access_data = isset($access_data["access_token"]) ? $access_data["access_token"] : $access_data;
        }

        return $access_data;
    }



    /**
     * Возвращает набор параметров для обновения токена авторизации
     * @param $refresh_token
     * @return array
     */
    public static function constructRefreshParams($refresh_token){
        /*
        https://oauth.bitrix.info/oauth/token/?
        grant_type=refresh_token
        &client_id=app.573ad8a0346747.09223434
        &client_secret=LJSl0lNB76B5YY6u0YVQ3AW0DrVADcRTwVr4y99PXU1BWQybWK
        &refresh_token=nfhxkzk3gvrg375wl7u7xex9awz6o3k8
        */
        $params = array(
            "grant_type" => "refresh_token",
            "client_id" => CLIENT_ID,
            "client_secret" => CLIENT_SECRET,
            "redirect_uri" => REDIRECT_URI,
            "scope" => SCOPE,
            "refresh_token" => $refresh_token,
        );
        return $params;

    }

    /**
     * Конструирует первый запрос для получения авторизации
     * @param $code - передаваемый код авторизации от сервера
     * @return array - возвращает массив значений
     */
    public static function constructFirstAuthParams($code) {
        $params = array(
            "grant_type" => "authorization_code",
            "client_id" => CLIENT_ID,
            "client_secret" => CLIENT_SECRET,
            "redirect_uri" => REDIRECT_URI,
            "scope" => SCOPE,
            "code" => $code,
        );
        return $params;
    }


    /**
     * Поиск элемента в массиве. Возвращает первый элемент или NULL, если отсутствует
     * @param $searchable - искомое значение
     * @param $field - поле массива, по которому будет производиться поиск
     * @param $array - массив, в котором осуществляется поиск
     * @return array|null
     */
    public static function searchItemInArray($searchable, $field, $array) {
        $result = null;
        foreach ($array as $key => $value) {
            if($array[$key][$field] != $searchable) continue;
            $result = $array[$key];
            break;
        }
        return $result;
    }

    /**
     * Осуществляет поиск элемента в массиве, возвращает true, если найден, и false - если нет. Legacy code
     * @param $searchable - искомый элемент
     * @param $array - массив
     * @param null $field_name - если передаваемый массив - ассоциативный, то необходимо поле, с которым идет сравнение
     * @return false|true
     */
    public static function search_in_array($searchable, $array, $field_name = null) {

        $searchable = gettype($searchable) ? trim($searchable) : $searchable;
        foreach ($array as $value) {

            $item = !is_null($field_name) ? $value[$field_name] : $value;
            $item = gettype($item) =="string" ? trim($item) : $item;

            if($item == $searchable) return true;
        }
        return false;
    }

    /**
     * Фильтрация массива по полю и значению этого поля. Возвращает массив отобранных элементов
     * @param $value - искомое значение
     * @param $field - поле массива, по которому будет производиться поиск
     * @param $array - массив, в котором осуществляется поиск
     * @return array|null
     */
    public static function filterByField($value, $field, $array) {
        $result = null;
        foreach ($array as $item) {
            if ($item[$field] != $value) continue;
            $result = is_null($result) ? array() : $result;
            $result[] = $item;
        }
        return $result;

    }

    public static function reverseArray($array) {
        $result = array();
        for($i = count($array) - 1; $i >= 0;$i--) {
            $result[] = $array[$i];
        }
        return $result;
    }


    /**
     * @param $exception
     */
    public static function processException($exception){

    }

    public static function processError($error_text) {
        $filename = $_SERVER["DOCUMENT_ROOT"]."/log/errors.log";
        $text = "[".self::formatCurrentTime("atom")."] ".$error_text."\n";
        error_log($text, 3, $filename);
    }

    /**
     *
     * @param $event_text
     * @param string $filename
     * @return bool
     */
    public static function log($event_text, $filename = "/log/process_events.log") {
        if ($event_text == "") return false;

        $filename = $_SERVER["DOCUMENT_ROOT"].$filename;
        $content = "[".self::formatCurrentTime("atom")."] ".$event_text."\n";
        $append = "APPEND";
        return ApplicationHelper::writeToFile($filename, $content, $append);
    }

    public static function debug($something) {
        $filename = $_SERVER["DOCUMENT_ROOT"]."/log/debug.log";
        $content = "[".self::formatCurrentTime("atom")."]".$something."\n";
        return ApplicationHelper::writeToFile($filename, $content);
    }

    /**
     * Записывает содержимое в файл. Возвращает результат записи
     * @param $filename - имя файла, в который будет осуществляться запись
     * @param $content - содержимое
     * @param null $append
     * @return bool
     */
    public static function writeToFile($filename, $content, $append = null){
        try {
            if (is_null($append)) file_put_contents($filename,  $content);
            else file_put_contents($filename,  $content, FILE_APPEND);
            return true;
        } catch(Exception $ex){
            ApplicationHelper::processError($ex);
        }
        return false;
    }

    /**
     * Читает содержимое файла. Возвращает содержимое либо NULL, если возникла какая-то ошибка
     * @param $filename - имя файла
     * @return null|string
     */
    public static function readFromFile($filename){
        try {
            $content = file_get_contents($filename);
            return $content;
        } catch(Exception $ex) {
            ApplicationHelper::processError($ex);
        }
        return NULL;
    }

    /**
     * Преобразовывает строку в формате json в объект-json. Возвратит исходный объект в случае ошибки
     * @param $content - исходная строка
     * @return array
     */
    public static function toJson($content){

        try {
            $data = json_decode($content);
            $array = (array)$data;
            foreach($array as $key => &$field){
                if(is_object($field))$field = self::objectToarray($field);
            }
            return $array;
        } catch(Exception $ex){

        }
        return $content;
    }

    public static function objectToArray($d){
        if (is_object($d)) {
            $d = get_object_vars($d);
        }
        return is_array($d) ? array_map(__FUNCTION__, $d) :  $d;
    }

}