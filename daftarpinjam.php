<?php
define("INDEX_AUTH", "1");

require_once('./sysconf.php');
require_once('./user.php');

session_start();

// check if login
if (isset($_SESSION["user"]) && $_SESSION["user"]->is_member) {
	// get loan list
	$data = $_SESSION["user"]->getItemLoan();
} else {
	$data["login_status"] = "false";
}

echo json_encode($data);
?>
