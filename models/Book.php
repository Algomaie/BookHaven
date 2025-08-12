<?php
require_once 'config/database.php';

class Book {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conn;
    }

    public function getAll($status = 'Approved') {
        $query = "SELECT b.*, c.Name as CategoryName FROM Book b 
                  JOIN Category c ON b.CategoryID = c.CategoryID 
                  WHERE b.Status = '$status'";
        return mysqli_query($this->db, $query);
    }

    public function getById($book_id) {
        $query = "SELECT b.*, c.Name as CategoryName FROM Book b 
                  JOIN Category c ON b.CategoryID = c.CategoryID 
                  WHERE b.BookID = $book_id";
        return mysqli_query($this->db, $query);
    }

    public function getBySeller($seller_id) {
        $query = "SELECT b.*, c.Name as CategoryName FROM Book b 
                  JOIN Category c ON b.CategoryID = c.CategoryID 
                  WHERE b.SellerID = $seller_id";
        return mysqli_query($this->db, $query);
    }

    public function getPending() {
        $query = "SELECT b.*, c.Name as CategoryName FROM Book b 
                  JOIN Category c ON b.CategoryID = c.CategoryID 
                  WHERE b.Status = 'Pending'";
        return mysqli_query($this->db, $query);
    }

    public function add($title, $author, $price, $stock, $category_id, $seller_id, $cover_image) {
        $title = mysqli_real_escape_string($this->db, $title);
        $author = mysqli_real_escape_string($this->db, $author);
        $cover_image = mysqli_real_escape_string($this->db, $cover_image);
        $query = "INSERT INTO Book (Title, Author, Price, Stock, CategoryID, SellerID, CoverImage, Status) 
                  VALUES ('$title', '$author', $price, $stock, $category_id, $seller_id, '$cover_image', 'Pending')";
        return mysqli_query($this->db, $query);
    }

    public function update($book_id, $title, $author, $price, $stock, $category_id, $cover_image = null) {
        $title = mysqli_real_escape_string($this->db, $title);
        $author = mysqli_real_escape_string($this->db, $author);
        $cover_query = $cover_image ? ", CoverImage = '" . mysqli_real_escape_string($this->db, $cover_image) . "'" : "";
        $query = "UPDATE Book SET Title='$title', Author='$author', Price=$price, Stock=$stock, CategoryID=$category_id $cover_query 
                  WHERE BookID=$book_id";
        return mysqli_query($this->db, $query);
    }

    public function delete($book_id) {
        $query = "DELETE FROM Book WHERE BookID=$book_id";
        return mysqli_query($this->db, $query);
    }

    public function approve($book_id) {
        $query = "UPDATE Book SET Status='Approved' WHERE BookID=$book_id";
        return mysqli_query($this->db, $query);
    }

    public function reject($book_id) {
        $query = "UPDATE Book SET Status='Rejected' WHERE BookID=$book_id";
        return mysqli_query($this->db, $query);
    }
}
?>