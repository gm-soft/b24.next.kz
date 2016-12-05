<?php
    //if (!isset($_SESSION)) session_start();
    define('CLIENT_ID', 'local.57e244c4c901e8.62049767');
    define('CLIENT_SECRET', 'D1sOv6TROrreN5Dj2XLWjnXwZP50h6ik4nM1rU1nHxYhJPmjcc');
    define('PATH', '/server/index.php');
    define('REDIRECT_URI', 'http://b24.next.kz/server/index.php');
    define('SCOPE', 'crm,log,user,task,tasks_extended,im,bizproc,entity,department,calendar');
    define('PROTOCOL', "https");
    define('AUTH_FILENAME', $_SERVER["DOCUMENT_ROOT"]."/rest/auth.js");
    define('PORTAL_ADDRESS', "next.bitrix24.kz");
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
        //log_debug($result);
    	return json_decode($result, 1);
    }

    /**
     * Вызов метода REST.
     *
     * @param string $method вызываемый метод
     * @param array $params параметры вызова метода
     *
     * @return array
     */
    function call($method, $params)
    {
    	return query("POST", PROTOCOL."://".PORTAL_ADDRESS."/rest/".$method, $params);
    }

    /**
     * Вызов методов REST способом BATCH.
     *
     * @param string $commands массив GET-запросов
     * @param string $access_token токен авторизации
     *
     * @return array
     */
    function batch($commands, $access_token){
        $batch_params = array("auth" => $access_token, "halt" => 0, "cmd" => $commands);
        $call_result = call("batch", $batch_params);
        return $call_result;
    }
    
    /**
     * @param $commands
     * @param $access_token
     * @return array|null
     */
    function batch_commands($commands, $access_token){
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
            $batch_result = batch($cmd, $access_token);
            $data = isset($batch_result["result"]) ? $batch_result["result"] : $batch_result;
            $result = array_merge($result, $data);
        }
        return count($result) > 0 ? $result : null;
    }


    function objectToArray($d){
        if (is_object($d)) {
            $d = get_object_vars($d);
        }
        return is_array($d) ? array_map(__FUNCTION__, $d) :  $d;
    }

    function phpist_get_array_by_key ($array, $key){
        $ret = array();
        foreach ($array as $v){
            $ret[] = $v[$key];
        }
        return $ret;
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

    
    /**
    * Преобразовывает строку в формате json в объект-json. Возвратит исходный объект в случае ошибки
    * @param $content - исходная строка
    * @return array
    */
    function object_as_json($content){

        try {
            $data = json_decode($content);
            $array = (array)$data;
            foreach($array as $key => &$field){
                if(is_object($field))$field = $this->objectToarray($field);
            }
            return $array;
        } catch(Exception $ex){

        }
        return $content;
    }

    function format_current_time($format = null) {

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
        return $result;
    }

/**
 * @param $exception
 */
    function process_exception($exception){

    }

    function process_error($error_text) {
        $filename = $_SERVER["DOCUMENT_ROOT"]."/log/errors.log";
        $text = "[".format_current_time("atom")."] ".$error_text."\n";
        error_log($text, 3, $filename);
    }
    
    /**
     * 
     * 
     */
    function log_event($event_text, $filename = "/log/process_events.log") {
        if ($event_text == "") return false;

        $filename = $_SERVER["DOCUMENT_ROOT"].$filename;
        $content = "[".format_current_time("atom")."] ".$event_text."\n";
        $append = "APPEND";
        return write_to_file($filename, $content, $append);
    }

    function log_debug($something) {
        $filename = $_SERVER["DOCUMENT_ROOT"]."/log/debug.log";
        $content = "[".format_current_time("atom")."]".$something."\n";
        return write_to_file($filename, $content);
    }

/**
 * Записывает содержимое в файл. Возвращает результат записи
 * @param $filename - имя файла, в который будет осуществляться запись
 * @param $content - содержимое
 * @param null $append
 * @return bool
 */
    function write_to_file($filename, $content, $append = null){
        try {
            if (is_null($append)) file_put_contents($filename,  $content);
            else file_put_contents($filename,  $content, FILE_APPEND);
            return true;
        } catch(Exception $ex){ process_exception($ex); }
        return false;
    }

    /**
     * Читает содержимое файла. Возвращает содержимое либо NULL, если возникла какая-то ошибка
     * @param $filename - имя файла
     * @return null|string
     */
    function read_from_file($filename){
        try {
            $content = file_get_contents($filename);
            return $content;
        } catch(Exception $ex){ process_exception($ex); }
        return NULL;
    }

    function xml_encode($mixed, $root_name = "result") {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><'.$root_name.'/>');

        /*foreach ($mixed as $key => $value){
            $node = $xml->addChild($key, $value);
        }*/
        $convert_result = array_walk($mixed, array($xml, 'addChild'));

        return $convert_result ? $xml->asXML() : null;
    }