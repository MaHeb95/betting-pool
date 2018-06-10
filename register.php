<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 04.09.17
 * Time: 18:04
 */

//Check Login
require ("view.nologin.php");

//Abfrage der Nutzer ID vom Login
$userid = $_SESSION['userid'];

//Ausgabe des internen Startfensters
require ("view.header.php");
require ("view.navbar.php");

//session_start();
require_once("config.php");

//$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'root');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registrierung</title>
</head>
<body>

<?php
$showFormular = true; //Variable ob das Registrierungsformular anezeigt werden soll

if(isset($_GET['register'])) {
    $error = false;
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password2 = $_POST['password-repeat'];

    if(empty($username)) {
        echo 'Bitte eine Username eingeben<br>';
        $error = true;
    }
    if(strlen($password) == 0) {
        echo 'Bitte ein Passwort angeben<br>';
        $error = true;
    }
    if($password != $password2) {
        echo 'Die Passwörter müssen übereinstimmen<br>';
        $error = true;
    }

    //Überprüfe, dass der Username noch nicht registriert wurde
    if(!$error) {
        $statement = $pdo->prepare("SELECT * FROM user WHERE username = :username");
        $result = $statement->execute(array('username' => $username));
        $user = $statement->fetch();

        if($user !== false) {
            echo 'Dieser Username ist bereits vergeben<br>';
            $error = true;
        }
    }

    //Keine Fehler, wir können den Nutzer registrieren
    if(!$error) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $statement = $pdo->prepare("INSERT INTO user (username, password) VALUES (:username, :password)");
        $result = $statement->execute(array('username' => $username, 'password' => $password_hash));

        if($result) {
            echo 'User wurde erfolgreich registriert. <a href="einstellungen.php">Zu Settings!</a>';
            $showFormular = true;
        } else {
            echo 'Beim Abspeichern ist leider ein Fehler aufgetreten<br>';
        }
    }
}

if($showFormular) {
    ?>
    <div class="jumbotron">
        <div class="container">
            <h1 class="display-5">Nutzer erstellen</h1>
            <form action="?register=1" method="post">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-label-group">
                            <input type="text" id="username" class="form-control" name="username" placeholder="Username" required>
                            <label for="username">Username</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-label-group">
                            <input type="text" id="displayname" class="form-control" name="displayname" placeholder="Anzeigename">
                            <label for="displayname">Anzeigename</label>
                        </div>
                    </div>
                </div>
                <div class="form-label-group">
                    <input type="email" id="email" class="form-control" name="email" placeholder="Email">
                    <label for="email">Email</label>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-label-group">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Passwort" required>
                            <label for="password">Passwort</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-label-group">
                            <input type="password" class="form-control" id="password-repeat" name="password-repeat" placeholder="Passwort wiederholen" required>
                            <label for="password-repeat">Passwort wiederholen</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Speichern</button>
            </form>
        </div>
    </div>

    <!--<form action="?register=1" method="post">
        Username:<br>
        <input type="username" size="40" maxlength="250" name="username"><br><br>

        Dein Passwort:<br>
        <input type="password" size="40"  maxlength="250" name="password"><br>

        Passwort wiederholen:<br>
        <input type="password" size="40" maxlength="250" name="password2"><br><br>

        <input type="submit" value="Abschicken">
    </form>-->

    <?php
} //Ende von if($showFormular)
?>

</body>
</html>