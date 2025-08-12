<?php
require_once 'models/Cart.php';
require_once 'utils/helpers.php';

class CartController {
    private $cart;

    public function __construct() {
        $this->cart = new Cart();
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'customer') {
            $book_id = $_POST['book_id'];
            $quantity = $_POST['quantity'];
            $this->cart->addItem($_SESSION['user_id'], $book_id, $quantity);
            redirect('/cart.php');
        }
    }

    public function remove() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'customer') {
            $book_id = $_POST['book_id'];
            $this->cart->removeItem($_SESSION['user_id'], $book_id);
            redirect('/cart.php');
        }
    }
}
?>