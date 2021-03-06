<?php

	require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");

	$access_data = ApplicationHelper::readAccessData();

	$result = false;
	$action = $_REQUEST["action"];
	$json_array = array(
		"result" => $result,
		"action" => $action
	);
	$ip = $_SERVER['REMOTE_ADDR'];
    $browser = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "unknown";
    ApplicationHelper::log("control.php: IP: ".$ip.". Browser: ".$browser.". \$_REQUEST[\"action\"]=".$action);

	switch ($action) {
		case 'refresh':
			if (is_null($access_data)) {

				$text = "Refresh requested, but Access data is null";
                //ApplicationHelper::log($text, "/log/auth.log");
				break;
			}
			$params = ApplicationHelper::constructRefreshParams($access_data["refresh_token"]);
			$query_data = query("GET", PROTOCOL."://".PORTAL_ADDRESS."/oauth/token/", $params);

			$text_to_log = "";

			if(isset($query_data["access_token"]))
			{
				$query_data["ts"] = time();
				$json = json_encode($query_data);
				$json_array["result"] = ApplicationHelper::writeToFile(AUTH_FILENAME, $json);
				
				$access_token = $query_data["access_token"];
            	$current_user = BitrixHelper::getCurrentUser($access_token);
            	$_SESSION["user_name"] = $current_user["EMAIL"];
            	$text_to_log = "Auth refreshed successfully";
			} else {
				$text_to_log = "Refresh requested, but went wrong. Refresh response: ".var_export($query_data, true);
			}
            ApplicationHelper::log($text_to_log, "/log/auth.log");
			break;

		case "getAccessToken":
			$content = ApplicationHelper::readFromFile(AUTH_FILENAME);
			$json_array = ApplicationHelper::toJson($content);
			break;

		case "checkdate":


		    $dateString = "2016-11-25";
            $time = "11:00";
            $time = null;
            $datetime = BitrixHelper::constructDatetime($dateString, $time);
            //log_debug(var_export($datetime, true));
            $json_array["result"] = $datetime;
            $json_array["isHoliday"] = OrderHelper::isDateHoliday($datetime);
            $strToCons = gettype($datetime) != "string" ?  str_replace(" ", "T", ApplicationHelper::formatDate($datetime, "Y-m-d H:i+03:00")) : $datetime;
            $json_array["atom_format"] = $strToCons;
			break;
		
		default:
			// code...
			break;
	}
	
	
	header('Content-Type: application/json');
	echo json_encode($json_array);