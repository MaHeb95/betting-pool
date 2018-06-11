<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 04.09.17
 * Time: 17:06
 */

session_start();
session_destroy();

require ("view.header.php");
?>

<div class="jumbotron">
    <div class="container">
        <h1 class="display-5">Logout erfolgreich!</h1>
        <form class="form-signin">
            <p><a class="btn btn-primary btn-lg" href="login.php" role="button">Log in  »</a></p>
        </form>
    </div> <!-- /container -->
</div>

<?php
require('view.footer.php');