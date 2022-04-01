<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 05.09.17
 * Time: 12:21
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

$is_admin = (bool) (get_user($userid)['admin']);

if(trim($_POST["oldpassword"]) !== "") {
    $oldpassword = $_POST['oldpassword'];
    $newpassword = $_POST['newpassword'];
    $newpassword2 = $_POST['newpassword2'];

    //Überprüfung des Passworts
    $statement = $pdo->prepare("SELECT * FROM user WHERE id=:id");
    $statement->bindValue(':id', $userid, PDO::PARAM_INT);
    $statement->execute();
    $user = $statement->fetchAll();
    if ($user !== false && password_verify($oldpassword, $user[0]['password'])) {


        if(strlen($newpassword) == 0) {
            echo 'Bitte ein Passwort angeben<br>';
            $errorMessage = "Neues Passwort eingeben!<br>";
        } elseif($newpassword != $newpassword2) {
            echo 'Die Passwörter müssen übereinstimmen!<br>';
            $errorMessage = "Passwörter müssen übereinstimmen!<br>";
        } else {
            $newpassword_hash = password_hash($newpassword, PASSWORD_DEFAULT);
            $statement = $pdo->prepare("UPDATE ".$db_name.".user SET password=:password WHERE id=$userid");
            $statement->bindValue(':password', $newpassword_hash, PDO::PARAM_STR);
            $result = $statement->execute();
            echo "Passwort erfolgreich geändert!";
        }

    } else {
        $errorMessage = "Altes Passwort war ungültig!<br>";
    }
}

if (trim($_POST["new_season_name"]) !== "" && trim($_POST["season_bet_type"]) !== "") {

    $season_settings = array();
    if ($_POST["tendency_check"] == "true") {
        $season_settings['tendency'] = $_POST["tendency_points"];
    }
    if ($_POST["difference_check"] == "true" && strpos(trim($_POST["season_bet_type"]), 'result') !== true) {
        $season_settings['difference'] = $_POST["difference_points"];
    }
    if ($_POST["correct_check"] == "true" && strpos(trim($_POST["season_bet_type"]), 'result') !== true) {
        $season_settings['correct'] = $_POST["correct_points"];
    }
    create_season(trim($_POST["new_season_name"]), trim($_POST["season_bet_type"]), $season_settings);
}

if(isset($errorMessage)) { echo $errorMessage; }

$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>

<?php if ($is_admin) { ?>
<div class="jumbotron">
    <div class="container">
        <h1 class="display-5">Tipprunden verwalten</h1>
        <p>Hier können Sie Tippgruppen erstellen bzw. deren User und Saisons verwalten.</p>
        <p><a class="btn btn-primary btn-lg" href="tipprunden.php" role="button">Tipprunden »</a></p>
    </div>
</div>
<?php } ?>

<?php if ($is_admin) { ?>
    <div class="jumbotron">
        <div class="container">
            <h1 class="display-5">Nutzer verwalten</h1>
            <p>Hier können Sie Nutzer erstellen bzw verwalten.</p>
            <p><a class="btn btn-primary btn-lg" href="user_administration.php" role="button">Nutzer verwalten »</a></p>
        </div>
    </div>
<?php } ?>

<?php if ($is_admin) { ?>
    <div class="jumbotron">
        <div class="container">
            <form action="<?php echo $actual_link; ?>" method="post">
                <h1 class="display-5">Neue Saison</h1>
                <div class="form-label-group">
                    <input id="new_season_name" type="text" class="form-control" name="new_season_name" placeholder="Saison Name">
                    <label for="new_season_name">Saison Name</label>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <fieldset class="form-group">
                            <h4>Wettentyp</h4>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="season_bet_type" id="bet_type_1" value="winner" checked>
                                <label class="form-check-label" for="bet_type_1">Gewinner</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="season_bet_type" id="bet_type_2" value="result_fulltime">
                                <label class="form-check-label" for="bet_type_2">Ergebnis nach Regelspielzeit</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="season_bet_type" id="bet_type_3" value="result">
                                <label class="form-check-label" for="bet_type_3">Endergebnis (nach Verlängerung)</label>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col-md-6">
                        <h4>Punktevergabe</h4>
                        <div class="form-check">
                            <div class="row">
                                <div class="col-6">
                                    <input class="form-check-input" type="checkbox" name="tendency_check" id="tendency_check" value="true" checked>
                                    <label class="form-check-label" for="tendency_check">Korrekte Tendenz</label>
                                </div>

                                <div class="col-6">
                                    <div class="form-label-group">
                                        <input id="tendency_points" type="number" class="form-control" name="tendency_points" placeholder="Punkte" value="1">
                                        <label for="tendency_points">Punkte</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-check">
                            <div class="row">
                                <div class="col-6">
                                    <input class="form-check-input" type="checkbox" name="difference_check" id="difference_check" value="true">
                                    <label class="form-check-label" for="difference_check">Korrekte Differenz<br/>(nur für Ergebnistipps)</label>
                                </div>

                                <div class="col-6">
                                    <div class="form-label-group">
                                        <input id="difference_points" type="number" class="form-control" name="difference_points" placeholder="Punkte" value="2">
                                        <label for="difference_points">Punkte</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-check">
                            <div class="row">
                                <div class="col-6">
                                    <input class="form-check-input" type="checkbox" name="correct_check" id="correct_check" value="true">
                                    <label class="form-check-label" for="correct_check">Korrektes Ergebnis<br/>(nur für Ergebnistipps)</label>
                                </div>

                                <div class="col-6">
                                    <div class="form-label-group">
                                        <input id="correct_points" type="number" class="form-control" name="correct_points" placeholder="Punkte" value="3">
                                        <label for="correct_points">Punkte</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Speichern</button>
            </form>
        </div>
    </div>
<?php } ?>

<div class="jumbotron">
    <div class="container">
        <h1 class="display-5">Passwort ändern</h1>
        <form class="form-change-password" action="<?php echo $actual_link; ?>" method="post">
            <div class="form-label-group">
                <input type="password" id="altesPasswort" class="form-control" name="oldpassword" placeholder="Altes Passwort">
                <label for="altesPasswort">Altes Passwort</label>
            </div>
            <div class="form-label-group">
                <input type="password" class="form-control" id="neuesPasswort" name="newpassword" placeholder="Neues Passwort">
                <label for="neuesPasswort">Neues Passwort</label>
            </div>
            <div class="form-label-group">
                <input type="password" class="form-control" id="neuesPasswortwiederholen" name="newpassword2" placeholder="Neues Passwort wiederholen">
                <label for="neuesPasswortwiederholen">Neues Passwort wiederholen</label>
            </div>
            <button type="submit" class="btn btn-primary">Speichern</button>
        </form>
    </div>
</div>

<?php
require('view.footer.php');