<dl class="dl-horizontal">
    <dt>Стоимость заказа</dt><dd><?= $totalCost ?></dd>
    <dt>Оплачено</dt><dd><?= $payed ?></dd>
    <dt>Остаток по оплате</dt><dd><b><?= $remainder ?></b></dd>
    <dt>Статус заказа</dt><dd><i><?= $closeResponse["status"] ?></i></dd>
    <?php
    if (isset($barItems)){
        ?>
        <dt>Доп.заказов, кол-во</dt><dd><?= $barItemsCount ?></dd>
        <?php
    }
    ?>

    <dt>Сделка</dt><dd><a href="https://next.bitrix24.kz/crm/deal/show/<?= $deal["ID"] ?>/"><?= $deal["ID"] ?></a></dd>
    <dt>Сообщение сервера</dt><dd><?= $message ?></a></dd>
</dl>
<?php
if (isset($barItems) && $barItemsCount > 0){
    ?>
    <table class="table table-striped">
        <tr>
            <th>№</th>
            <th>Название</th>
            <th>Цена</th>
            <th>Кол-во</th>
            <th>Стоимость</th>
        </tr>
        <?php
        $barCost = 0;
        for ($i = 0; $i < $barItemsCount; $i++){

            $name = $barItems[$i]["name"];
            $count = $barItems[$i]["count"];
            $price = $barItems[$i]["price"];
            $cost = $barItems[$i]["cost"];
            $barCost += $cost;
            echo "\t\t"."<tr>";
            echo "<td>$i</td><td>$name</td><td>$price</td><td>$count</td><td>$cost</td>";
            echo "<tr>"."\n";
        }
        ?>
        <tr>
            <td></td>
            <td>Итого</td>
            <td></td>
            <td></td>
            <td><?= $barCost ?></td>
        </tr>


    </table>

    <?php
}

?>