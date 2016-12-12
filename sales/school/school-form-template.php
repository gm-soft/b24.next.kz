

<form id="form" class="form-horizontal" method="post" action="school.php">
    <input type="hidden" name="action_performed" value="order_created">
    <input type="hidden" name="action" value="<?= $action ?>">
    <input type="hidden" name="auth_id" value="<?= $auth_id ?>">
    <input type="hidden" name="admin_token" value="<?= $adminAuthToken ?>">

    <input type="hidden" name="contact_id" value="<?= $contactId ?>">
    <input type="hidden" name="company_id" value="<?= $companyId?>">

    <input type="hidden" name="contact_name" value="<?= $contact["NAME"]." ".$contact["LAST_NAME"] ?>">
    <input type="hidden" name="contact_phone" value="<?= $contact["PHONE"][0]["VALUE"]?>">
    <input type="hidden" name="company_name" value="<?= $company["TITLE"]?>">

    <div class="form-group">
        <label class="control-label col-sm-3" for="pack">Выберите пакет:</label>
        <div class="col-sm-9">
            <div class="input-group">
                <select class="form-control" id="pack" name="pack" required>
                    <?php
                    if (!isset($order["Event"]["Pack"])){
                        ?>
                        <option value=\"\">Выберите из списка</option>
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
                <span class="input-group-addon"><i class="glyphicon glyphicons-gift"></i></span>
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
                        <option value="next_ese">NEXT Esentai</option>
                        <option value="next_apo">NEXT Aport</option>
                        <option value="next_pro">NEXT Promenade</option>
                    <?php } else {
                        $selectedOption = $order["Center"];
                        ?>
                        <option value="next_ese" <?= $selectedOption == "next_ese" ? "selected" : "" ?>>NEXT Esentai</option>
                        <option value="next_apo" <?= $selectedOption == "next_apo" ? "selected" : "" ?>>NEXT Aport</option>
                        <option value="next_pro" <?= $selectedOption == "next_pro" ? "selected" : "" ?>>NEXT Promenade</option>

                    <?php } ?>
                </select>
                <span class="input-group-addon"><i class="glyphicon glyphicons-building"></i></span>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-3" for="pupil_count">Количество детей:</label>
        <div class="col-sm-9">
            <div class="input-group">
                <?php $value = isset($order["Event"]["PupilCount"]) ? $order["Event"]["PupilCount"] : "" ?>
                <input type="number" step="1" min="0" class="form-control" id="pupil_count" name="pupil_count" required placeholder="Количество детей (учеников)" value="<?= $value ?>">
                <span class="input-group-addon"><i class="glyphicon glyphicons-fire"></i></span>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-3" for="pupil_age">Возраст детей:</label>
        <div class="col-sm-9">
            <div class="input-group">
                <?php $value = isset($order["Event"]["PupilAge"]) ? $order["Event"]["PupilAge"] : "" ?>
                <input type="text" class="form-control" id="pupil_age" name="pupil_age" required placeholder="Пример: 6-7 класс, 12-14 лет" value="<?= $value ?>">
                <span class="input-group-addon"><i class="glyphicon glyphicons-fire"></i></span>
            </div>
        </div>
    </div>

    <hr>

    <div class="form-group">
        <label class="control-label col-sm-3" for="teacher_count">Количество учителей:</label>
        <div class="col-sm-9">
            <div class="input-group">
                <?php $value = isset($order["Event"]["TeacherCount"]) ? $order["Event"]["TeacherCount"] : "" ?>
                <input type="number" step="1" min="0" class="form-control" id="teacher_count" name="teacher_count" required placeholder="Кол-во учителей и/или родителей" value="<?= $value ?>">
                <span class="input-group-addon"><i class="glyphicon glyphicons-parents"></i></span>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-3" for="package_price">Цена пакета:</label>
        <div class="col-sm-9">
            <div class="input-group">
                <?php $value = isset($order["Event"]["PackPrice"]) ? $order["Event"]["PackPrice"] : "" ?>
                <input type="number" step="0ю1" min="0" class="form-control" id="package_price" name="package_price" required placeholder="Стоимость пакета развлечений на одного ребенка" value="<?= $value ?>">
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
                    $date = isset($order["DateOfEvent"]) ? $order["DateOfEvent"] : time() ;
                    $value = date("d.m.Y", $date);
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

                if (isset($order["DateOfEvent"])){
                    $date = $order["DateOfEvent"];
                    $value = date("H:m", $date);
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
        <label class="control-label col-sm-3" for="with-food">С фуд-пакетом:</label>
        <div class="col-sm-9">
            <?php
                $selectedOption = isset($order["BanquetInfo"]) && !is_null($order["BanquetInfo"]) ? "yes" : "no";
            ?>
            <select class="form-control" id="with-food" name="with-food" required>
                <option value="no" <?= $selectedOption == "no" ? "selected" : "" ?>>Нет</option>
                <option value="yes" <?= $selectedOption == "yes" ? "selected" : "" ?>>Да</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-3" for="has_transfer">С трансфером:</label>
        <div class="col-sm-9">
            <?php
                $selectedOption = isset($order["Event"]["HasTransfer"]) && !is_null($order["Event"]["HasTransfer"]) ? "yes" : "no";
            ?>
            <select class="form-control" id="has_transfer" name="has_transfer" required>
                <option value="no" <?= $selectedOption == "no" ? "selected" : "" ?>>Нет</option>
                <option value="yes" <?= $selectedOption == "yes" ? "selected" : "" ?>>Да</option>
            </select>
        </div>
    </div>
    <div id="transfer-inputs"></div>
    <hr>

    <div class="form-group">
        <label class="control-label col-sm-3" for="bribe_percent">Сумма учителю (за одного ребенка):</label>
        <div class="col-sm-9">
            <div class="input-group">
                <?php $value = isset($order["Event"]["TeacherBribePercent"]) ? $order["Event"]["TeacherBribePercent"] : "" ?>
                <input type="number" step="1" min="0" class="form-control" id="bribe_percent" name="bribe_percent" required placeholder="Сумма учителю за одного ребенка" value="<?= $value ?>">
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
                <input type="text" class="form-control" id="discount_comment" name="discount_comment" placeholder="Комментарий к скидке" maxlength="150" value="<?= $value?>">
                <span class="input-group-addon"><i class="glyphicon glyphicons-heart-empty"></i></span>
            </div>

        </div>

    </div>

    <!--div class="form-group">
        <label class="control-label col-sm-3" for="discount_comment">Комментарий к скидке:</label>
        <div class="col-sm-9">
            <div class="input-group">
                <?php $value = isset($order["FinanceInfo"]["DiscountComment"]) ? $order["FinanceInfo"]["DiscountComment"] : "" ?>
                <input type="text" class="form-control" id="discount_comment" name="discount_comment" placeholder="Комментарий к скидке" maxlength="150" value="<?= $value?>">
                <span class="input-group-addon"><i class="glyphicon glyphicons-heart-empty"></i></span>
            </div>
        </div>
    </div-->
    <div class="form-group">
        <label class="control-label col-sm-3" for="comment">Комментарий к заказу:</label>
        <div class="col-sm-9">
            <?php $value = isset($order["Comment"]) ? $order["Comment"] : "" ?>
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