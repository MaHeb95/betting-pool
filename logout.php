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
<html>
<body>
<link href="/css/signin.css" rel="stylesheet">
<div class="container" class="parent">
    <form class="form-signin">
        <h2 class="form-signin-heading">Logout erfolgreich!</h2>
        <p class="text-center"><a  class="btn  btn-primary"  href="login.php">Log in  »</a></p>
    </form>
</div> <!-- /container -->
</body>
</html>
