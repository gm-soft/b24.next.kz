
<?php

/**
* Осуществляет обработку пользовательского запроса к приложению
* @param array $request осуществляемый реквест к странице
* @param array $access_data данные авторизации, необходим только токен, по сути
* 
* @return array
*/
function process_user_request($request, $access_data) {
//
    $method = isset($request["method"]) ? $request["method"] : "";
    $id = isset($request["deal_id"]) ? $request["deal_id"] : "";
    $access_token = $access_data["access_token"];

    switch($method)
    {
        case "user.fields":
            $data = call("user.fields", array(
                    "id" => $id,
                    "auth" => $access_token)
            );
            break;

        case "department.fields":
            $data = call("department.fields", array(
                    "id" => $id,
                    "auth" => $access_token)
            );
            break;

        case "department.get":
            $data = call("department.get", array(
                    "id" => $id,
                    "auth" => $access_token)
            );
            break;

        case "user.get":
            $data = call("user.get", array(
                    "id" => $id,
                    "auth" => $access_token)
            );
            break;

        case 'crm.deal.productrows.get':
            $data = call("crm.deal.productrows.get", array(
                    "id" => $id,
                    "auth" => $access_token)
            );
            break;

        case "crm.contact.company.items.get":
            $data = call("crm.contact.company.items.get", array(
                    "id" => $id,
                    "auth" => $access_token)
            );
            break;

        case 'crm.deal.get':
            $data = call("crm.deal.get", array(
                    "id" => $id,
                    "auth" => $access_token)
            );
            break;

        case "crm.deal.fields":
            $data = call("crm.deal.fields", array(
                    "auth" => $access_token)
            );
        break;

        case 'crm.deal.userfield.list':
            $data = call("crm.deal.userfield.list", array(
                    "auth" => $access_token)
            );
            break;

        case 'crm.product.list':
            $data = call("crm.product.list", array(
                    "auth" => $access_token)
            );
            break;

        case 'task.item.getdata':
            $data = call("task.item.getdata", array("auth" => $access_token, 0 => $id));
            break;

        case 'user.current':
            $data = call("user.current", array("auth" => $access_token));
            break;

        case 'event.bind':
            $event = isset($request["event_name"]) ? $request["event_name"] : "";
            $data = call("event.bind", array(
                "auth" => $access_token,
                "EVENT" => $event,
                "HANDLER" => "http://b24.next.kz/event.php",
            ));

            break;

        case 'event.get':
            $data = call("event.get", array(
                    "auth" => $access_token)
            );
            break;

        case 'event.unbind':
            $data = call("event.unbind", array(
                "auth" => $access_token,
                'EVENT' => 'ONCRMLEADADD',
                'HANDLER' => REDIRECT_URI . "event.php"
            ));
            break;

        case 'event.list':
            $data = call("events", array(
                    "auth" => $access_token)
            );
            break;
        case 'bizproc.activity.add':
            $data = call("bizproc.activity.add", array(
                "auth" => $access_token,
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
            $data = call("entity.get", array(
                "auth" => $access_token)
            );
        break;

        case 'bizproc.activity.delete':
            $data = call("bizproc.activity.delete", array(
                "auth" => $access_token,
                "CODE" => "client_by_deal_id"
            ));
        break;

        case '':
            $data = null;
        break;

        default:
            $data = call($method, array(
                    "id" => $id,
                    "auth" => $access_token)
            );
            break;
    }
    return $data;
}


?>