<?php
define("INDEX_AUTH", "1");

require_once('./sysconf.php');
require_once('./user.php');

session_start();

// check if login
if(isset($_SESSION["user"]) && $_SESSION["user"]->is_member) {
	if (isset($_POST["oldPass"]) && isset($_POST["newPass"]) && isset($_POST["confPass"])) {
		// secure the input
		$old_pass = $conn->real_escape_string($_POST["oldPass"]);
		$new_pass = $conn->real_escape_string($_POST["newPass"]);
		$conf_new_pass = $conn->real_escape_string($_POST["confPass"]);
		// try to change the password
		$data["msg"] = $_SESSION["user"]->changePassword($old_pass, $new_pass, $conf_new_pass);
	} else {
		$data["msg"] = "Semua label harus diisi";
	}
} else {
	$data["login_status"] = "false";
}

echo json_encode($data);
?>
