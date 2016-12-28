<script>

    var filterSelect = $('#filterSelect');
    var dealSelect = $('#dealSelect');
    var smtButton = $("#submit-btn");
    //---------------------------


    $("#confirmAction").change(function() {
        if(this.checked) {
            smtButton.removeClass('disabled');
            smtButton.prop("disabled", false);
        } else {
            smtButton.addClass('disabled');
            smtButton.prop("disabled", true);
        }
    });

    dealSelect.change(function(){
        $('#confirmAction').prop('checked', false);
        //---------------
        dealSelect.prop("disabled", true);
        var title = $(this).find('option:selected').text();
        console.log(title);
        var orderId = title.substr(2, title.indexOf(" ") - 2);
        console.log(orderId);
        var output = $('#orderInfo');
        PrintFinanceOrderAjax(orderId, output);
        smtButton.addClass('disabled');
    });


    filterSelect.change(function(){


        var filterValue = filterSelect.val();

        filterSelect.prop("disabled", "disabled");
        dealSelect.prop("disabled", "disabled");

        $.ajax({
            type: 'POST',
            url: 'https://b24.next.kz/rest/bitrix.php',

            // UF_CRM_1468830187
            data: {
                'action': 'center.deals.get',
                'center' : filterValue,
                //"period" : 14
            },
            success: function(res){

                //var deals = res["result"];
                var total = res["total"];
                var openOrders = res["openOrders"];
                var closedOrders = res["closedOrders"];

                dealSelect.find('optgroup').remove().end();
                dealSelect.find('option').remove().end();
                dealSelect.append('<option value="">Выберите заказ</option>').val('');
                //----------------------------------
                var optGroup;
                var deal;
                var option;
                //----------------
                optGroup = $('<optgroup></optgroup>').attr("label", "Заказ подтвержден");
                for (var i = 0; i < openOrders.length; i++){

                    deal = openOrders[i];
                    option = $("<option></option>").attr("value", deal["ID"]).text(deal["TITLE"]);
                    optGroup.append(option);
                }
                dealSelect.append(optGroup);
                //---------------------------------
                optGroup = $('<optgroup></optgroup>').attr("label", "Аренда проведена");
                for (var i = 0; i < closedOrders.length; i++){

                    deal = closedOrders[i];
                    option = $("<option></option>").attr("value", deal["ID"]).text(deal["TITLE"]);
                    optGroup.append(option);
                }
                dealSelect.append(optGroup);
                //---------------------------------

                filterSelect.prop("disabled", false);
                dealSelect.prop("disabled", false);

            }
        });
    });

    function PrintFinanceOrderAjax(id, outputNode){
        $.ajax({
            type: 'POST',
            url: 'https://b24.next.kz/rest/bitrix.php',
            data: {
                'action': 'order.get.google',
                'id' : id,
            },
            success: function(res){

                dealSelect.prop("disabled", false);
                console.log(res);
                if (res["result"] != false){
                    var order = res["result"];
                    var html = "";
                    html += "ID"+order["Id"]+" "+order["Event"]["Event"]+" ("+order["Event"]["Zone"]+")<b></b><br>";
                    html += "<dl class=\"dl-horizontal\">";
                    html += "<dt>Статус заказа</dt><dd><i>"+order["Status"]+"</i><dd>";
                    html += "<dt>Дата</dt><dd><i>"+order["DateOfEvent"]+"</i><dd>";
                    html += "<dt>Сделка в битрикс24</dt><dd><a href='https://next.bitrix24.kz/crm/deal/show/"+order["DealId"]+"/'>Сделка №"+order["DealId"]+"</a><dd>";
                    html += "<dt>Полная стоимость:</dt><dd>"+order["TotalCost"]+"</dd>";
                    html += "<dt>Оплачено: </dt><dd>"+order["FinanceInfo"]["Payed"]+"</dd>";
                    html += "<dt>Остаток по оплате: </dt><dd>"+order["FinanceInfo"]["Remainder"]+"</dd>";
                    html += "</dl>";
                    outputNode.html(html);
                } else {
                    outputNode.html("Возникла какая-то ошибка. Заказ не найден");
                }




            }
        });
    }

</script>