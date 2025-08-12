<?php
require_once 'config/database.php';

class Review {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conn;
    }

    public function getByBook($book_id) {
        $query = "SELECT r.*, u.Name FROM Review r 
                  JOIN User u ON r.CustomerID = u.UserID 
                  WHERE r.BookID = $book_id";
        return mysqli_query($this->db, $query);
    }

    public function add($customer_id, $book_id, $rating, $comment) {
        $comment = mysqli_real_escape_string($this->db, $comment);
        $query = "INSERT INTO Review (CustomerID, BookID, Rating, Comment) 
                  VALUES ($customer_id, $book_id, $rating, '$comment')";
        if (mysqli_query($this->db, $query)) {
            $this->updateBookRating($book_id);
            return true;
        }
        return false;
    }

    public function hide($review_id) {
        $query = "UPDATE Review SET Comment = NULL WHERE ReviewID = $review_id";
        return mysqli_query($this->db, $query);
    }

    private function updateBookRating($book_id) {
        $avg_rating_query = "SELECT AVG(Rating) as AvgRating FROM Review WHERE BookID = $book_id";
        $avg_rating_result = mysqli_query($this->db, $avg_rating_query);
        $avg_rating = mysqli_fetch_assoc($avg_rating_result)['AvgRating'] ?? 0;
        $update_book = "UPDATE Book SET Rating = $avg_rating WHERE BookID = $book_id";
        mysqli_query($this->db, $update_book);
    }
}
?>