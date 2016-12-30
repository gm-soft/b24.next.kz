<?php
	require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
	require($_SERVER["DOCUMENT_ROOT"]."/include/help.php");
	require($_SERVER["DOCUMENT_ROOT"]."/Helpers/BitrixHelperClass.php");

	$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
	$authId = isset($_REQUEST["authId"]) && !empty($_REQUEST["authId"]) ? $_REQUEST["authId"] : null;

	if (!isset($_REQUEST["authId"]) || empty($_REQUEST["authId"])) {
		
		redirect("https://b24.next.kz/sales/index.php");
	}

	$admin_authId = isset($_REQUEST["adminToken"]) ? $_REQUEST["adminToken"] : get_access_data(true);

	

	$curr_user = BitrixHelper::getCurrentUser($authId);
	$_SESSION["user_name"] =  $curr_user["EMAIL"];
	$_SESSION["user_id"] =  $curr_user["ID"];
	$userId = $curr_user["ID"];

	$form_action = "booth.php";

	$actionPerformed = $_REQUEST["actionPerformed"];

	switch ($actionPerformed){
        case "contactDefined":
        case "contact_defined":

            $contactId = isset($_REQUEST["contact_id"]) ? $_REQUEST["contact_id"] : $_REQUEST["contactId"];

	        $contact = BitrixHelper::getContact($contactId);
            require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");

            ?>
            <div class="container ">

                <h1>Продажа буса</h1>
                <div class="row">
                    <div class="col-sm-2"><b>Контакт:</b></div>
                    <div class="col-sm-10"><?= $contact["NAME"]." ".$contact["LAST_NAME"]?> (<a href="https://next.bitrix24.kz/crm/contact/show/<?= $contact["ID"]?>/">ID<?= $contact["ID"]?></a>)</div>
                </div>
                <hr>
                <form id="form" class="form-horizontal" method="post" action="">
                    <input type="hidden" name="actionPerformed" value="price_get">
                    <input type="hidden" name="authId" value="<?= $authId ?>">
                    <input type="hidden" name="action" value="<?= $action ?>">
                    <input type="hidden" name="adminToken" value="<?= $admin_authId ?>">

                    <input type="hidden" name="contact_name" value="">
                    <input type="hidden" name="last_name" value="">
                    <input type="hidden" name="contact_phone" value="">
                    <input type="hidden" name="contact_id" value="<?= $contactId ?>">

                    <div class="form-group">
                        <label class="control-label col-sm-2" for="booth">Бус:</label>
                        <div class="col-sm-10">
                            <select class="form-control " id="booth" name="booth" required>
                                <option value="">Не выбран</option>
                                <optgroup label="NEXT Aport">
                                    <option value="apo_booth_1">Motion Booth 1 (158)</option>
                                    <option value="apo_booth_2">Motion Booth 2 (159)</option>
                                    <option value="apo_booth_3">Motion Booth 3 (160)</option>
                                </optgroup>

                                <optgroup label="NEXT Esentai">
                                    <option value="ese_booth_1">Motion Booth 1 (181)</option>
                                    <option value="ese_booth_2">Motion Booth 2 (182)</option>
                                    <option value="ese_booth_3">Motion Booth 3 (183)</option>
                                    <option value="ese_booth_4">Motion Booth 4 (184)</option>
                                </optgroup>

                                <optgroup label="NEXT Promenade">
                                    <option value="pro_booth_1">Motion Booth 1 (123)</option>
                                    <option value="pro_booth_2">Motion Booth 2 (124)</option>
                                    <option value="pro_music_booth_3">Music Booth (125)</option>
                                    <option value="pro_double_booth_4">Double Booth (153)</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <hr>
                    <h3>Дата, время и продолжительность аренды</h3>
                    <?php
                    $now = date("Y-m-d");

                    ?>
                    <div class="form-group">
                        <div class="col-sm-4">
                            <div class="input-group">
                                <div class="input-group-addon">Дата</div>
                                <input type="date" class="form-control" id="date" name="date" required placeholder="Выберите дату" value="<?= $now ?>">

                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="input-group">
                                <div class="input-group-addon">Время</div>
                                <input type="time" class="form-control" id="time" name="time" required placeholder="Выберите время">

                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="input-group">
                                <div class="input-group-addon">Длительность</div>
                                <input type="number" step="0.5" class="form-control" id="duration" name="duration" required
                                       placeholder="Кол-во часов">

                            </div>
                        </div>

                    </div>

                    <!--div class="form-group">
                        <label class="control-label col-sm-2" for="time">Время:</label>
                        <div class="col-sm-10">
                            <input type="time" class="form-control" id="time" name="time" required
                                   placeholder="Выберите время">
                        </div>
                    </div-->

                    <!--div class="form-group">
                        <label class="control-label col-sm-2" for="duration">Время аренды (часов):</label>
                        <div class="col-sm-10">
                            <input type="number" step="0.5" class="form-control" id="duration" name="duration" required
                                   placeholder="Введите кол-во часов">
                        </div>
                    </div-->

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button id="submit" type="submit" class="btn btn-primary">Рассчитать стоимость</button>
                        </div>
                    </div>
                </form>

                <div id="alert" class="alert alert-warning">
                </div>
            </div>
            <?php
            break;
		//-------------------------------------
		//-------------------------------------
		case "price_get":

			$datetime_atom = $_REQUEST["date"]."T".$_REQUEST["time"]."+06:00";
			$url = "https://script.google.com/macros/s/AKfycbxjyTPPbRdVZ-QJKcWLFyITXIeQ1GwI7fAi0FgATQ0PsoGKAdM/exec";
			$parameters = array(
				"event" => "OnBoothPriceRequested",
				"booth_id" => $_REQUEST["booth"],
				"datetime_atom" => $datetime_atom,
				"date" => $_REQUEST["date"],
				"time" => str_replace(":", "-", $_REQUEST["time"]),

				"duration" => str_replace(",", ".", $_REQUEST["duration"]),
			);

			$process_data = query("POST", $url, $parameters);
			$process_data = isset($process_data["result"]) ? $process_data["result"] : $process_data;
			log_debug(var_export($process_data, true));

            require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
			if (!is_null($process_data) ) {
				$cost = $process_data["cost"];
				$date_string = $process_data["date_string"];
				$end_time_str = $process_data["end_time_string"];
				$center_name = $process_data["center_name"];
				$booth_name = $process_data["booth_name"];
				?>
				<div class="container">
					<div class="row">
						<div class="col-md-2"></div>
						<div class="col-md-8">
						<h2>Стоимсть продажи буса:</h2>
						<table class="table table-striped">
							<tbody>
								<tr> <td>Выбранный центр:</td> <td><?= $center_name ?></td> </tr>
								<tr> <td>Выбранный бус:</td> <td><?= $booth_name ?></td> </tr>
								<tr> <td>Время начала аренды:</td> <td><?= $_REQUEST["time"] ?></td> </tr>
								<tr> <td>Время окончания аренды:</td> <td><?= $end_time_str ?></td> </tr>
								<tr> <td>Продолжительность, часов:</td> <td><?= $_REQUEST["duration"] ?></td> </tr>
								<tr> <td>Стоимость аренды буса:</td> <td><b><?= $cost ?> тг.</b></td> </tr>
							</tbody>
						</table>
							<form id="form" class="form-horizontal" method="post" action="">
								<input type="hidden" name="actionPerformed" value="create_booth">
								<input type="hidden" name="authId" value="<?= $authId ?>">
								<input type="hidden" name="action" value="<?= $action ?>">
								<input type="hidden" name="ADMIN_AuthId" value="<?= $admin_authId ?>">


								<input type="hidden" name="contact_name" value="<?= $_REQUEST["contact_name"] ?>">
								<input type="hidden" name="last_name" value="<?= $_REQUEST["last_name"] ?>">
								<input type="hidden" name="contact_phone" value="<?= $_REQUEST["contact_phone"] ?>">
								<input type="hidden" name="contact_id" value="<?= $_REQUEST["contact_id"] ?>">

								<input type="hidden" name="booth" value="<?= $_REQUEST["booth"] ?>">
								<input type="hidden" name="date" value="<?= $_REQUEST["date"] ?>">
								<input type="hidden" name="time" value="<?= $_REQUEST["time"] ?>">
								<input type="hidden" name="duration" value="<?= $_REQUEST["duration"] ?>">

								<div class="form-group">
									<label class="control-label" for="discount">Сумма скидки <a href="#" class="glyphicon glyphicon-question-sign" data-toggle="tooltip"  data-placement="right" title="Если необходимо"></a></label>
									<input type="number" step="0.1" class="form-control" id="discount" name="discount" placeholder="Скидка (если необходимо)">
								</div>

								<div class="form-group">
									<label class="control-label" for="discount_comment">Комментарий к скидке <a href="#" class="glyphicon glyphicon-question-sign" data-toggle="tooltip"  data-placement="right" title="Если комментарий пуст, то скидка автоматически обнуляется"></a> :</label>
									<input type="text" class="form-control" id="discount_comment" name="discount_comment" placeholder="Комментарий для скидки">
								</div>

								<div class="form-group">
									<button id="submit" type="submit" class="btn btn-primary">Создать заказ</button>
								</div>
							</form>

							<div id="alert" class="alert alert-warning">
							</div>
						</div>
					</div>
					<?php
					} else {
						?>
						<div class="container">
							<div class="alert alert-danger">
								<strong>Внимание!</strong> Возникла какая-то ошибка. Повторите попытку позднее
							</div>
						</div>

						<?php
					}

			break;
		//-------------------------------------
		//-------------------------------------
		case "create_booth":


			$datetime_atom = $_REQUEST["date"]."T".$_REQUEST["time"]."+06:00";

			$curr_user = isset($curr_user) ? $curr_user : BitrixHelper::getCurrentUser($authId);
			$url = "https://script.google.com/macros/s/AKfycbxjyTPPbRdVZ-QJKcWLFyITXIeQ1GwI7fAi0FgATQ0PsoGKAdM/exec";
			$parameters = array(
				"event" => "OnBoothCreateRequested",
				"booth_id" => $_REQUEST["booth"],
				"contact_id" => $_REQUEST["contact_id"],
				"contact_name" => $_REQUEST["contact_name"],
				"last_name" => $_REQUEST["last_name"],
				"contact_phone" => BitrixHelper::formatPhone($_REQUEST["contact_phone"]),

				"datetime_atom" => $datetime_atom,
				"date" => $_REQUEST["date"],
				"time" => str_replace(":", "-", $_REQUEST["time"]),

				"duration" => str_replace(".", ",", $_REQUEST["duration"]),
				"user_id" => $_SESSION["user_id"],
				"user_name" => $curr_user["LAST_NAME"]." ".$curr_user["NAME"],
				"discount" => $_REQUEST["discount"],
				"discount_comment" => $_REQUEST["discount_comment"],
			);

			$process_data = query("POST", $url, $parameters);
			$process_data = isset($process_data["result"]) ? $process_data["result"] : $process_data;

            require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
			if (!is_null($process_data) ) {
				$deal_id = $process_data["deal_id"];
				$order_id = $process_data["order_id"];
				$contact_id = $process_data["contact_id"];
				$cost = $process_data["cost"];
				$full_cost = $process_data["full_cost"];

				$date_string = $process_data["date_string"];
				$end_time_str = $process_data["end_time_string"];
				$center_name = $process_data["center_name"];
				$booth_name = $process_data["booth_name"];
				$contact_created = $process_data["contact_created"];
				$contact_full_name = $process_data["contact_full_name"];
				$discount = $process_data["discount"];
				$discountComment = $process_data["discount_comment"];

				?>
				<div class="container">
					<h2>Результат операции "Продажа буса"</h2>
					<p><?= $contact_created == true ? "Были созданы контакт и сделка" : "Была создана сделка";  ?></p>

					<table class="table table-striped">
						<tbody>
						<tr>
							<td>Номер заказа консолидации 9-1</td><td>ID<?= $order_id?></td>
						</tr>
						<tr>
							<td>Полная стоимость аренды буса</td><td><?= $full_cost ?> тг.</td> 
						</tr>
						<tr>
							<td>Клиент в Битрикс24</td><td><a href="https://next.bitrix24.kz/crm/contact/show/<?= $contact_id ?>/" target="_blank"><?= $contact_full_name ?></a></td>
						</tr>
						<tr>
							<td>Сделка в Битрикс24</td><td><a href="https://next.bitrix24.kz/crm/deal/show/<?= $deal_id ?>/" target="_blank"><?= $deal_id ?></a></td>
						</tr>

						<tr>
							<td>Выбранный центр</td><td><?= $center_name ?></td>
						</tr>

						<tr>
							<td>Выбранный бус</td><td><?= $booth_name ?></td>
						</tr>

						<tr>
							<td>Время начала аренды</td><td><?= $_REQUEST["time"] ?></td>
						</tr>

						<tr>
							<td>Время окончания аренды</td><td><?= $end_time_str ?></td>
						</tr>

						<tr>
							<td>Продолжительность, часов</td><td><?= $_REQUEST["duration"] ?></td>
						</tr>
						<tr>
							<td>Сумма скидки</td><td><?= $discount ?></td>
						</tr>
						<tr>
							<td>Комментарий к скидке</td><td><?= $discountComment ?></td>
						</tr>
						<tr>
							<td><b>Конечная стоимость аренды</b></td><td><b><?= $cost ?> тг.</b></td>
						</tr>

						</tbody>
					</table>

				</div>

				<?php
			} else {
				?>
				<div class="container">
					<div class="alert alert-danger">
						<strong>Внимание!</strong> Возникла какая-то ошибка. Повторите попытку позднее.
					</div>
				</div>

				<?php
				//echo "<pre>".var_export($process_data, true)."</pre>";
			}

				break;
			//-------------------------------------
			//-------------------------------------



	}

?>

	<script type="text/javascript">
		$('#form').submit(function(){
			//$(this).find('input[type=submit]').prop('disabled', true);
			$("#submit").prop('disabled',true);
			$('#alert').append("<strong>Внимание!</strong> Идет обрабокта информации. Не закрывайте окно!");
		});

		$("#booth").select2();
	</script>
<?php 

	require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/footer.php");

?>
