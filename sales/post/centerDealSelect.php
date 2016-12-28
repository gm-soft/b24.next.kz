<input type="hidden" name="action" value="<?= $_REQUEST["action"] ?>">
<input type="hidden" name="authId" value="<?= $_REQUEST["authId"] ?>">
<input type="hidden" name="adminToken" value="<?= $_REQUEST["adminToken"] ?>">

<div class="form-group">
    <label class="control-label col-sm-3" for="filterSelect">Выберите центр</label>
    <div class="col-sm-9">
        <div class="input-group">
            <select class="form-control" id="filterSelect" name="filterSelect">
                <option value="">Центр</option>
                <option value="128">NEXT Aport</option>
                <option value="130">NEXT Esentai</option>
                <option value="132">NEXT Promenade</option>

            </select>
            <span class="input-group-addon"></span>
        </div>
    </div>
</div>


<div class="form-group">
    <label class="control-label col-sm-3" for="dealSelect">Выберите заказ аренды</label>
    <div class="col-sm-9">
        <div class="input-group">
            <select class="form-control" id="dealSelect" name="dealSelect" required>
                <option value="">Нет данных</option>
            </select>
            <span class="input-group-addon"></span>
        </div>
    </div>
</div>

<div class="form-group" >
    <div class="control-label col-sm-3">Информация по заказу</div>
    <div id="orderInfo" class="col-sm-9 alert alert-warning">

    </div>
</div>