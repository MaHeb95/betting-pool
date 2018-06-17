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

/**
 * @var PDO
 */
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=$db_charset", $db_user, $db_password);

$db = new mysqli($db_host,$db_user,$db_password,$db_name);

define('SECRET_KEY', '17d81c75e70a92924e135e01cef60f39937a8f88aa00ec55baf105512a8ff6f8f3bfeb10a3377d308494645428ce1f0cb99c244ccd8eea1c386e5d8fb0ef8d773ad3ad7b26c1a7915154021863958718cfe37f84631f3aa1f926bf760c9cafdb5620d0a47624be5a402c0634a2b9b725fac18ef4f0f4cc31eac3b833a09bff3241e89e2b08c6ca63d1ccbd2713338dc388e9ab892aef48f3bfa4475292668904575ee71bfd3fd186dc4ff9677dec9c98db735c1ae2ff62dac0e869700af2f58ff493e517ba31bce0613848b64f95058f16ba37fb8753b7e25a139792ee8f6c4dcaf171bd6146a2e4591e514133e43da4eba6ca25483cbad679ba6ca45f6355f9');

$host_domain = $_SERVER[HTTP_HOST];

require_once ('database.php');
