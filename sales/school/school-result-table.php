<div id="tableToPrint">
    <table class="table table-striped">

        <tr><th>Информация о сделке</th><td></td></tr>
        <?php
        if ($actionPerformed == "order_confirmed"){
            echo "<tr><td>ID заказа аренды (Консолидация 9-1)</td><td><b>".$_REQUEST["orderId"]."<b></td></tr>";
            echo "<tr><td>Номер сделки</td><td><a href='https://next.bitrix24.kz/crm/deal/show/".$_REQUEST["dealId"]."/'>".$_REQUEST["dealId"]."</a><b></td></tr>";
            echo "<tr><td>Компания</td><td><a href='https://next.bitrix24.kz/crm/company/show/".$_REQUEST["companyId"]."/' target='_blank'>Школа: ".$_REQUEST["companyTitle"]."</a></td></tr>";
            echo "<tr><td>Контакт</td><td><a href='https://next.bitrix24.kz/crm/contact/show/".$_REQUEST["contactId"]."/' target='_blank'>".$_REQUEST["contactName"]."</a></td></tr>";
        }
        ?>
        <tr><td>Тема урока</td><td><?= $_REQUEST["subject"]?></td></tr>
        <tr><td>Пакет</td><td><?= $_REQUEST["packName"]?> (<?= $_REQUEST["packType"]?>)</td></tr>
        <tr><td>Кол-во учеников</td><td><?= $_REQUEST["pupilCount"]?></td></tr>
        <tr><td>Кол-во учителей</td><td><?= $_REQUEST["teacherCount"]?></td></tr>
        <tr><td>Дата</td><td><?= $_REQUEST["date"]?></td></tr>
        <tr><td>Время</td><td><?= $_REQUEST["time"]?></td></tr>
        <tr><td>Продолжительность</td><td><?= $_REQUEST["duration"]?></td></tr>
        <tr><td>Центр</td><td><?= $_REQUEST["centerName"]?></td></tr>
        <hr>
        <tr><td>Комментарий к заказу</td><td><?= $_REQUEST["comment"]?></td></tr>
        <tr><td>Стоимость пакетов</td><td><?= $_REQUEST["packCost"]?></td></tr>

        <tr><th>Фуд-пакеты</th><td></td></tr>
        <?php
        if ($_REQUEST["hasFood"] == "yes") {
            echo "<tr><td>Наличие фуд-пакетов</td><td><b>Есть</b></td></tr>".
                "<tr><td>Стоимость фуд-пакетов</td><td>".$_REQUEST["foodCost"]."</td></tr>";
        } else {
            echo "<tr><td>Наличие фуд-пакетов</td><td><b>Отсутствуют</b></td></tr>";
        }
        ?>
        <!--tr><td>Кол-во фуд-пакетов</td><td><?= $_REQUEST["foodPackCount"]?></td></tr-->


        <tr><th>Трансфер</th><td></td></tr>
        <?php
        if ($_REQUEST["hasTransfer"] == "yes"){
            echo "<tr><td>Наличие трансфера</td><td><b>Есть</b></td></tr>";
            echo "<tr><td>Оплата водителю</td><td>".$_REQUEST["transferCost"]."</td></tr>";
        } else {
            echo "<tr><td>Наличие трансфера</td><td><b>Отсутствует</b></td></tr>";
        }
        ?>
        <!--tr><td>Деньги водителю</td><td><?= $_REQUEST["driverCost"]?></td></tr>
                    <tr><td>Трансфер в кассу</td><td><?= $_REQUEST["transferToCash"]?></td></tr-->

        <tr><th>Информация о скидке</th><td></td></tr>
        <tr><td>Скидка</td><td><?= $_REQUEST["discount"]?></td></tr>
        <tr><td>Комментарий к скидке</td><td><?= $_REQUEST["discountComment"]?></td></tr>

        <tr><th>Финансовая информация</th><td></td></tr>

        <tr><td>Процент Х</td><td><?= $_REQUEST["bribe"]?></td></tr>
        <tr><td>Полная стоимость заказа</td><td><?= $_REQUEST["totalCost"]?></td></tr>
        <tr><td>Полная стоимость заказа (с учетом скидки)</td><td><?= $_REQUEST["totalCostDiscount"]?></td></tr>
        <tr><td>Стоимость заказа (в кассу)</td><td><?= $_REQUEST["moneyToCash"]?></td></tr>
    </table>
</div>