
<?php
		//if(time() > $access_data["ts"] + $access_data["expires_in"]) 
		//	echo "<b>Авторизационные данные истекли</b>";
		$lifetime = $access_data["ts"] + $access_data["expires_in"] - time();
		$update_time = date("H:i", $access_data["ts"] + (60*60*6));
		$expire_time = date("H:i", ($access_data["ts"] + $access_data["expires_in"] + (60*60*6)));
		//echo "Авторизационный токен: <b>".$access_data["access_token"]."</b>";
?>
<div class="container">

    <div class="page-header">
        <h1>Сервер авторизации next.bitrix24.kz</h1>
    </div>

    <table class="table table-striped">
        <thead>
            <tr><th>Время жизни</th><th>Время обновления</th><th>Срок жизни</th><th>Авторизаци от имени</th><th></th><th></th></tr>
        </thead>
        <tbody>
            <tr>
                <td><?=$lifetime?> сек.</td>
                <td><?=$update_time?></td>
                <td><?=$expire_time?></td>
                <td><a href="<?=PATH?>?method=user.current"><?= $_SESSION['tokenemail']?></a></td>
                <td>
                    <a href="<?=PATH?>?action=refresh" class="btn btn-default">Обновить токен</a>
                    <a href="<?=PATH?>?action=clear" class="btn btn-default">Очистить токен</a>
                    <div class="dropdown div-inline">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">Логи
                        <span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <li><a href="../server/log_page.php?type=errors">Ошибки (errors.log)</a></li>
                                <li><a href="../server/log_page.php?type=process_events">События (process_events.log)</a></li>
                                <li><a href="../server/log_page.php?type=auth_events">Лог авторизаций (auth.log)</a></li>
                                <li><a href="../server/log_page.php?type=debug">Дебаг (debug.log)</a></li>
                        </ul>
                    </div>
                </td>
                
            </tr>
        </tbody>
    </table>
    <hr>
	<div class="row">
        <div class="col-sm-6">
			<h3>Поля сущности</h3>
            <form class="form-horizontal" method="GET" action="<?= PATH?>">
	            <div class="form-group">
	                <!--input type="hidden" name="method" value="crm.deal.fields"-->
	                <label class="col-sm-2" for="method_select_fields">Сущность:</label>
                    <div class="col-sm-10">
                        <select class="form-control" id="method_select_fields" name="method">
                            <option value="no.method">Не выбрана</option>
                            <option value="user.fields">Пользователь CRM</option>
                            <option value="department.fields">Департамент CRM</option>
                            <option value="crm.deal.fields">Сделка</option>
                            <option value="crm.lead.fields">Лид</option>
                            <option value="crm.product.fields">Товар</option>
                            <option value="crm.productrow.fields">Товары, прикрепленные к сделке</option>
                            <option value="crm.contact.fields">Контакт</option>
                            <option value="crm.company.fields">Компания</option>
                            <option value="task.item.getmanifest">Задача</option>
                        </select>
                    </div>
	                
               	</div>
                <div class="form-group">
                	<button type="submit" class="btn btn-default">Получить</button>
                    <a href="#" id="get_fields_sbt" class="btn btn-default">Получить AJAX'ом</a>
            	</div>
            </form>
        </div>

        <div class="col-sm-6">
            <h3>Значение сущности</h3>
            <form class="form-horizontal" method="GET" action="<?=PATH?>">
                <div class="form-group">
                    <label class="col-sm-2" for="method_select_instance">Сущность:</label>
                    <div class="col-sm-10">
                        <select class="form-control" id="method_select_instance" name="method">
                            <option value="no.method">Не выбрана</option>
                            <option value="user.get">Пользователь CRM</option>
                            <option value="department.get">Департамент CRM</option>
                            <option value="crm.deal.get">Сделка</option>
                            <option value="crm.lead.get">Лид</option>
                            <option value="crm.product.get">Товар</option>
                            <option value="crm.deal.productrows.get">Товары, прикрепленные к сделке</option>
                            <option value="crm.contact.company.items.get">Контакт в компаниях</option>
                            <option value="crm.contact.get">Контакт</option>
                            <option value="crm.company.get">Компания</option>
                            <option value="task.item.getdata">Задача</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2" for="deal_id">ID:</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" id="deal_id" name="deal_id">
                    </div>
                </div>

                <div class="form-group">
                	<button type="submit" class="btn btn-default">Получить</button>
                    <a href="#" id="get_instance_sbt" class="btn btn-default">Получить AJAX'ом</a>
            	</div>
            </form>
        </div>
            <!--  -->
        </div>


	</div>
</div>

