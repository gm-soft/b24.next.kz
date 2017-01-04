	
	

	<?php
	if ($_SESSION["user_id"] == "72" || $_SESSION["user_id"] == "30") {
		?>
		<!--div class="container">
			<div class="row">
				<div class="col-md-8 col-md-offset-2">
					<pre><?= "_REQUEST = ".var_export($_REQUEST, true); ?></pre>
				</div>
			</div>
		</div-->

	<div class="container">
		<div class="panel-group">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" href="#collapse1">_REQUEST</a>
					</h4>
				</div>
				<div id="collapse1" class="panel-collapse collapse">
					<div class="panel-body">

                        <?php
                        $_REQUEST["REQUEST_URI"] = $_SERVER['REQUEST_URI'];
                        ?>
						<pre><?= var_export($_REQUEST, true); ?></pre>
						
					</div>
				</div>
			</div>
		</div>
	</div>
		
		<?php
	} ?>
	
	</body>
</html>
