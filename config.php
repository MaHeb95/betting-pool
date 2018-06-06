<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 04.09.17
 * Time: 18:26
 */

//Tragt hier eure Verbindungsdaten zur Datenbank ein
$db_host = 'localhost';
$db_name = 'betting_pool';
$db_user = 'betting-pool';
$db_password = 'bets2018';
$db_charset = 'utf8';
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=$db_charset", $db_user, $db_password);
$db = new mysqli($db_host,$db_user,$db_password,$db_name);

$host_domain = $_SERVER[HTTP_HOST];

require_once ('database.php');
