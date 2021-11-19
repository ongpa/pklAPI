<?php
define("INDEX_AUTH", "1");

require_once('./sysconf.php');
require_once('./user.php');

// Algorithm
if(isset($_SESSION["user"]) && $_SESSION["user"]->is_member) {
	if (isset($_POST["oldPass"]) && isset($_POST["newPass"]) && isset($_POST["confPass"])) {
		$old_pass = $_POST["oldPass"];
		$new_pass = $_POST["newPass"];
		$conf_new_pass = $_POST["confPass"];
		$data["msg"] = $_SESSION["user"]->changePassword($old_pass, $new_pass, $conf_new_pass);
	} else {
		$data["msg"] = "Semua label harus diisi";
	}
} else {
	$data["login_status"] = "false";
}

echo json_encode($data);
?>
