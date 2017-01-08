<?php
require($_SERVER['DOCUMENT_ROOT'] ."/include/config.php");
require($_SERVER['DOCUMENT_ROOT'] ."/bp/include/bp_config.php");

$deal_info = BitrixHelper::callMethod($domain, "crm.deal.get", array(
	"id" => $deal_id,
	"auth" => $auth)
);

$contact_info = BitrixHelper::callMethod($domain, "crm.contact.get", array(
	"id" => $deal_info["result"]["CONTACT_ID"],
	"auth" => $auth)
);

$bizproc_event = BitrixHelper::callMethod($domain, "bizproc.event.send", array(
	"EVENT_TOKEN" => $event_token,
	"RETURN_VALUES" => array(
		"clientName" => $contact_info["result"]["NAME"]." ".$contact_info["result"]["LAST_NAME"],
		"clientPhone" => $contact_info["result"]["PHONE"][0]["VALUE"],
		"clientBirthday" => $contact_info["result"]["BIRTHDATE"]
		),
	"auth" => $auth
	)
);