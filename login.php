<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 04.09.17
 * Time: 12:52
 */
session_start();
require_once("config.php");

if(isset($_GET['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $statement = $pdo->prepare("SELECT * FROM user WHERE username = :username");
    $result = $statement->execute(array('username' => $username));
    $user = $statement->fetch();

    //Überprüfung des Passworts
    if ($user !== false && password_verify($password, $user['password'])) {
        $_SESSION['userid'] = $user['id'];
        header("Location: start.php");
        //echo "<a href='http://$host_domain/tipps.php' class='btn btn-primary' role='button' aria-pressed='true'>Zum Tippspiel</a>";
        exit();
    } else {
        $errorMessage = "Username oder Passwort war ungültig<br>";
    }

}
require ("view.header.php");
?>

<?php
if(isset($errorMessage)) {
    echo $errorMessage;
}
?>
<html>
<body class="body-signin">

    <form class="form-signin" action="?login=1" method="post">
        <div class="text-center mb-4">
            <h2 class="h3 mb-3 font-weight-normal">Anmeldung</h2>
        </div>
        <div class="form-label-group">
            <input type="text" id="eingabefeldUsername" class="form-control" placeholder="Username" required autofocus name="username">
            <label for="eingabefeldUsername">Username</label>
        </div>
        <div class="form-label-group">
            <input type="password" id="eingabefeldPasswort" class="form-control" placeholder="Passwort" required name="password">
            <label for="eingabefeldPasswort">Passwort</label>
        </div>

        <div class="checkbox">
        </div>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Anmelden</button>
    </form>

<!-- IE10-Anzeigefenster-Hack für Fehler auf Surface und Desktop-Windows-8 -->
<script src="../../assets/js/ie10-viewport-bug-workaround.js"></script>

</body>
</html>
