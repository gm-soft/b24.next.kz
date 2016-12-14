<form id="form" method="post" action="school.php">
    <input type="hidden" name="action_performed" value="order_confirmed">
    <input type="hidden" name="action" value="<?= $action ?>">
    <input type="hidden" name="auth_id" value="<?= $auth_id ?>">
    <input type="hidden" name="admin_token" value="<?= $adminAuthToken ?>">

    <input type="hidden" name="contactId" value="<?= $_REQUEST["contactId"] ?>">
    <input type="hidden" name="companyId" value="<?= $_REQUEST["companyId"]?>">

    <input type="hidden" name="dealId" value="<?= $_REQUEST["dealId"] ?>">
    <input type="hidden" name="orderId" value="<?= $_REQUEST["orderId"] ?>">

    <input type="hidden" name="contactName" value="<?= $_REQUEST["contactName"] ?>">
    <input type="hidden" name="contactPhone" value="<?= $_REQUEST["contactPhone"]?>">
    <input type="hidden" name="companyName" value="<?= $_REQUEST["companyName"]?>">

    <input type="hidden" name="pack" value="<?= $_REQUEST["pack"]?>">

    <input type="hidden" name="pupilCount" value="<?= $_REQUEST["pupilCount"]?>">
    <input type="hidden" name="teacherCount" value="<?= $_REQUEST["teacherCount"]?>">
    <input type="hidden" name="center" value="<?= $_REQUEST["center"]?>">
    <input type="hidden" name="status" value="<?= $_REQUEST["status"]?>">

    <input type="hidden" name="pupilAge" value="<?= $_REQUEST["pupilAge"]?>">
    <input type="hidden" name="packagePrice" value="<?= $_REQUEST["packagePrice"]?>">
    <input type="hidden" name="hasTransfer" value="<?= $_REQUEST["hasTransfer"]?>">
    <input type="hidden" name="hasFood" value="<?= $_REQUEST["hasFood"]?>">

    <input type="hidden" name="date" value="<?= $_REQUEST["date"]?>">
    <input type="hidden" name="time" value="<?= $_REQUEST["time"]?>">
    <input type="hidden" name="duration" value="<?= $_REQUEST["duration"]?>">

    <input type="hidden" name="foodpackCount" value="<?= $_REQUEST["foodpackCount"]?>">
    <input type="hidden" name="transferCost" value="<?= $_REQUEST["transferCost"]?>">
    <!--input type="hidden" name="driver_cost" value="<?= $_REQUEST["driver_cost"]?>"-->


    <input type="hidden" name="bribePercent" value="<?= $_REQUEST["bribePercent"]?>">
    <input type="hidden" name="discount" value="<?= $_REQUEST["discount"]?>">
    <input type="hidden" name="discountComment" value="<?= $_REQUEST["discountComment"]?>">
    <input type="hidden" name="comment" value="<?= $_REQUEST["comment"]?>">
    <input type="hidden" name="subject" value="<?= $_REQUEST["subject"]?>">
    <input type="hidden" name="userId" value="<?= $userId?>">

    <div class="form-group">
        <a href="#" id="back" class="btn btn-default">Вернуться</a>
        <button type="submit" id="submit-btn" class="btn btn-primary">Сохранить заказ</button>
    </div>
</form>