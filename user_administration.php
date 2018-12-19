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

foreach (all_users() as $user) {
    if (isset($_POST['delete_'.$user['id']])) {
        delete_user($user['id']);
    }
}

if(isset($_GET['register'])) {
    $error = false;
    $username = $_POST['username'];
    $displayname = $_POST['displayname'];
    $password = $_POST['password'];
    $password2 = $_POST['password-repeat'];

    if(empty($username)) {
        $erroralert = 1;
        $error = true;
    }
    if(empty($displayname)) {
        $erroralert = 2;
        $error = true;
    }
    if(strlen($password) == 0) {
        $erroralert = 3;
        $error = true;
    }
    if($password != $password2) {
		$erroralert = 4;        
		$error = true;
    }
    if(!$error) {
    	if (create_user($username,$displayname,$_POST['email'],$password) == TRUE) {
    		$erroralert = 5; 	
    	} else {
    		$erroralert = 6; 
    	}

    }
}
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>
<script>
	function alertRegisterErrors() {
		/*switch ($erroralert) {
    		case 1:
        		alert("Bitte geben Sie einen Username ein!");
        	break;
        	case 2:
        		alert("Bitte geben Sie einen Displayname ein!");
        	break;
        	case 3:
        		alert("Bitte geben Sie einen Passwort ein!");
        	break;
        	case 4:
        		alert("Die Passwörter stimmen nicht überein!");
        	break;
        	case 5:
        		alert("Der User wurde erfolgreich registriert!");
        	break;
        	case 6:
        		alert("Der User existiert bereits!");
        	break;
    
		}*/
		alert("Der User wurde erfolgreich registriert!");
    }
</script>


<form action="<?php echo $actual_link; ?>" method="post">
<table class="table">
	<thead class="thead-dark">
		<tr>
			<th>ID</th>
			<th>User</th>
		    <th>Anzeigename</th>
		    <th>E-mail</th>
			<th>Erstellt am</th>
			<th>Admin?</th>
			<th>Aktion</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach (all_users() as $user) { 
		echo "<tr>";
		echo "<td>" . $user['id'] . "</td>";
		echo "<td>" . $user['username'] . "</td>";
		echo "<td>" . $user['displayname'] . "</td>";
		echo "<td>" . $user['email'] . "</td>";
		echo "<td>" . $user['created_at'] . "</td>";
		if ($user['admin'] == TRUE) {
			echo "<td>✓</td>";
		} else {echo "<td></td>";}
		echo "<td>";
		$user_id = $user['id'];
		echo "<button onclick='return confirmDelete()' type='submit' class='btn btn-primary' name='delete_$user_id' value='1'>Löschen</button>";
		echo '&nbsp;&nbsp;&nbsp;';
		$usermenu = $user['id'];
		echo "<a href='user_update.php?user=$usermenu' class='btn btn-primary' role='button' aria-pressed='true'>Update</a>";
		echo "</td>";
		echo "</tr>";

		} ?>
	</tbody>
</table>
</form>
<script>
	function confirmDelete() {
		return confirm("Wollen Sie den User endgültig löschen?");
	}
</script>

    
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
                            <input type="text" id="displayname" class="form-control" name="displayname" placeholder="Anzeigename" required>
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
                <button onclick='return alertRegisterErrors()' type="submit" class="btn btn-primary">Speichern</button>
            </form>
        </div>
    </div>

<?php require('view.footer.php');
