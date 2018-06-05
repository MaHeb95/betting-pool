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


?>
<html>
<head>
</head>
<body>
<?php //if ($is_admin) { ?>

<div class="jumbotron">
    <div class="container">
        <h1 class="display-5">Tipprunden verwalten</h1>
        <p>Hier können Sie Tippgruppen erstellen bzw. deren User und Saisons verwalten.</p>
        <p><a class="btn btn-primary btn-lg" href="tipprunden.php" role="button">Tipprunden »</a></p>
    </div>
</div>

<?php //} ?>

</body>
</html>