<?php
require_once 'config/database.php';

class Seller {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conn;
    }

    public function updateProfile($seller_id, $store_name) {
        $store_name = mysqli_real_escape_string($this->db, $store_name);
        $query = "UPDATE Seller SET StoreName = '$store_name' WHERE UserID = $seller_id";
        return mysqli_query($this->db, $query);
    }
}
?>