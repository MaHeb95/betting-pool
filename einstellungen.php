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
if(isset($errorMessage)) { echo $errorMessage; }


?>
<html>
<head>
</head>
<body>

<?php
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

<div class="jumbotron">
    <div class="container">
        <h1 class="display-5">Passwort ändern</h1>
        <form class="form-change-password" action="<?php echo $actual_link; ?>" method="post">
            <div class="form-group">
                <label for="altesPasswort">Altes Passwort</label>
                <input type="password" id="altesPasswort" class="form-control" name="oldpassword" placeholder="Altes Passwort">
            </div>
            <div class="form-group">
                <label for="neuesPasswort">Neues Passwort</label>
                <input type="password" class="form-control" id="neuesPasswort" name="newpassword" placeholder="Neues Passwort">
            </div>
            <div class="form-group">
                <label for="neuesPasswortwiederholen">Neues Passwort wiederholen</label>
                <input type="password" class="form-control" id="neuesPasswortwiederholen" name="newpassword2" placeholder="Neues Passwort wiederholen">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</div>

</body>
</html>