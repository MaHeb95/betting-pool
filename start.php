<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 04.09.17
 * Time: 17:05
 */
//Check Login
require ("view.nologin.php");

//Abfrage der Nutzer ID vom Login
$userid = $_SESSION['userid'];

//Ausgabe des internen Startfensters
require ("view.header.php");
require ("view.navbar.php");
require ("config.php");

$statement = $pdo->prepare("SELECT username FROM " . $db_name . ".user WHERE id =" . $userid);
$statement->execute();
$user = $statement->fetch(PDO::FETCH_ASSOC)['username'];

echo "Hallo ".$user."! Dein Login war erfolgreich!";
?>