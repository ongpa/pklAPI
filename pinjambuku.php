<?php
define("INDEX_AUTH", "1");

require_once('./sysconf.php');
require_once('./simbio_date.php');
require_once('./user.php');
require_once('./sirkulasi.php');

// variables
$_POST["item_code"] = "B01";

// algorithm
// session_start();

// check if login
if (isset($_SESSION["user"]) && $_SESSION["user"]->is_member) {
	// cek apakah member expire atau pending
	if ($_SESSION["user"]->is_expired) {
		$data["msg"] = "Tidak dapat meminjam karena akun anda sudah expired";
	} elseif ($_SESSION["user"]->is_pending) {
		$data["msg"] = "Tidak dapat meminjam karena akun anda sedang pending";
	} else {
		// cek operasi
		if (isset($_SESSION["scanned"]) && $_POST["operation"] == "p") {
			// create sirkulasi instance
			$circulation = new sirkulasi();

			// set holiday rules
			$circulation->holiday_dayname = $_SESSION["holiday_dayname"];
			$circulation->holiday_date = $_SESSION["holiday_date"];

			// check if not already loaned
			$item_status_q = $conn->query("SELECT loan_id FROM loan WHERE item_code='" .$item_code. "' member_id='" .$_SESSION["user"]->member_id. "' AND is_lent=1 AND is_return=0");
			if ($item_status_q->num_rows > 0) {
				$data["msg"] = "Anda sedang meminjam item ini, silahkan hubungi pustakawan untuk mengembalikan atau memperpanjang peminjaman";
			} else {
				// new loan
				$data["msg"] = $circulation->loanItem($_SESSION["scanned"]);
			}
			// unset scanned after finish
			unset($_SESSION["scanned"]);
		} elseif (isset($_SESSION["scanned"]) && $_POST["operation"] == "c") {
			// unset scanned if cancelled
			unset($_SESSION["scanned"]);
		} else {
			// get info of scanned item
			$item_code = $_POST["item_code"];
			if ($_SESSION["scanned"] = sirkulasi::getScannedInfo($item_code, $conn)) {
				$data["title"] = $_SESSION["scanned"]["title"];
			} else {
				$data["msg"] = "Item tidak ditemukan";
			}
		}
	}
} else {
	$data["login_status"] = "false";
}

echo json_encode($data);
?>
