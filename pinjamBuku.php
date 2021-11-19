<?php
define("INDEX_AUTH", "1");

require_once('./sysconf.php');
require_once('./simbio_date.php');
require_once('./user.php');
require_once('./sirkulasi.php');

session_start();
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
			$circulation = new sirkulasi($conn);

			// set holiday rules
			$circulation->holiday_dayname = $_SESSION["holiday_dayname"];
			$circulation->holiday_date = $_SESSION["holiday_date"];

			// check if not already loaned
			$item_status_q = $conn->query("SELECT loan_id FROM loan WHERE item_code='" .$_SESSION["scanned"]["item_code"]. "' member_id='" .$_SESSION["user"]->member_id. "' AND is_lent=1 AND is_return=0");
			if ($item_status_q->num_rows > 0) {
				$data["msg"] = "Anda sedang meminjam item ini, silahkan hubungi pustakawan untuk mengembalikan atau memperpanjang peminjaman";
			} else {
				// new loan
				$data["msg"] = $circulation->loanItem($_SESSION["scanned"]);
			}
			// unset scanned after finish
			unset($_SESSION["scanned"]);
		} elseif (isset($_POST["itemCode"]) && $_POST["operation"] == "s") {
			// get info of scanned item
			$item_code = $_POST["itemCode"];
			if ($_SESSION["scanned"] = sirkulasi::getScannedInfo($item_code, $conn)) {
				$data["title"] = $_SESSION["scanned"]["title"];
				$data["item_code"] = $_SESSION["scanned"]["item_code"];
			} else {
				$data["msg"] = "Item tidak ditemukan";
			}
		} else {
			$data["msg"] = "unknown operation";
		}
	}
} else {
	$data["login_status"] = "false";
}

echo json_encode($data);
?>
