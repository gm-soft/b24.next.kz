<?php
require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");

$curr_user = BitrixHelper::getCurrentUser($_REQUEST["authId"]);
$_SESSION["user_name"] =  $curr_user["EMAIL"];
$_SESSION["user_id"] =  $curr_user["ID"];
$userId = $curr_user["ID"];

require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
$authId= $_REQUEST["authId"];
?>

    <div class="container">
        <a href="../?authId=<?= $authId?>" class="btn btn-default">В главное меню</a>
    </div>

<p>
    <iframe src="https://docs.google.com/forms/d/e/1FAIpQLSfBy9pzdHbMEEYi8NF21cVSNuEHGer1LQdMwwAvUOzCs4AGTw/viewform?embedded=true"
            width="100%" height="700" frameborder="0" marginheight="0" marginwidth="0">Загрузка...</iframe>
</p>


<?php

require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/footer.php");