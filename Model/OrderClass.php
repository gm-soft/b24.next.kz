<?php

require_once ("../include/helper.php");

class Order
{
    public $hash;


    function __construct($id)
    {
        $hash = array();
        $hash["Id"] = $id;
        $hash["Status"] = null;
        $hash["DealId"] = null;
        $hash["ContactId"] = null;
        //--------------------------------------------
        $hash["ClientName"] = null;
        $hash["KidName"] = null;
        $hash["Phone"] = null;
        $hash["Center"] = null;

        $hash["DateOfEvent"] = null;

        $hash["Date"] = null;
        $hash["DateAtom"] = null;

        $hash["TotalCost"] = 0;
        $hash["UserId"] = null;
        $hash["User"] = null;
        $hash["FullPriceType"] = null;
        //------------------------------------
        $event = array(
            'Event' => null,
            'Zone' => null,
            'Date' => $hash["DateOfEvent"],
            'StartTime' => null,
            'Duration' => null,
            'GuestCount' => null,
            'Cost' => null
        );
        $hash["Event"] = $event;
        //----------------------------
        $clientInfo = array(
            'Id' => $id,
            'ClientName' => null,
            'KidName' => null,
            'Code' => null,
            'Status' => null,
            'Date' => null,
        );
        $hash["VerifyInfo"] = $clientInfo;
        //----------------------------
        $financeInfo = array (
            'Id' => $id,
            'Remainder' =>  0,
            'Payed' => null,
            'PaymentsView' => null,
            'Payments' => array(
                "paymentValue" => null,
                "receiptDate" => null,
                "receiptNumber" => 1
            ),
            'TotalDiscount' => null,
            'Increase' => 0,
            'IncreaseComment' => null,
            'Discount' => null,
            'DiscountComment' => null,
            'LoyaltyCode' => null,
            'LoyaltyDiscount' => 0,
            'AgentCode' => null,
            'AgentDiscount' => 0,
        );
        if ($financeInfo["DiscountComment"] == "") $financeInfo["Discount"] = 0;
        $hash["FinanceInfo"] = $financeInfo;
        //--------------------------------------------
        $hash["BanquetInfo"] = null;
        /*
        var banquetInfo = {
            'BanquetId' : hash[10],
            'Comment' : "",
            'TranscriptStr' : "",
            'Items' : null,
            'Cost' : 0,
            'Cake' : "",
            'Candybar' : "",
            'Pinata' : "",
            'Date' : null,
            "BarItems" : null,
            "BarItemsCost" : 0
        };
        */
        $hash["OptionalInfo"] = null;
        /*
        var optionalInfo = {
          'OptionalId' : hash[11],
          'Comment' : "",
          'TranscriptStr' : "",
          'Items' : null,
          'Cost' : 0,
          'Entertainment' : "",
          'Cameraman' : "",
          'TableBooking' : "",
          'MonitorSaver' : "",
          'Date' : null
        };
        */
        $hash["Comment"] = null;
        //--------------------------------
        $hash["CreatedAt"] = str_replace(" ", "T", date("Y-m-d H:i:s+06:00", time() + 3600*6));
        $hash["UpdatedAt"] = $hash["CreatedAt"];
        $hash["TaskId"] = null;

        $hash["ContactSource"] = null;
        $hash["LeadSource"] = null;

    }

    public static function fromDatabase(array $hash){

    }


}