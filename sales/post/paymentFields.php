<div class="form-group">
    <label class="control-label col-sm-3" for="paymentValue">Сумма оплаты</label>
    <div class="col-sm-9">
        <div class="input-group">
            <input type="number" min="1" step="0.1" disabled class="form-control" id="paymentValue" name="paymentValue" placeholder="Введите сумму оплаты" required>
            <span class="input-group-addon"></span>
        </div>
    </div>
</div>

<div class="form-group">
    <label class="control-label col-sm-3" for="receiptDate">Дата чека</label>
    <div class="col-sm-9">
        <div class="input-group">
            <input type="date" disabled class="form-control" id="receiptDate" name="receiptDate" required placeholder="Выберите дату" value="<?= date("Y-m-d") ?>" >
            <span class="input-group-addon"></span>
        </div>
    </div>
</div>

<div class="form-group">
    <label class="control-label col-sm-3" for="receiptNumber">Номер чека</label>
    <div class="col-sm-9">
        <div class="input-group">
            <input type="number" min="1" step="1" disabled class="form-control" id="receiptNumber" name="receiptNumber" required placeholder="Введите номер чека" value="<?= date("Y-m-d") ?>">
            <span class="input-group-addon"></span>
        </div>
    </div>
</div>