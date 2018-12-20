<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 11.12.18
 * Time: 23:21
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

if (!$is_admin) {
    echo "Admin-Bereich!";
    die;
}

$usermenu = null;
if (isset($_GET["user"]) && is_numeric($_GET["user"])) {
    $usermenu = $_GET["user"];
}

if(empty($_POST) == FALSE) {
	
    $error = false;
    $password = $_POST['password'];
    $password2 = $_POST['password-repeat'];

    if (isset($password)) {
    	if($password != $password2) {        
		$error = true;
    	}
    }
    if ($_POST['admin'] == 'on') {
    	$admin = TRUE;
    } else {
    	$admin = FALSE;
    }
    if(!$error) {
    	if (update_user($usermenu,$_POST['username'],$_POST['displayname'],$_POST['email'],$password, $admin) == TRUE) {
            //echo 'User wurde erfolgreich registriert. <a href="einstellungen.php">Zu Settings!</a>';
            $showFormular = true;
        } else {
            //echo 'Beim Abspeichern ist leider ein Fehler aufgetreten.<br>';
    	}
		//echo 'Beim Abspeichern ist leider ein Fehler aufgetreten.<br>';
    }
}


?>
<html>
<head>
    <script type="text/javascript">
        /**
         * You can have a look at https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/with * for more information on with() function.
         */
        function autoSubmit_user()
        {
            with (window.document.form) {
                /**
                 * We have if and else block where we check the selected index for Useregory(User) and * accordingly we change the URL in the browser.
                 */
                if (user.selectedIndex === 0) {
                    window.location.href = 'user_update.php';
                } else {
                    window.location.href = 'user_update.php?user=' + user.options[user.selectedIndex].value;
                }
            }
        }

    </script>
</head>
<body>
<?php
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>

<?php 
$user = get_user($usermenu); ?>
<div class="jumbotron">
        <div class="container">
            <h1 class="display-5">Nutzer ändern</h1>
            <form action="?user=<?php echo $usermenu; ?>" method="post">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                        	<label for="username">Username</label>
                            <input type="text" id="username" class="form-control" name="username" placeholder="<?php echo $user[username];?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                        	<label for="displayname">Anzeigename</label>
                            <input type="text" id="displayname" class="form-control" name="displayname" placeholder="<?php echo $user[displayname];?>">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                	<label for="email">Email</label>
                    <input type="email" id="email" class="form-control" name="email" placeholder="<?php echo $user[email];?>">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                        	<label for="password">Passwort</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Passwort" >                            
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                        	<label for="password-repeat">Passwort wiederholen</label>
                            <input type="password" class="form-control" id="password-repeat" name="password-repeat" placeholder="Passwort wiederholen" >                            
                        </div>
                    </div>
                </div>
                <div class="form-check">
                	<input type="checkbox" id="admin" class="form-check-input" name="admin" <?php if($user[admin] == TRUE) {echo "checked";}?>>
                	<label for="admin">Admin</label>
                </div>
                <button onclick='return confirmUpdate()' type="submit" class="btn btn-primary">Speichern</button>
                <a href='user_administration.php' class='btn btn-primary' role='button' aria-pressed='true'>Zurück</a>
            </form>
        </div>
    </div>

<script>
	function confirmUpdate() {
		return confirm("Wollen Sie die Daten endgültig ändern?");
	}
</script>

<?php require('view.footer.php');