<?php
    require ($_SERVER["DOCUMENT_ROOT"]."/include/config.php");

    $action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
    $authId = isset($_REQUEST["authId"]) ? $_REQUEST["authId"] : null;
    if (is_null($authId)) {
        redirect("../sales/index.php");
    }



    $adminAuthToken = isset($_REQUEST["adminToken"]) ? $_REQUEST["adminToken"] : ApplicationHelper::readAccessData(true);
    //$userId = "72";

    $curr_user = BitrixHelper::getCurrentUser($authId);
    $_SESSION["user_name"] =  $curr_user["EMAIL"];
    $_SESSION["user_id"] =  $curr_user["ID"];
    $userId = $curr_user["ID"];

    $actionPerformed = isset($_REQUEST["actionPerformed"]) ? $_REQUEST["actionPerformed"] : "initiated";
    switch ($actionPerformed) {
        case "initiated":

            $companies = BitrixHelper::getCompanies($userId, $adminAuthToken);

            require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
            ?>
            <div class="container">
                <h1>Корпоративная продажа</h1>
                <form id="form" class="form-horizontal" method="post" action="">

                    <input type="hidden" name="action" value="<?= $action ?>">
                    <input type="hidden" name="authId" value="<?= $authId ?>">
                    <input type="hidden" name="actionPerformed" value="initiated">
                    <input type="hidden" name="adminToken" value="<?= $adminAuthToken ?>">

                    <?php
                    require_once $_SERVER["DOCUMENT_ROOT"]."/sales/search/searchFields.php";
                    ?>

                    <div class=" form-group">
                        <div class="dropdown div-inline">
                            <a class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">Дополнительные действия
                            <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="https://b24.next.kz/sales/company.php?authId=<?= $authId ?>&action=<?=$action?>&actionPerformed=company_create">Создать новую компанию</a>
                                </li>
                                <li>
                                    <a href="<?= $url ?>"><?= $urlHeader ?></a>
                                </li>
                                <li>
                                    <a href="/sales/index.php?authId=<?= $authId ?>">Отменить</a>
                                </li>
                            </ul>
                        </div>
                        <button id="submit" type="submit" class="btn btn-primary">Далее</button>
                    </div>
                    
                        
                    

                </form>
            </div>

            <?php
            break;
    }
?>

<?php




?>


<script>
    $('#form').submit(function(){
        $("#submit").prop('disabled',true); //
        $("a").addClass('disabled');
    });
</script>
<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/footer.php");
?>
