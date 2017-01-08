<?php


/**
 * Форматирует вывод даты, оформленной в формате atom
 * @param string $date - Дата в формате atom
 * @param string $format
 * @return false|string
 */
    function formatDate($date, $format = "d.m.y - H:i") {

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
    function get_access_data($token_only = false) {
        $access_source = read_from_file(AUTH_FILENAME);
        if ($access_source == "null") return null;
        $access_data = object_as_json($access_source);
        
        if ($token_only == true) {
            $access_data = isset($access_data["access_token"]) ? $access_data["access_token"] : $access_data;
        }

        return $access_data;
    }




