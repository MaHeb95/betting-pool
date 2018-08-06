<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 04.09.17
 * Time: 17:05
 */
//Check Login
require ("view.nologin.php");

//Abfrage der Nutzer ID vom Login
$userid = $_SESSION['userid'];

//Ausgabe des internen Startfensters
require ("view.header.php");
require ("view.navbar.php");
require ("config.php");
require ("match.php");
require ("bet.php"); //for get_user only

$statement = $pdo->prepare("SELECT displayname FROM " . $db_name . ".user WHERE id =" . $userid);
$statement->execute();
$user = $statement->fetch(PDO::FETCH_ASSOC)['displayname'];

$betgroups = get_betgroups_from_user($userid);
?>

    <div class="jumbotron">
        <div class="container">
            <h1 class="display-5">Hallo <?php echo $user; ?>!</h1>
            <h5><?php echo '&nbsp;'; ?></h5>
            <h6>Dein Login war erfolgreich!</h6>
            <h5><?php echo '&nbsp;'; ?></h5>
            <!--<h6>Du wurdest folgender Tipprunde hinzugefügt:
                <?php /*echo '&nbsp;'; foreach ($betgroups AS $row) { echo $row['name']; echo ',&nbsp;'; } */?>
            </h6>
            <h5><?php /*echo '&nbsp;'; */?></h5>-->
            <h6>Direkt zum Tippspiel der Bundesliga-Saison 2018-2019 geht es hier entlang!<?php echo '&nbsp;'; ?> </h6>
            <p><a class="btn btn-primary btn-lg" href="tipps.php?season=3" role="button">Bundesliga 2018-2019 »</a></p>
        </div>
    </div>
<?php
require('view.footer.php');