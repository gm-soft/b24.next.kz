<fieldset>
    <legend>Поиск заказа</legend>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label class="col-sm-3 control-label" for="filterSelect">Центр</label>

                <div class="col-sm-9">
                    <select class="form-control" id="filterSelect" name="filterSelect">
                        <option value="">Центр</option>
                        <option value="128">NEXT Aport</option>
                        <option value="130">NEXT Esentai</option>
                        <option value="132">NEXT Promenade</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label" for="dealSelect">Заказ аренды</label>
                <div class="col-sm-9">
                    <select class="form-control" id="dealSelect" name="dealSelect" required>
                        <option value="">Нет данных</option>
                    </select>
                </div>
                
            </div>

        </div>

        <div class="col-sm-6">
            <div id="orderInfo" class="alert alert-info">Информация по заказу</div>

        </div>


    </div>
</fieldset>

