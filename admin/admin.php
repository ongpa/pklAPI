<?php
define("INDEX_AUTH", "1");

require_once('../sysconf.php');

// variables
$_POST["username"] = "admin";
$_POST["password"] = "admin";
$username = $_POST["username"];
$password = $_POST["password"];

// check if trying to login
if (!isset($_SESSION["username"]) || !isset($_SESSION["realname"])) {
	if(isset($username) && isset($password)) {
		// login
		$login_q = $conn->query("SELECT username, realname FROM user
			WHERE username='$username' AND passwd=MD5('$password')");
		if ($login_d = $login_q->fetch_assoc()) {
			$_SESSION["username"] = $login_d["username"];
			$_SESSION["realname"] = $login_d["realname"];
			$data["login_admin"] = "true";
		} else {
			$data["login_admin"] = "false";
		}
	}
}

// check if logged in
if (isset($_SESSION["username"]) && isset($_SESSION["realname"])) {
	// get list of biblio items
	$books = array();
	$items_q = $conn->query("SELECT b.biblio_id, b.title, i.item_code
		FROM biblio AS b
		LEFT JOIN item AS i ON b.biblio_id=i.biblio_id");
	while ($items_d = $items_q->fetch_assoc()) {
		$books[$items_d["biblio_id"]]["title"] = $items_d["title"];
		$books[$items_d["biblio_id"]]["item_code"][] = $items_d["item_code"];
	}
	$data["books"] = $books;
}

echo json_encode($data);
?>
