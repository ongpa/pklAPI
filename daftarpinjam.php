<?php
define("INDEX_AUTH", "1");

require_once('./sysconf.php');
require_once('./user.php');

// Variables
// $uid = $_GET['u'];
$_SESSION["member_id"] = 'memb-01';
$_SESSION["user"] = new user($conn, "memb-01", "password");

// session_start();
// check if login
if (isset($_SESSION["user"]) && $_SESSION["user"]->is_member) {
	$data = $_SESSION["user"]->getItemLoan();
} else {
	$data["login_status"] = "false";
}

echo json_encode($data);
?>
