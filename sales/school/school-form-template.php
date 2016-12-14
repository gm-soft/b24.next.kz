

<form id="form" class="form-horizontal" method="post" action="school.php">
    <input type="hidden" name="action_performed" value="order_saved">
    <input type="hidden" name="action" value="<?= $action ?>">
    <input type="hidden" name="auth_id" value="<?= $auth_id ?>">
    <input type="hidden" name="admin_token" value="<?= $adminAuthToken ?>">

    <input type="hidden" name="contactId" value="<?= $contactId ?>">
    <input type="hidden" name="companyId" value="<?= $companyId?>">

    <input type="hidden" name="dealId" value="<?= $dealId?>">
    <input type="hidden" name="orderId" value="<?= $orderId?>">

    <input type="hidden" name="contactName" value="<?= $contact["NAME"]." ".$contact["LAST_NAME"] ?>">
    <input type="hidden" name="contactPhone" value="<?= $contact["PHONE"][0]["VALUE"]?>">
    <input type="hidden" name="companyName" value="<?= $company["TITLE"]?>">



    <div class="form-group">
        <label class="control-label col-sm-3" for="pack">Выберите пакет:</label>
        <div class="col-sm-9">
            <div class="input-group">
                <select class="form-control" id="pack" name="pack" required>
                    <?php
                    if (!isset($order["Event"]["Pack"])){
                        ?>
                        <option value="">Выберите из списка</option>
                        <option value="basepack">Базовый</option>
                        <option value="standartpack">Стандартный</option>
                        <option value="allinclusive">Все включено</option>
                        <option value="newyear">Новогодний</option>
                    <?php } else {
                        $selectedOption = $order["Event"]["Pack"];
                        ?>
                        <option value="basepack" <?= $selectedOption == "basepack" ? "selected" : "" ?>>Базовый</option>
                        <option value="standartpack" <?= $selectedOption == "standartpack" ? "selected" : "" ?>>Стандартный</option>
                        <option value="allinclusive" <?= $selectedOption == "allinclusive" ? "selected" : "" ?>>Все включено</option>
                        <option value="newyear" <?= $selectedOption == "newyear" ? "selected" : "" ?>>Новогодний</option>

                    <?php } ?>


                </select>
                <span class="input-group-addon"><span class="glyphicon glyphicon-building"></span></span>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-3" for="center">Центр:</label>
        <div class="col-sm-9">
            <div class="input-group">
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
                <span class="input-group-addon"><i class="glyphicon glyphicon-building"></i></span>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-3" for="status">Статус заказа:</label>
        <div class="col-sm-9">
            <div class="input-group">
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


                    } else {
                        $value = "initiated";
                    }

                ?>
                <select class="form-control" id="status" name="status" required>
                    <option value="initiated" <?= $value == "initiated" ? "selected" : "" ?>>Заказ подтвержден</option>
                    <option value="conducted" <?= $value == "conducted" ? "selected" : "" ?>>Аренда проведена</option>
                    <option value="closed" <?= $value == "closed" ? "selected" : "" ?>>Сделка закрыта</option>
                    <option value="canceled" <?= $value == "canceled" ? "selected" : "" ?>>Аренда отменена</option>
                </select>
                <span class="input-group-addon"><i class="glyphicon glyphicon-building"></i></span>
            </div>
        </div>
    </div>
    <hr>
    <div class="form-group">
        <label class="control-label col-sm-3" for="pupilCount">Количество детей:</label>
        <div class="col-sm-9">
            <div class="input-group">
                <?php $value = isset($order["Event"]["PupilCount"]) ? $order["Event"]["PupilCount"] : "" ?>
                <input type="number" step="1" min="0" class="form-control" id="pupilCount" name="pupilCount" required placeholder="Количество детей (учеников)" value="<?= $value ?>">
                <span class="input-group-addon"><i class="glyphicon glyphicons-fire"></i></span>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-3" for="pupilAge">Возраст детей:</label>
        <div class="col-sm-9">
            <div class="input-group">
                <?php $value = isset($order["Event"]["PupilAge"]) ? $order["Event"]["PupilAge"] : "" ?>
                <input type="text" class="form-control" id="pupilAge" name="pupilAge" required placeholder="Пример: 6-7 класс, 12-14 лет" value="<?= $value ?>">
                <span class="input-group-addon"><i class="glyphicon glyphicon-fire"></i></span>
            </div>
        </div>
    </div>

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
        <div class="col-sm-9">
            <div class="input-group">

                <?php
                if (isset($order["DateAtom"])) {
                    $date = strtotime($order["DateAtom"]);
                    $date = $date + 6 * 3600;
                    $value = date("Y-m-d", $date);
                } else {
                    $value = "";
                }

                    //echo $date. " " .$value;
                ?>
                <input type="date" class="form-control" id="date" name="date" required placeholder="Выберите дату" value="<?= $value ?>">
                <span class="input-group-addon"><i class="glyphicon glyphicons-calendar"></i></span>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-3" for="time">Начало в:</label>
        <div class="col-sm-9">
            <div class="input-group">
                <?php

                if (isset($order["DateAtom"])){
                    // 2016-12-13T11:00:00+06:00
                    $date = strtotime($order["DateAtom"]);
                    $date = $date + 6 * 3600;
                    $value = date("H:m", $date);
                    //echo $date. " " .$value;
                } else {
                    $value = "";
                }

                ?>
                <input type="time" class="form-control" id="time" name="time" required
                       placeholder="Время" value="<?= $value ?>">
                <span class="input-group-addon"><i class="glyphicon glyphicons-clock"></i></span>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-3" for="duration">Длительность аренды (часов):</label>
        <div class="col-sm-9">
            <div class="input-group">
                <?php $value = isset($order["Event"]["Duration"]) ? $order["Event"]["Duration"] : "3" ?>
                <input type="number" step="1" min="0" class="form-control" id="duration" name="duration" required placeholder="Продолжительность мероприятия" value="<?= $value ?>">
                <span class="input-group-addon"><i class="glyphicon glyphicons-clock"></i></span>
            </div>
        </div>
    </div>
    <hr>
    <div class="form-group">
        <label class="control-label col-sm-3" for="hasFood">С фуд-пакетом:</label>
        <div class="col-sm-9">
            <?php
                $selectedOption = isset($order["BanquetInfo"]) && !is_null($order["BanquetInfo"]) ? "yes" : "no";
            ?>
            <select class="form-control" id="hasFood" name="hasFood" required>
                <option value="no" <?= $selectedOption == "no" ? "selected" : "" ?>>Нет</option>
                <option value="yes" <?= $selectedOption == "yes" ? "selected" : "" ?>>Да</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-3" for="hasTransfer">С трансфером:</label>
        <div class="col-sm-3">
            <?php
                $selectedOption = isset($order["Event"]["HasTransfer"]) ? $order["Event"]["HasTransfer"]  : "no";
            ?>
            <select class="form-control" id="hasTransfer" name="hasTransfer" required>
                <option value="no" <?= $selectedOption == "no" ? "selected" : "" ?>>Нет</option>
                <option value="yes" <?= $selectedOption == "yes" ? "selected" : "" ?>>Да</option>
            </select>
        </div>
        <div id="transfer-inputs" class="col-sm-6">

            <?php
            if (isset($order["Event"]["TransferCost"]) && $order["Event"]["TransferCost"] != 0){
            ?>
                <input type="number" step="1" min="0" class="form-control" id="transferCost" name="transferCost"
                       required placeholder="Стоимость трансфера" value="<?= $order["Event"]["TransferCost"] ?>">
            <?php } else {
                ?>
                <input type="hidden" name="transferCost" value="0">
                <input type="text" class="form-control" name="empty" value="Стоимость трансфера: 0" disabled >
            <?php } ?>

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