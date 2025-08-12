<?php
require_once 'models/Review.php';
require_once 'utils/helpers.php';

class ReviewController {
    private $review;

    public function __construct() {
        $this->review = new Review();
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'customer') {
            $book_id = $_POST['book_id'];
            $rating = $_POST['rating'];
            $comment = $_POST['comment'];
            if ($this->review->add($_SESSION['user_id'], $book_id, $rating, $comment)) {
                redirect('/book-details.php?book_id=' . $book_id);
            } else {
                $error = "Failed to add review";
                include 'views/customer/review.php';
            }
        } else {
            $book_id = $_GET['book_id'] ?? 0;
            include 'views/customer/review.php';
        }
    }

    public function hide() {
        if ($_SESSION['role'] === 'admin' && isset($_POST['review_id'])) {
            $this->review->hide($_POST['review_id']);
            redirect('/views/admin/reviews.php');
        }
    }
}
?>