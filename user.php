<?php
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
    die("can not access this file directly");
}

class user {
  public $is_member = false;
  public $member_id = null;
  public $member_name = null;
  public $member_type_id = null;
  public $member_type_name = null;
  public $expire_date = null;
  public $is_expired = true;
  public $is_pending = true;
  public $member_type_prop = array();
  protected $obj_db = false;

  // class constructor
  public function __construct($obj_db, $member_id, $password) {
      $this->obj_db = $obj_db;

      // fetch data from database
      $member_q = $this->obj_db->query("SELECT m.*, mt.*
        FROM member AS m
        LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id
        WHERE m.member_id='$member_id' AND m.mpasswd=MD5('$password')");

      if ($member_d = $member_q->fetch_assoc()) {
          $this->is_member = true;

          // assign database value
          $this->member_id = $member_d["member_id"];
          $this->member_name = $member_d["member_name"];
          $this->member_type_id = $member_d["member_type_id"];
          $this->member_type_name = $member_d["member_type_id"];
          $this->expire_date = $member_d["expire_date"];
          $this->is_expired = time() > strtotime($member_d["expire_date"]) ? true : false;
          $this->is_pending = (bool)$member_d["is_pending"];
          $this->member_type_prop = array(
            "loan_limit" => $member_d["loan_limit"],
            "loan_periode" => $member_d["loan_periode"],
            "reborrow_limit" => $member_d["reborrow_limit"],
            "fine_each_day" => $member_d["grace_periode"]
          );
      }
  }

  // function to change password
  public function changePassword($old_pass, $new_pass, $conf_new_pass) {
    // check old password
    $pass_check_q = $conn->query("SELECT member_id FROM member WHERE member_id='" .$this->member_id. "' AND mpasswd=MD5('" .$old_pass. "')");
    if ($pass_check_q->num_rows > 0) {
      // check if new password and confirmation is same
      if ($new_pass && $conf_new_pass && ($new_pass === $conf_new_pass)) {
        // change the password
        if ($this->obj_db->query("UPDATE member SET mpasswd=MD5('" .$new_pass. "') WHERE member_id='" .$member_id. "'") === true) {
          $msg = "Password berhasil diganti";
        } else {
          $msg = "Penggantian password gagal";
        }
      } else {
        $msg = "Password baru dan konfirmasi harus sama";
      }
    } else {
      $msg = "Password lama salah";
    }
    return $msg;
  }

  // get info about item being loaned
  public function getItemLoan() {
    $data = array();
    // get data from database
    $on_loan_q = $this->obj_db->query("SELECT l.loan_id, b.title, l.loan_date, l.due_date
      FROM loan AS l
      LEFT JOIN member AS m ON l.member_id=m.member_id
      LEFT JOIN item AS i ON l.item_code=i.item_code
      LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id
      WHERE l.member_id='" .$this->member_id. "'");

    // put every item into array
    while ($on_loan_d = $on_loan_q->fetch_assoc()) {
      $data[] = $on_loan_d;
    }

    return $data;
  } 
}
?>

