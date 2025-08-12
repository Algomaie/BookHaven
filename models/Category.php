<?php
require_once 'config/database.php';

class Category {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conn;
    }

    public function getAll() {
        $query = "SELECT * FROM Category";
        return mysqli_query($this->db, $query);
    }
}
?>