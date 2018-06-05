<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 04.09.17
 * Time: 18:04
 */

session_start();
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
    $password2 = $_POST['password2'];

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
            echo 'Du wurdest erfolgreich registriert. <a href="login.php">Zum Login</a>';
            $showFormular = false;
        } else {
            echo 'Beim Abspeichern ist leider ein Fehler aufgetreten<br>';
        }
    }
}

if($showFormular) {
    ?>

    <form action="?register=1" method="post">
        Username:<br>
        <input type="username" size="40" maxlength="250" name="username"><br><br>

        Dein Passwort:<br>
        <input type="password" size="40"  maxlength="250" name="password"><br>

        Passwort wiederholen:<br>
        <input type="password" size="40" maxlength="250" name="password2"><br><br>

        <input type="submit" value="Abschicken">
    </form>

    <?php
} //Ende von if($showFormular)
?>

</body>
</html>