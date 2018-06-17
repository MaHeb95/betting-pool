<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 04.09.17
 * Time: 17:06
 */

require_once('libraries/authentication.php');
session_start();
session_destroy();
destroy_current_token();

require ("view.header.php");
?>

<body class="body-signin">
    <div class="text-center container">
        <h1 class="display-5">Logout erfolgreich!</h1>
        <form class="form-signin">
            <p><a class="btn btn-primary btn-lg" href="login.php" role="button">Log in »</a></p>
        </form>
    </div> <!-- /container -->

<?php
require('view.footer.php');