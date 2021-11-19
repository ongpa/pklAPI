<?php
define("INDEX_AUTH", "1");

require_once('./sysconf.php');
require_once('./user.php');

session_start();
// login by creating user instance
$_SESSION["user"] = new user($conn, $user_id, $password);

// check if success
if ($_SESSION["user"]->is_member) {
	$data["login_status"] = "true";
	$data["member_id"] = $_SESSION["user"]->member_id;
	$data["member_name"] = $_SESSION["user"]->member_name;
	$data["expire_date"] = $_SESSION["user"]->expire_date;
	$data["is_expired"] = $_SESSION["user"]->is_expired;

	// load holiday from database
	$_SESSION["holiday_dayname"] = array();
	$holiday_dayname_q = $conn->query("SELECT holiday_dayname FROM holiday WHERE holiday_date IS NULL");
	while ($holiday_dayname_d = $holiday_dayname_q->fetch_row()) {
		$_SESSION["holiday_dayname"][] = $holiday_dayname_d[0];
	}

	$_SESSION["holiday_date"] = array();
	$holiday_date_q = $conn->query("SELECT holiday_date FROM holiday WHERE holiday_date IS NOT NULL ORDER BY holiday_date LIMIT 365");
	while ($holiday_date_d = $holiday_date_q->fetch_row()) {
		$_SESSION['holiday_date'][$_holiday_date_d[0]] = $_holiday_date_d[0];
	}
} else {
	$data["login_status"] = "false";
}

echo json_encode($data);
?>
