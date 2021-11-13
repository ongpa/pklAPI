<?php
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
    die("can not access this file directly");
}

class sirkulasi {
	public $holiday_dayname = array('Sun');
	public $holiday_date = array();
	public $loan_have_overdue = false;
	protected $obj_memb = false;
	protected $obj_db = false;
	protected $loan_limit = 0;
	protected $loan_periode = 0;
	protected $reborrow_limit = 0;
	protected $fine_each_day = 0;
	protected $item_loan_rules = 0;
	protected $grace_periode = 0;

	// class constructor
	public function __construct($obj_db) {
		$this->obj_db = $obj_db;
		$this->loan_limit = intval($_SESSION["user"]->member_type_prop['loan_limit']);
		$this->loan_periode = intval($_SESSION["user"]->member_type_prop['loan_periode']);
		$this->reborrow_limit = intval($_SESSION["user"]->member_type_prop['reborrow_limit']);
		$this->fine_each_day = intval($_SESSION["user"]->member_type_prop['fine_each_day']);
		$this->grace_periode = intval($_SESSION["user"]->member_type_prop['grace_periode']);
	}

	// set loan rules
	public function setLoanRules($int_coll_type = 0, $int_gmd_id = 0) {
		// if the collection type and gmd is not specified
		// get from the membership type directly
		if (!$int_coll_type AND !$int_gmd_id) {
				return;
		}

		$ctype_string = '';
		if ($int_coll_type) {
				$ctype_string .= ' AND coll_type_id='.intval($int_coll_type).' ';
		}
		$gmd_string = '';
		if ($int_gmd_id) {
				$gmd_string .= ' AND gmd_id='.intval($int_gmd_id).' ';
		}

		// get the data from the loan rules table
		$loan_rules_q = $this->obj_db->query("SELECT * FROM mst_loan_rules
				WHERE member_type_id=".intval($_SESSION["user"]->member_type_id)." $ctype_string $gmd_string");
		// check if the loan rules exists
		if ($loan_rules_d = $loan_rules_q->fetch_assoc()) {
				$this->loan_limit = $loan_rules_d['loan_limit'];
				$this->loan_periode = $loan_rules_d['loan_periode'];
				$this->reborrow_limit = $loan_rules_d['reborrow_limit'];
				$this->fine_each_day = $loan_rules_d['fine_each_day'];
				$this->grace_periode = $loan_rules_d['grace_periode'];
				$this->item_loan_rules = $loan_rules_d['loan_rules_id'];
		} else {
				// get data from the loan rules table with collection type specified but GMD not specified
				$loan_rules_q = $this->obj_db->query("SELECT * FROM mst_loan_rules
						WHERE member_type_id=".intval($_SESSION["user"]->member_type_id)." $ctype_string");
				// check if the loan rules exists
				if ($loan_rules_d = $loan_rules_q->fetch_assoc()) {
						$this->loan_limit = $loan_rules_d['loan_limit'];
						$this->loan_periode = $loan_rules_d['loan_periode'];
						$this->reborrow_limit = $loan_rules_d['reborrow_limit'];
						$this->fine_each_day = $loan_rules_d['fine_each_day'];
						$this->grace_periode = $loan_rules_d['grace_periode'];
						$this->item_loan_rules = $loan_rules_d['loan_rules_id'];
				} else {
						// get data from the loan rules table with GMD specified but collection type not specified
						$loan_rules_q = $this->obj_db->query("SELECT * FROM mst_loan_rules
								WHERE member_type_id=".intval($this->member_type_id)." $gmd_string");
						// check if the loan rules exists
						if ($loan_rules_d = $loan_rules_q->fetch_assoc()) {
								$this->loan_limit = $loan_rules_d['loan_limit'];
								$this->loan_periode = $loan_rules_d['loan_periode'];
								$this->reborrow_limit = $loan_rules_d['reborrow_limit'];
								$this->fine_each_day = $loan_rules_d['fine_each_day'];
								$this->grace_periode = $loan_rules_d['grace_periode'];
								$this->item_loan_rules = $loan_rules_d['loan_rules_id'];
						}
				}
		}
		// destroy query object
		unset($_loan_rules_q);
	}

	// loan item
	public function loanItem($scanned) {
		// check item availability and loan status
		$avail_q = $this->obj_db->query("SELECT item_code FROM loan
			WHERE item_code ='" .$scanned["item_code"]. "' AND is_lent='1' AND is_return='0'");
		if ($avail_q->num_rows > 0) {
			$msg = "Item sedang dipinjam member lain";
		} elseif ((int)$scanned["no_loan"] > 0) {
			$msg = "Item sedang tidak dipinjamkan";
		} else {
			// check if being reserved by other member
			$resv_q = $this->obj_db->query("SELECT l.loan_id FROM reserve AS rs
				INNER JOIN loan AS l ON rs.item_code=l.item_code
				WHERE rs.item_code='" .$scanned["item_code"]. "' AND rs.member_id!='" .$_SESSION["user"]->member_id. "'");
			if ($resv_q->num_rows > 0) {
				$msg = "Item sedang dipesan oleh member lain";
			} else {
				// loan process
				// set loan rules
				self::setLoanRules($scanned["coll_type_id"], $scanned["gmd_id"]);

				// check if loan limit not exceeded
				$curr_loan_count = self::countLoan($this->item_loan_rules);
				if ($this->loan_limit < ($curr_loan_count + 1)) {
					$msg = "Jumlah loan melebihi batas";
				} else {
					// set loan date
					$loan_date = date("Y-m-d");

					// calculate due date
					$due_date = simbio_date::getNextDate($this->loan_periode, $loan_date);
					$due_date = simbio_date::getNextDateNotHoliday($due_date, $this->holiday_dayname, $this->holiday_date);

					// check if due date more than member's expiry date
					$expiry_date_compare = simbio_date::compareDates($due_date, $_SESSION["user"]->expire_date);
					if ($expiry_date_compare != $_SESSION["user"]->expire_date) {
						$due_date = $_SESSION["expire_date"];
					}

					// insert to database
					$insert_q = "INSERT INTO loan (item_code, member_id, loan_date, due_date, renewed, loan_rules_id, is_lent, is_return)
						VALUES ('" .$scanned["item_code"]. "', '" .$_SESSION["member_id"]. "', '$loan_date', '$due_date', '0', '" .$this->loan_rules_id. "', '1', '0')";
					if ($this->obj_db->query($insert_q) === true) {
						$msg = "Peminjaman item berhasil";
					} else {
						$msg = "Peminjaman item gagal";
					}
				}
			}
		}

		return $msg;
	}
	
	// get current loan count
	protected function countLoan($int_loan_rules = 0) {
		$lrules_string = '';
		if ($int_loan_rules) {
			$lrules_string = "AND loan_rules_id=$int_loan_rules";
		}

		// get data from database
		$on_loan_q = $this->obj_db->query("SELECT count(item_code) FROM loan
			WHERE member_id='" .$_SESSION["user"]->member_id. "' AND is_lent=1 AND is_return=0 $lrules_string");
		
		return $on_loan_q->fetch_row["0"];
	}

	// get info about item being scanned
	public static function getScannedInfo($item_code, $obj_db) {
		// get data from database
		$item_search_q = $obj_db->query("SELECT b.title, i.item_code, i.coll_type_id, b.gmd_id, ist.no_loan
			FROM biblio AS b
			LEFT JOIN item AS i ON b.biblio_id=i.biblio_id
			LEFT JOIN mst_item_status AS ist ON i.item_status_id=ist.item_status_id
			WHERE i.item_code='" .$item_code. "'");

		// return data if found, else false
		return $item_search_q->num_rows > 0 ? $item_search_q->fetch_assoc() : false;
	}
}
?>
