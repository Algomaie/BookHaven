<?php
require_once 'models/Book.php';
require_once 'utils/helpers.php';

class BookController {
    private $book;

    public function __construct() {
        $this->book = new Book();
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'seller') {
            $title = $_POST['title'];
            $author = $_POST['author'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $category_id = $_POST['category_id'];
            $seller_id = $_SESSION['user_id'];
            $cover_image = null;
            if ($_FILES['cover_image']['name']) {
                $target_dir = UPLOAD_PATH;
                $cover_image = time() . "_" . basename($_FILES['cover_image']['name']);
                move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_dir . $cover_image);
            }
            if ($this->book->add($title, $author, $price, $stock, $category_id, $seller_id, $cover_image)) {
                redirect('/views/seller/add-book.php?success=Book added');
            } else {
                $error = "Failed to add book";
                include 'views/seller/add-book.php';
            }
        } else {
            include 'views/seller/add-book.php';
        }
    }

    public function edit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'seller') {
            $book_id = $_POST['book_id'];
            $title = $_POST['title'];
            $author = $_POST['author'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $category_id = $_POST['category_id'];
            $cover_image = null;
            if ($_FILES['cover_image']['name']) {
                $target_dir = UPLOAD_PATH;
                $cover_image = time() . "_" . basename($_FILES['cover_image']['name']);
                move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_dir . $cover_image);
            }
            if ($this->book->update($book_id, $title, $author, $price, $stock, $category_id, $cover_image)) {
                redirect('/views/seller/edit-book.php?book_id=' . $book_id . '&success=Book updated');
            } else {
                $error = "Failed to update book";
                include 'views/seller/edit-book.php';
            }
        } else {
            $book_id = $_GET['book_id'] ?? 0;
            $book = mysqli_fetch_assoc($this->book->getById($book_id));
            include 'views/seller/edit-book.php';
        }
    }

    public function delete() {
        if ($_SESSION['role'] === 'seller' && isset($_POST['book_id'])) {
            $this->book->delete($_POST['book_id']);
            redirect('/views/seller/books.php');
        }
    }

    public function approve() {
        if ($_SESSION['role'] === 'admin' && isset($_POST['book_id'])) {
            $this->book->approve($_POST['book_id']);
            redirect('/views/admin/pending-approvals.php');
        }
    }

    public function reject() {
        if ($_SESSION['role'] === 'admin' && isset($_POST['book_id'])) {
            $this->book->reject($_POST['book_id']);
            redirect('/views/admin/pending-approvals.php');
        }
    }
}
?>