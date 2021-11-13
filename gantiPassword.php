<?php
require_once('./sysconf.php');

// Variables
$old_pass = $_POST["old_pass"];
$new_pass = $_POST["new_pass"];
$conf_new_pass = $_POST["conf_new_pass"];

// Algorithm
if(isset($_SESSION["user"]) && $_SESSION["user"]->is_member) {
	$data["msg"] = $_SESSION["user"]->changePassword($old_pass, $new_pass, $conf_new_pass);
} else {
	$data["login_status"] = "false";
}

echo json_encode($data);
?>
