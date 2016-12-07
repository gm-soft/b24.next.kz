<?php
    require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
    require($_SERVER["DOCUMENT_ROOT"] . "/include/help.php");
    require ($_SERVER["DOCUMENT_ROOT"]."/Helpers/BitrixHelperClass.php");

    log_event("control.php: IP: ".$_SERVER['REMOTE_ADDR'].". Browser: ".$_SERVER['HTTP_USER_AGENT'].". \$_REQUEST[\"method\"]=".$_REQUEST["method"]);
    $method = isset($_REQUEST["method"]) ? $_REQUEST["method"] : null;
    $id = isset($_REQUEST["id"]) ? $_REQUEST["id"] : null;

    if (is_null($method) /*|| is_null($id)*/){
        $response = ["error" => "No method received", "request" => var_export($_REQUEST, true)];
        header('Content-Type: application/json');
        echo json_encode($response);
        die();
    }
    $response = [
        "result" => null,
        "method" => $method
    ];

    $authData = BitrixHelper::getAuth();
    if (is_null($authData) || time() > ($authData["ts"] + 3600)) {
        $response = ["error" => "Auth data is null or expired"];
        header('Content-Type: application/json');
        echo json_encode($response);
        die();
    }
    //-------------------------------------
    $auth = $authData["access_token"];


    switch ($method){
        case "user.fields":
            $data = BitrixHelper::callMethod("user.fields", array(
                "id" => $id,
                "auth" => $auth)
            );
            break;

        case "department.fields":
            $data = BitrixHelper::callMethod("department.fields", array(
                    "id" => $id,
                    "auth" => $auth)
            );
            break;

        case "department.get":
            $data = BitrixHelper::callMethod("department.get", array(
                    "id" => $id,
                    "auth" => $auth)
            );
            break;

        case "user.get":
            $data = BitrixHelper::callMethod("user.get", array(
                    "id" => $id,
                    "auth" => $auth)
            );
            break;

        case 'crm.deal.productrows.get':
            $data = BitrixHelper::callMethod("crm.deal.productrows.get", array(
                    "id" => $id,
                    "auth" => $auth)
            );
            break;

        case "crm.contact.company.items.get":
            $data = BitrixHelper::callMethod("crm.contact.company.items.get", array(
                    "id" => $id,
                    "auth" => $auth)
            );
            break;

        case 'crm.deal.get':
            $data = BitrixHelper::callMethod("crm.deal.get", array(
                    "id" => $id,
                    "auth" => $auth)
            );
            break;

        case "crm.deal.fields":
            $data = BitrixHelper::callMethod("crm.deal.fields", array(
                    "auth" => $auth)
            );
            break;

        case 'crm.deal.userfield.list':
            $data = BitrixHelper::callMethod("crm.deal.userfield.list", array(
                    "auth" => $auth)
            );
            break;

        case 'crm.product.list':
            $data = BitrixHelper::callMethod("crm.product.list", array(
                    "auth" => $auth)
            );
            break;

        case 'task.item.getdata':
            $data = BitrixHelper::callMethod("task.item.getdata", array("auth" => $auth, 0 => $id));
            break;

        case 'user.current':
            $data = BitrixHelper::callMethod("user.current", array("auth" => $auth));
            break;

        case 'event.bind':
            $event = isset($request["event_name"]) ? $request["event_name"] : "";
            $data = BitrixHelper::callMethod("event.bind", array(
                "auth" => $auth,
                "EVENT" => $event,
                "HANDLER" => "http://b24.next.kz/event.php",
            ));

            break;

        case 'event.get':
            $data = BitrixHelper::callMethod("event.get", array(
                    "auth" => $auth)
            );
            break;

        case 'event.unbind':
            $data = BitrixHelper::callMethod("event.unbind", array(
                "auth" => $auth,
                'EVENT' => 'ONCRMLEADADD',
                'HANDLER' => REDIRECT_URI . "event.php"
            ));
            break;

        case 'event.list':
            $data = BitrixHelper::callMethod("events", array(
                    "auth" => $auth)
            );
            break;
        case 'bizproc.activity.add':
            $data = BitrixHelper::callMethod("bizproc.activity.add", array(
                "auth" => $auth,
                "CODE" => "client_by_deal_id",
                "HANDLER" => "http://b24.next.kz/bp/client_by_deal_id.php",
                "NAME" => "Возврат данных о контакте по ID сделки",
                "PROPERTIES" => array(
                    "dealID" => array(
                        "NAME" => "ID сделки",
                        "TYPE" => "string",
                        "Multiple" => "N"
                    )
                ),
                "RETURN_PROPERTIES" => array(
                    "clientName" => array(
                        "NAME" => "Фамилия и Имя контакта",
                        "TYPE" => "string",
                        "Multiple" => "N"
                    ),
                    "clientPhone" => array(
                        "NAME" => "Телефон контакта",
                        "TYPE" => "string",
                        "Multiple" => "N"
                    ),
                    "clientBirthday" => array(
                        "NAME" => "День рождения контакта",
                        "TYPE" => "date",
                        "Multiple" => "N"
                    )
                )
            ));
            break;

        case 'entity.get':
            $data = BitrixHelper::callMethod("entity.get", array(
                    "auth" => $auth)
            );
            break;

        case 'bizproc.activity.delete':
            $data = BitrixHelper::callMethod("bizproc.activity.delete", array(
                "auth" => $auth,
                "CODE" => "client_by_deal_id"
            ));
            break;

        case '':
            $data = null;
            break;

        default:
            $data = BitrixHelper::callMethod($method, array(
                    "id" => $id,
                    "auth" => $auth)
            );
            break;
    }
    $response["result"] = $data;
    if (isset($_REQUEST["type"]) && $_REQUEST["type"] == "var_export"){
        echo var_export($response, true);
        die();
    }
    header('Content-Type: application/json');
    echo json_encode($response);

