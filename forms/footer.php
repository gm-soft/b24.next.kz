	
	

	<?php
	if ($_SESSION["user_id"] == "72" || $_SESSION["user_id"] == "30") {
		?>
		<div class="container">
			<div class="row">
				<div class="col-md-8 col-md-offset-2">
					<pre><?= "_REQUEST = ".var_export($_REQUEST, true); ?></pre>
				</div>
			</div>
		</div>
		
		<?php
	} ?>
	
	</body>
</html>
