<?php
require_once 'config/database.php';

class Wishlist {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conn;
    }

    public function getByCustomer($customer_id) {
        $query = "SELECT w.*, b.Title, b.Price, b.CoverImage 
                  FROM Wishlist w 
                  JOIN Book b ON w.BookID = b.BookID 
                  WHERE w.CustomerID = $customer_id";
        return mysqli_query($this->db, $query);
    }

    public function add($customer_id, $book_id) {
        $check_query = "SELECT * FROM Wishlist WHERE CustomerID = $customer_id AND BookID = $book_id";
        $check_result = mysqli_query($this->db, $check_query);
        if (mysqli_num_rows($check_result) == 0) {
            $query = "INSERT INTO Wishlist (CustomerID, BookID) VALUES ($customer_id, $book_id)";
            mysqli_query($this->db, $query);
        }
    }

    public function remove($customer_id, $book_id) {
        $query = "DELETE FROM Wishlist WHERE CustomerID = $customer_id AND BookID = $book_id";
        mysqli_query($this->db, $query);
    }
}
?>