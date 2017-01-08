<?php

    /*
    
    require_once $_SERVER["DOCUMENT_ROOT"] . "/include/constants.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/Helpers/ApplicationHelperClass.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/Helpers/BitrixHelperClass.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/Helpers/OrderHelperClass.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/Helpers/SmsApiClass.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/Helpers/MysqlHelper.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/Model/UserClass.php";*/

    require_once "/var/www/b24.next.kz/include/constants.php";
    require_once "/var/www/b24.next.kz/Helpers/ApplicationHelperClass.php";
    require_once "/var/www/b24.next.kz/Helpers/BitrixHelperClass.php";
    require_once "/var/www/b24.next.kz/Helpers/OrderHelperClass.php";
    require_once "/var/www/b24.next.kz/Helpers/SmsApiClass.php";
    require_once "/var/www/b24.next.kz/Helpers/MysqlHelper.php";
    require_once "/var/www/b24.next.kz/Model/UserClass.php";


    /**
     * Производит перенаправление пользователя на заданный адрес
     *
     * @param string $url адрес
     */
    function redirect($url)
    {
    	Header("HTTP 302 Found");
    	Header("Location: ".$url);
    	die();
    }

    /**
     * Отправляет запрос на гугл-скрипт-сервер
     *
     * @param $data
     * @param string $url - по дефолту стоит адрес моего гугл-скрипт-сервера
     * @return array
     */
    function queryGoogleScript($data, $url = "https://script.google.com/macros/s/AKfycbxjyTPPbRdVZ-QJKcWLFyITXIeQ1GwI7fAi0FgATQ0PsoGKAdM/exec"){
        return query("POST", $url, $data);
    }

    /**
     * Совершает запрос с заданными данными по заданному адресу. В ответ ожидается JSON
     *
     * @param string $method GET|POST - тип запроса
     * @param string $url адрес
     * @param array|null $data параметры запроса: Post или Get аргументы
     *
     * @return array
     */
    function query($method, $url, $data = null)
    {
    	$query_data = "";

        try {
            $curlOptions = array(
                CURLOPT_RETURNTRANSFER => true
            );

            if($method == "POST")
            {
                $curlOptions[CURLOPT_POST] = true;
                $curlOptions[CURLOPT_POSTFIELDS] = http_build_query($data);
            }
            elseif(!empty($data))
            {
                $url .= strpos($url, "?") > 0 ? "&" : "?";
                $url .= http_build_query($data);
            }

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt_array($curl, $curlOptions);
            $result = curl_exec($curl);
            return json_decode($result, 1);

        } catch (Exception $ex){
            $text = $ex->getMessage(). "\nFile: ".$ex->getFile()."\nLine: ".$ex->getLine();
            ApplicationHelper::processError($text);
        }
        return null;

    }

/**
 * Осуществляет поиск элемента в массиве, возвращает true, если найден, и false - если нет
 * @param $searchable - искомый элемент
 * @param $array - массив
 * @param null $field_name - если передаваемый массив - ассоциативный, то необходимо поле, с которым идет сравнение
 * @return false|true
 */
    function search_in_array($searchable, $array, $field_name = null) {

        $searchable = gettype($searchable) ? trim($searchable) : $searchable;
        foreach ($array as $value) {

            $item = !is_null($field_name) ? $value[$field_name] : $value;
            $item = gettype($item) =="string" ? trim($item) : $item;
            
            if($item == $searchable) return true;
    	}
        return false;
    }
