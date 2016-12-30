
<div class="row">
    <div class="col-sm-6">
        <fieldset>
            <legend>Поиск компании</legend>
            <div class="form-group">

                <div class="col-sm-3">
                    <div class="btn-group">
                        <button id="companyTitle" type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">Компания: <span class="caret"></span></button>
                        <ul class="dropdown-menu" role="menu">
                            <li><a id="btnMyCompanies"  href="#">Мои компании</a></li>
                            <li><a id="btnAllCompanies" href="#">Все компании</a></li>
                        </ul>
                    </div>
                </div>



                <div class="col-sm-9">
                    <select class="form-control" id="companySelect" name="companySelect" required>
                        <option value="">Выберите компанию</option>
                        <?php
                        $i = 0;
                        foreach ($companies as $key => $value) {

                            $option =
                                "<option value='".$value["ID"]."'>".$value["TITLE"].
                                " [ID ".$value["ID"]."]</option>";
                            echo $option;
                        }
                        ?>
                    </select>
                </div>

            </div>
            <div id="companySearchResult" class="alert alert-info"></div>

        </fieldset>
    </div>


    <div class="col-sm-6">
        <fieldset>
            <legend>Поиск контакта</legend>
            <div class="form-group">

                <div class="col-sm-7">
                    <select class="form-control" id="contactSelect" name="contactSelect" required>
                        <option value="">Нет данных</option>
                    </select>
                </div>

                <div class="col-sm-5">
                    <div class="input-group">
                        <input type="tel" class="form-control" id="contactPhone" name="contactPhone"
                               pattern="^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$" required placeholder="Найти контакт по номеру">
                        <div class="input-group-btn">
                            <button id="searchByPhone" class="btn btn-default" type="button">
                                Найти
                            </button>
                        </div>
                    </div>
                </div>


            </div>

            <div id="contactSearchResult" class="alert alert-info">
        </fieldset>
    </div>
</div>







<script>

    var contactSelect = $('#contactSelect');
    var companySelect = $("#companySelect");
    var phoneSearchBtn = $('#searchByPhone');
    var searchResult = $('#searchResult');
    var companyInputTitle = $('#companyTitle');
    var contactInputTitle = $('#contactTitle');

    var companySearchResult = $('#companySearchResult');
    var contactSearchResult = $('#contactSearchResult');


    contactSelect.select2();
    companySelect.select2();

    $('#btnMyCompanies').on("click", function(){
        LoadCompaniesByAjax(<?= $userId ?>);
    });

    $('#btnAllCompanies').on("click", function(){
        LoadCompaniesByAjax();
    });

    phoneSearchBtn.on("click", function(){
        var phone = $('#contactPhone').val();
        if (phone == "") return;
        LoadContactsByAjax(phone, "phone");
    });

    companySelect.change(function(){
        var value = $(this).val();
        var text =  $(this).find("option:selected").text();
        companySearchResult.html("<b>Компания:</b><br><a href='https://next.bitrix24.kz/crm/company/show/"+value+"/'>"+text+"</a>");
        LoadContactsByAjax(value, "company");
    });

    contactSelect.change(function(){
        var value = $(this).val();
        var text =  $(this).find("option:selected").text();
        console.log(text);
        //contactSearchResult.html("<b>Контакт:</b><br><a href='https://next.bitrix24.kz/crm/contact/show/"+value+"/'>"+text+"</a>");
    });

    function LoadCompaniesByAjax(byUserId){

        byUserId = typeof byUserId !== 'undefined' ? byUserId : null;
        var url = "https://b24.next.kz/rest/bitrix.php";
        var parameters = {
            "action" : "companies.get"
        };
        if (byUserId != null) {
            parameters["byUser"] = byUserId;
            companyInputTitle.html("Мои компании <span class='caret'></span>");
        } else {
            companyInputTitle.html("Все компании <span class='caret'></span>");
        }
        contactSelect.prop("disabled", true);
        companySelect.prop("disabled", true);
        $.ajax({
            type: 'POST',
            url: url,
            data: parameters,
            success: function(res){

                //var deals = res["result"];
                var total = res["total"];
                var instances = res["result"];

                companySelect.find('option').remove().end();

                //----------------------------------
                if (total > 0){
                    for (var i = 0; i < instances.length; i++){
                        var instance = instances[i];
                        var option = $("<option></option>").attr("value", instance["ID"]).text(instance["TITLE"]);
                        companySelect.append(option);
                    }
                } else {
                    companySelect.append('<option value="">Не найдено компаний</option>').val('');
                }

                //---------------------------------
                contactSelect.prop("disabled", false);
                companySelect.prop("disabled", false);

            }
        });

    }

    function LoadContactsByAjax(filter, type){

        filter = typeof filter !== 'undefined' ? filter : null;
        type = typeof type !== 'undefined' ? type : null;

        var url = "https://b24.next.kz/rest/bitrix.php";
        var parameters = {
            "action" : "contacts.get"
        };

        if (filter != null && type != null){
            if (type == "phone") {
                parameters["byPhone"] = filter;
                contactInputTitle.html("Поиск по телефону");
            }
            if (type == "company") {
                parameters["byCompany"] = filter;
                contactInputTitle.html("Контакты в компании");
            }
        } else {
            contactInputTitle.html("Результат");
        }

        contactSelect.prop("disabled", true);
        companySelect.prop("disabled", true);
        $.ajax({
            type: 'POST',
            url: url,
            data: parameters,
            success: function(res){

                //var deals = res["result"];
                var total = res["total"];
                var instances = res["result"];
                contactSelect.find('option').remove().end();

                if (total > 0){
                    for (var i = 0; i < instances.length; i++){

                        var instance = instances[i];
                        var option = $("<option></option>").attr("value", instance["ID"]).text(instance["NAME"]+" "+instance["LAST_NAME"] + " ("+instance["PHONE"][0]["VALUE"]+")");
                        contactSelect.append(option);
                    }
                    //contactSearchResult.html("<b>Контакт:</b><br><a href='https://next.bitrix24.kz/crm/contact/show/"+value+"/'>"+text+"</a>");
                } else {
                    contactSelect.append('<option value="">Контакт в компании не найден</option>').val('');
                }
                //---------------------------------
                contactSelect.prop("disabled", false);
                companySelect.prop("disabled", false);

            }
        });

    }

</script>