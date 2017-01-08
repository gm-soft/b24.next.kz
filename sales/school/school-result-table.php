<div id="tableToPrint">

        <h3>Информация по заказу</h3>

        <?php
        if ($actionPerformed == "order_confirmed"){
            ?>
            <table class="table table-striped">
                <tr>
                    <td>ID заказа аренды (Консолидация 9-1)</td><td><b><?= $_REQUEST["orderId"] ?><b></td></tr>
                <tr>
                    <td>Номер сделки</td><td><a href='https://next.bitrix24.kz/crm/deal/show/<?= $_REQUEST["dealId"] ?>/'><?= $_REQUEST["dealId"] ?></a><b></td></tr>
                <tr>
                    <td>Компания</td><td><a href='https://next.bitrix24.kz/crm/company/show/<?= $_REQUEST["companyId"] ?>/' target='_blank'>Школа: <?= $_REQUEST["companyTitle"] ?></a></td></tr>
                <tr>
                    <td>Контакт</td><td><a href='https://next.bitrix24.kz/crm/contact/show/<?= $_REQUEST["contactId"] ?>/' target='_blank'><?= $_REQUEST["contactName"] ?></a></td></tr>
            </table>
        <?php
        }
        ?>



    <div class="row">
        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Общая информация
                </div>
                <div class="panel-body">
                    <dl class="dl-horizontal">
                        <dt>Тема урока</dt><dd><?= $_REQUEST["subject"]?></dd>
                        <dt>Пакет</dt><dd><?= $_REQUEST["packName"]?> (<?= $_REQUEST["packType"]?>)</dd>
                        <dt>Кол-во учеников</dt><dd><?= $_REQUEST["pupilCount"]?></dd>
                        <dt>Кол-во учителей</dt><dd><?= $_REQUEST["teacherCount"]?></dd>
                        <dt>Дата</dt><dd><?= $_REQUEST["date"]?></dd>
                        <dt>Время</dt><dd><?= $_REQUEST["time"]?></dd>
                        <dt>Продолжительность</dt><dd><?= $_REQUEST["duration"]?></dd>
                        <dt>Центр</dt><dd><?= $_REQUEST["centerName"]?></dd>
                        <dt>Комментарий к заказу</dt><dd><?= $_REQUEST["comment"]?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading">Фуд-пакеты</div>
                <div class="panel-body">
                    <dl class="dl-horizontal">
                        <?php
                        if ($_REQUEST["hasFood"] == "yes") {
                            echo "<dt>Наличие фуд-пакетов</dt><dd><b>Есть</b></dd>".
                                "<dt>Стоимость фуд-пакетов</td><dd>".$_REQUEST["foodCost"]."</td>";
                        } else {
                            echo "<dt>Наличие фуд-пакетов</dt><dd><b>Отсутствуют</b></dd>";
                        }
                        ?>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading">Трансфер</div>
                <div class="panel-body">
                    <dl class="dl-horizontal">
                        <?php
                        if ($_REQUEST["hasTransfer"] == "yes"){
                            echo "<dt>Наличие трансфера</dt><dd><b>Есть</b></dd>";
                            echo "<dt>Оплата водителю</dt><dd>".$_REQUEST["transferCost"]."</dd>";
                        } else {
                            echo "<dt>Наличие трансфера</dt><dd><b>Отсутствует</b></dd>";
                        }
                        ?>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading">Скидки</div>
                <div class="panel-body">
                    <dl class="dl-horizontal">
                        <dt>Скидка</dt><dd><?= $_REQUEST["discount"]?></dd>
                        <dt>Комментарий к скидке</dt><dd><?= $_REQUEST["discountComment"]?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading">Финансовая информация</div>
                <div class="panel-body">
                    <dl class="dl-horizontal">
                        <dt>Стоимость пакетов</dt><dd><?= $_REQUEST["packCost"]?></dd>

                        <dt>Процент Х</dt><dd><?= $_REQUEST["bribe"]?></dd>
                        <dt>Полная стоимость заказа</dt><dd><?= $_REQUEST["totalCost"]?></dd>
                        <dt>Полная стоимость заказа (с учетом скидки)</dt><dd><?= $_REQUEST["totalCostDiscount"]?></dd>
                        <dt>Стоимость заказа (в кассу)</dt><dd><?= $_REQUEST["moneyToCash"]?></dd>
                    </dl>
                </div>
            </div>
        </div>

    </div>
</div>