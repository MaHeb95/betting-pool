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
$db_user = 'root';
$db_password = 'root';
$db_charset = 'utf8';

/**
 * @var PDO
 */
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=$db_charset", $db_user, $db_password);

$db = new mysqli($db_host,$db_user,$db_password,$db_name);

//define('SECRET_KEY', '114ba229215cb079d2eaa2852f3d8c22441290bdfa6175bfb0717d8ecca4e38f92e4c3a4e4beda3e053e594081ed1e545f5da966256da83e8021cf65ca91c6c51f68b56ba66008db585f0b565ceda7ad8e7ed18f71f7987908eca78facf0cdd6bc59e3bbbe2db57198b1563fc5c4f2e79d4369e78d3c815a58ec9a6a3cd3993bc2ce74f0dccb359bb9bbea5656443a691e6a63a881185a487d6c6a805ee5ac241b6e0d60492c75e6baaa607e0605796468c0260af669651d422245565827d77435533b6438a428b631a4e7beb0823909516dda0526045c1c6948d7172dae80ba53654c6fd955215ecce3b455d90bb57749a5e6ca0b7451c8a6dd79e9a69efa84');

//$host_domain = $_SERVER[HTTP_HOST];

require_once ('database.php');
