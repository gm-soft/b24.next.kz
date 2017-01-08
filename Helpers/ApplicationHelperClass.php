<?php

/**
 * Created by PhpStorm.
 * User: Next
 * Date: 08.01.2017
 * Time: 11:43
 */
class ApplicationHelper
{

    /**
     * Форматирует вывод даты, оформленной в формате atom
     * @param string $date - Дата в формате atom
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
        $access_source = read_from_file(AUTH_FILENAME);
        if ($access_source == "null") return null;
        $access_data = object_as_json($access_source);

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

}