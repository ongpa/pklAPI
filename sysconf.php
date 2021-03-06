<?php
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
    die("can not access this file directly");
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

error_reporting(E_ERROR);

// timezone
date_default_timezone_set('Asia/Jakarta');

// database related
$servername = "";
$username = "";
$password = "";
$db_name = "";

// create connection
$conn = new mysqli($servername, $username, $password, $db_name);

// check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
?>
