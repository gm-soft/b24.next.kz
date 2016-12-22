

<form id="form" class="form-horizontal" method="post" action="school.php">
    <input type="hidden" name="actionPerformed" value="order_saved">
    <input type="hidden" name="action" value="<?= $actionRequest ?>">
    <input type="hidden" name="authId" value="<?= $_REQUEST["authId"] ?>">
    <input type="hidden" name="adminToken" value="<?= $_REQUEST["adminToken"] ?>">

    <input type="hidden" name="contactId" value="<?= $_REQUEST["contactId"]?>">
    <input type="hidden" name="companyId" value="<?= $_REQUEST["companyId"]?>">

    <input type="hidden" name="dealId" value="<?= $_REQUEST["dealId"] ?>">
    <input type="hidden" name="orderId" value="<?= $_REQUEST["orderId"] ?>">

    <input type="hidden" name="contactName" value="<?= $contact["NAME"]." ".$contact["LAST_NAME"] ?>">
    <input type="hidden" name="contactPhone" value="<?= $contact["PHONE"][0]["VALUE"]?>">
    <input type="hidden" name="companyName" value="<?= $company["TITLE"]?>">

    <input type="hidden" name="userId" value="<?= $_REQUEST["userId"] ?>">
    <input type="hidden" name="userFullName" value="<?= $_REQUEST["userFullName"] ?>">


    <div class="form-group">
        <label class="control-label col-sm-3">Компания</label>
        <div class="col-sm-9">
            <?= $company["TITLE"]?> <a href="https://next.bitrix24.kz/crm/company/show/<?= $company["ID"]?>/">ID<?= $company["ID"]?></a> (<?= $company["PHONE"][0]["VALUE"]?>)
        </div>
    </div>

    <div class="form-group">
        <label class=" control-label col-sm-3">Контакт</label>
        <div class="col-sm-9">
            Контакт <a href="https://next.bitrix24.kz/crm/contact/show/<?= $contact["ID"]?>/">ID<?= $contact["ID"]?></a> (<?= $contact["PHONE"][0]["VALUE"]?>)
        </div>
    </div>


    <div class="form-group">
        <label class="control-label col-sm-3" for="pack">Выберите пакет и центр:</label>
        <div class="col-sm-2">
            <div class="input-group">
                <select class="form-control" id="pack" name="pack" required>
                    <?php
                    if (!isset($order["Event"]["Pack"])){
                        ?>
                        <option value="">Выберите из списка</option>
                        <option value="basePack">Базовый</option>
                        <option value="standardPack">Стандартный</option>
                        <option value="allInclusive">Все включено</option>
                    <?php } else {
                        $selectedOption = $order["Event"]["Pack"];
                        ?>
                        <option value="basePack" <?= $selectedOption == "basePack" ? "selected" : "" ?>>Базовый</option>
                        <option value="standardPack" <?= $selectedOption == "standardPack" ? "selected" : "" ?>>Стандартный</option>
                        <option value="allInclusive" <?= $selectedOption == "allInclusive" ? "selected" : "" ?>>Все включено</option>
                        <!--option value="newYear" <?= $selectedOption == "newYear" ? "selected" : "" ?>>Новогодний</option-->

                    <?php } ?>


                </select>
                <span class="input-group-addon"><span class="glyphicon glyphicon-building"></span></span>
            </div>
        </div>

        <div class="col-sm-3">
            <div class="input-group">
                <span class="input-group-addon">Центр</span>
                <select class="form-control" id="center" name="center" required>

                    <?php
                    if (!isset($order["Center"])){
                        ?>
                        <option value="">Выберите из списка</option>
                        <option value="nextEse">NEXT Esentai</option>
                        <option value="nextApo">NEXT Aport</option>
                        <option value="nextPro">NEXT Promenade</option>
                    <?php } else {
                        $selectedOption = $order["Center"];
                        ?>
                        <option value="nextEse" <?= $selectedOption == "nextEse" ? "selected" : "" ?>>NEXT Esentai</option>
                        <option value="nextApo" <?= $selectedOption == "nextApo" ? "selected" : "" ?>>NEXT Aport</option>
                        <option value="nextPro" <?= $selectedOption == "nextPro" ? "selected" : "" ?>>NEXT Promenade</option>

                    <?php } ?>
                </select>

            </div>
        </div>

        <div class="col-sm-4">
            <div class="input-group">
                <span class="input-group-addon">Спец-название</span>
                <select class="form-control" id="packNameCode" name="packNameCode" required>

                    <?php
                    if (!isset($order["Event"]["PackNameCode"])){
                        ?>
                        <option value="">Выберите из списка</option>
                        <option value="without">Без спец-названия</option>
                        <option value="newYear">Новогодний</option>
                        <option value="holidays">Каникулярный</option>
                    <?php } else {
                        $selectedOption = $order["Event"]["PackNameCode"];
                        ?>
                        <option value="without" <?= $selectedOption == "without" ? "selected" : "" ?>>Без спец-названия</option>
                        <option value="newYear" <?= $selectedOption == "newYear" ? "selected" : "" ?>>Новогодний</option>
                        <option value="holidays" <?= $selectedOption == "holidays" ? "selected" : "" ?>>Каникулярный</option>

                    <?php } ?>
                </select>

            </div>
        </div>
    </div>


<?php

    if (isset($order["Status"])){

        switch ($order["Status"]){
            case "Заказ подтвержден":
                $value = "initiated";
                break;

            case "Аренда проведена":
                $value = "conducted";
                break;

            case "Сделка закрыта":
                $value = "closed";
                break;

            case "Аренда отменена":
                $value = "canceled";
                break;

            default:
                $value = "initiated";
                break;
        }
        ?>
    <div class="form-group">
        <label class="control-label col-sm-3" for="status">Статус заказа:</label>
        <div class="col-sm-9">
            <div class="input-group">
                <select class="form-control" id="status" name="status" required>
                    <option value="initiated" <?= $value == "initiated" ? "selected" : "" ?>>Заказ подтвержден</option>
                    <option value="conducted" <?= $value == "conducted" ? "selected" : "" ?>>Аренда проведена</option>
                    <option value="closed" <?= $value == "closed" ? "selected" : "" ?>>Сделка закрыта</option>
                    <option value="canceled" <?= $value == "canceled" ? "selected" : "" ?>>Аренда отменена</option>
                </select>
                <span class="input-group-addon"><i class="glyphicon glyphicons-building"></i></span>
            </div>
        </div>
    </div>
    <?php
    } else {
        ?>
    <input type="hidden" name="status" value="initiated">

<?php
    }

?>


    <hr>
    <div class="form-group">
        <label class="control-label col-sm-3" for="pupilCount">Количество детей:</label>
        <div class="col-sm-4">
            <div class="input-group">
                
                <?php $value = isset($order["Event"]["PupilCount"]) ? $order["Event"]["PupilCount"] : "" ?>
                <input type="number" step="1" min="0" class="form-control" id="pupilCount" name="pupilCount" required placeholder="Количество детей (учеников)" value="<?= $value ?>">
                <span class="input-group-addon"></span>
            </div>
        </div>

        <div class="col-sm-5">
            <div class="input-group">
                <span class="input-group-addon">Возраст детей</span>
                <?php $value = isset($order["Event"]["PupilAge"]) ? $order["Event"]["PupilAge"] : "" ?>
                <input type="text" class="form-control" id="pupilAge" name="pupilAge" required placeholder="Пример: 6-7 класс, 12-14 лет" value="<?= $value ?>">

            </div>
        </div>

    </div>

    <!--div class="form-group">
        <label class="control-label col-sm-3" for="pupilAge">Возраст детей:</label>
        <div class="col-sm-9">
            <div class="input-group">
                <?php $value = isset($order["Event"]["PupilAge"]) ? $order["Event"]["PupilAge"] : "" ?>
                <input type="text" class="form-control" id="pupilAge" name="pupilAge" required placeholder="Пример: 6-7 класс, 12-14 лет" value="<?= $value ?>">
                <span class="input-group-addon"><i class="glyphicon glyphicon-fire"></i></span>
            </div>
        </div>
    </div-->

    <hr>

    <div class="form-group">
        <label class="control-label col-sm-3" for="teacherCount">Количество учителей:</label>
        <div class="col-sm-9">
            <div class="input-group">
                <?php $value = isset($order["Event"]["TeacherCount"]) ? $order["Event"]["TeacherCount"] : "" ?>
                <input type="number" step="1" min="0" class="form-control" id="teacherCount" name="teacherCount" required placeholder="Кол-во учителей и/или родителей" value="<?= $value ?>">
                <span class="input-group-addon"><i class="glyphicon glyphicons-parents"></i></span>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-3" for="packagePrice">Цена пакета:</label>
        <div class="col-sm-9">
            <div class="input-group">
                <?php $value = isset($order["Event"]["PackPrice"]) ? $order["Event"]["PackPrice"] : "" ?>
                <input type="number" step="0.1" min="0" class="form-control" id="packagePrice" name="packagePrice" required placeholder="Стоимость пакета развлечений на одного ребенка" value="<?= $value ?>">
                <span class="input-group-addon"><i class="glyphicon glyphicons-fire"></i></span>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-3" for="subject">Программа:</label>
        <div class="col-sm-9">
            <div class="input-group">
                <?php $value = isset($order["Event"]["Subject"]) ? $order["Event"]["Subject"] : "" ?>
                <input type="text" class="form-control" id="subject" name="subject" required placeholder="Тема урока/мероприятия (космос, география и тд)" value="<?= $value ?>">
                <span class="input-group-addon"><i class="glyphicon glyphicons-book"></i></span>
            </div>
        </div>
    </div>
    <hr>
    <div class="form-group">
        <label class="control-label col-sm-3" for="date">Дата:</label>
        <div class="col-sm-3">
            <div class="input-group">

                <?php
                if (isset($order["DateAtom"])) {
                    $date = strtotime($order["DateAtom"]);
                    $date = $date + 6 * 3600;
                    $dateValue = date("Y-m-d", $date);
                    $timeValue = date("H:m", $date);
                } else {
                    $dateValue = "";
                    $timeValue = "";
                }

                    //echo $date. " " .$value;
                ?>
                <span class="input-group-addon">Дата:</i></span>
                <input type="date" class="form-control" id="date" name="date" required placeholder="Выберите дату" value="<?= $dateValue ?>">
            </div>
        </div>

        <div class="col-sm-3">
            <div class="input-group">
                <span class="input-group-addon">Начало в:</span>
                <input type="time" class="form-control" id="time" name="time" required
                       placeholder="Время" value="<?= $timeValue ?>">

            </div>
        </div>

        <div class="col-sm-3">
            <div class="input-group">
                <span class="input-group-addon">Длительность:</span>
                <?php $value = isset($order["Event"]["Duration"]) ? $order["Event"]["Duration"] : "3" ?>
                <input type="number" step="1" min="0" class="form-control" id="duration" name="duration" required placeholder="Продолжительность мероприятия" value="<?= $value ?>">
            </div>
        </div>


    </div>
    <hr>
    <div class="form-group">
        <label class="control-label col-sm-3" for="foodPackCount">Кол-во фуд-пакетов:</label>
        <div class="col-sm-9">
            <div id="food-input" class="input-group">
                <?php
                if (isset($order["Event"]["FoodPackCount"]) && $order["Event"]["FoodPackCount"] > 0) {
                   ?>
                    <input type="number" step="1" min="0" class="form-control" id="foodPackCount" name="foodPackCount"
                           required placeholder="Количество фуд-пакетов: " value="<?= $order["Event"]["FoodPackCount"] ?>">

                    <input type="hidden" name="hasFood" value="yes">
                    <span class="input-group-addon">Кол-во пакетов</span>
                    <?php
                }
                else {
                    ?>

                    <input type="text" class="form-control" value="Количество фуд-пакетов: 0" disabled>
                    <span class="input-group-addon">Кол-во пакетов</span>

                    <input type="hidden" name="foodPackCount" value="0">
                    <input type="hidden" name="hasFood" value="yes">

                <?php
                }

                ?>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-3" for="transferCost">Оплата водителю:</label>


        <div class="col-sm-9">
            <div id="transfer-input" class="input-group">

                <?php
                if (isset($order["Event"]["TransferCost"]) && $order["Event"]["TransferCost"] > 0) {
                    ?>
                    <input type="number" step="1" min="0" class="form-control" id="transferCost" name="transferCost"
                           required placeholder="Стоимость трансфера: " value="<?= $order["Event"]["TransferCost"] ?>">

                    <input type="hidden" name="hasTransfer" value="yes">
                    <span class="input-group-addon">Кол-во пакетов</span>
                    <?php
                }
                else {
                    ?>

                    <input type="text" class="form-control" value="Стоимость трансфера: 0" disabled>
                    <span class="input-group-addon">Кол-во пакетов</span>

                    <input type="hidden" name="transferCost" value="0">
                    <input type="hidden" name="hasTransfer" value="yes">

                    <?php
                }

                ?>
            </div>
        </div>
    </div>
    <hr>

    <div class="form-group">
        <label class="control-label col-sm-3" for="bribePercent">Сумма учителю (за одного ребенка):</label>
        <div class="col-sm-9">
            <div class="input-group">
                <?php $value = isset($order["Event"]["TeacherBribePercent"]) ? $order["Event"]["TeacherBribePercent"] : "" ?>
                <input type="number" step="1" min="0" class="form-control" id="bribePercent" name="bribePercent" required placeholder="Сумма учителю за одного ребенка" value="<?= $value ?>">
                <span class="input-group-addon"><i class="glyphicon glyphicons-money"></i></span>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-3" for="discount">Скидка:</label>
        <div class="col-sm-3">
            <div class="input-group">
                <?php $value = isset($order["FinanceInfo"]["Discount"]) ? $order["FinanceInfo"]["Discount"] : "0" ?>
                <input type="number" step="1" min="0" class="form-control" id="discount" name="discount" value="<?= $value?>" placeholder="Сумма скидки">
                <span class="input-group-addon"><i class="glyphicon glyphicons-heart-empty"></i></span>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="input-group">
                <?php $value = isset($order["FinanceInfo"]["DiscountComment"]) ? $order["FinanceInfo"]["DiscountComment"] : "" ?>
                <input type="text" class="form-control" id="discountComment" name="discountComment" placeholder="Комментарий к скидке" maxlength="150" value="<?= $value?>">
                <span class="input-group-addon"><i class="glyphicon glyphicons-heart-empty"></i></span>
            </div>

        </div>

    </div>

    <div class="form-group">
        <label class="control-label col-sm-3" for="comment">Комментарий к заказу:</label>
        <div class="col-sm-9">
            <?php $value = isset($order["Event"]["Comment"]) ? $order["Event"]["Comment"] : "" ?>
            <textarea class="form-control" id="comment" name="comment" ><?= $value ?></textarea>
        </div>
    </div>
    <hr>
    <div class="form-group">
        <div class="col-sm-offset-3">
            <a href="#" id="back" class="btn btn-default">Вернуться</a>
            <button type="submit" id="submit-btn" class="btn btn-primary">Рассчитать стоимость</button>
        </div>
    </div>
</form>

<script>
    $('#pack').change(function(){

        var transferHtml = "";
        var foodHtml = "";
        var value = $(this).val();
        switch (value){
            case "standardPack":
                transferHtml =
                    "<input type=\"text\" class=\"form-control\" value=\"Оплата водителю: 0\" disabled>"+
                    "<span class=\"input-group-addon\">Оплата водителю</span>"+
                    "<input type=\"hidden\" name=\"transferCost\" value=\"0\">"+
                    "<input type=\"hidden\" name=\"hasTransfer\" value=\"no\">";
                foodHtml =
                    "<input type=\"number\" step=\"1\" min=\"0\" class=\"form-control\" id=\"foodPackCount\" name=\"foodPackCount\""+
                    "required placeholder=\"Количество фуд-пакетов: \" value=\"<?= $order["Event"]["FoodPackCount"] ?>\">"+
                    "<input type=\"hidden\" name=\"hasFood\" value=\"yes\">"+
                    "<span class=\"input-group-addon\">Кол-во пакетов</span>";

                break;
            case "allInclusive":
                transferHtml =
                    "<input type=\"number\" step=\"1\" min=\"0\" class=\"form-control\" id=\"transferCost\" name=\"transferCost\" required"+
                    "placeholder=\"Оплата водителю: \" value=\"<?= $order["Event"]["TransferCost"] ?>\">"+
                    "<input type=\"hidden\" name=\"hasTransfer\" value=\"yes\">"+
                    "<span class=\"input-group-addon\">Оплата водителю</span>";

                foodHtml =
                    "<input type=\"number\" step=\"1\" min=\"0\" class=\"form-control\" id=\"foodPackCount\" name=\"foodPackCount\""+
                    "required placeholder=\"Количество фуд-пакетов: \" value=\"<?= $order["Event"]["FoodPackCount"] ?>\">"+
                    "<input type=\"hidden\" name=\"hasFood\" value=\"yes\">"+
                    "<span class=\"input-group-addon\">Кол-во пакетов</span>";


                break;


            case "basePack":
            default:
                transferHtml =
                    "<input type=\"text\" class=\"form-control\" value=\"Оплата водителю: 0\" disabled>"+
                    "<span class=\"input-group-addon\">Оплата водителю</span>"+
                    "<input type=\"hidden\" name=\"transferCost\" value=\"0\">"+
                    "<input type=\"hidden\" name=\"hasTransfer\" value=\"no\">";

                foodHtml =
                    "<input type=\"text\" class=\"form-control\" value=\"Количество фуд-пакетов: 0\" disabled>"+
                    "<span class=\"input-group-addon\">Кол-во пакетов</span>"+
                    "<input type=\"hidden\" name=\"foodPackCount\" value=\"0\">"+
                    "<input type=\"hidden\" name=\"hasFood\" value=\"no\">";
                break;
        }

        $('#food-input').html(foodHtml);
        $('#transfer-input').html(transferHtml);

    });
</script>