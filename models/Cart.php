<?php
require_once 'config/database.php';

class Cart {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conn;
    }

    public function getByCustomer($customer_id) {
        $query = "SELECT c.*, ci.*, b.Title, b.Price 
                  FROM Cart c 
                  JOIN CartItem ci ON c.CartID = ci.CartID 
                  JOIN Book b ON ci.BookID = b.BookID 
                  WHERE c.CustomerID = $customer_id";
        return mysqli_query($this->db, $query);
    }

    public function addItem($customer_id, $book_id, $quantity) {
        $cart_query = "SELECT CartID FROM Cart WHERE CustomerID = $customer_id";
        $cart_result = mysqli_query($this->db, $cart_query);
        if (mysqli_num_rows($cart_result) == 0) {
            $create_cart = "INSERT INTO Cart (CustomerID) VALUES ($customer_id)";
            mysqli_query($this->db, $create_cart);
            $cart_id = mysqli_insert_id($this->db);
        } else {
            $cart = mysqli_fetch_assoc($cart_result);
            $cart_id = $cart['CartID'];
        }

        $check_item = "SELECT * FROM CartItem WHERE CartID = $cart_id AND BookID = $book_id";
        $item_result = mysqli_query($this->db, $check_item);
        if (mysqli_num_rows($item_result) > 0) {
            $update_item = "UPDATE CartItem SET Quantity = Quantity + $quantity WHERE CartID = $cart_id AND BookID = $book_id";
            mysqli_query($this->db, $update_item);
        } else {
            $add_item = "INSERT INTO CartItem (CartID, BookID, Quantity) VALUES ($cart_id, $book_id, $quantity)";
            mysqli_query($this->db, $add_item);
        }

        $this->updateTotal($cart_id);
    }

    public function removeItem($customer_id, $book_id) {
        $cart_query = "SELECT CartID FROM Cart WHERE CustomerID = $customer_id";
        $cart_result = mysqli_query($this->db, $cart_query);
        if ($cart = mysqli_fetch_assoc($cart_result)) {
            $cart_id = $cart['CartID'];
            $delete_item = "DELETE FROM CartItem WHERE CartID = $cart_id AND BookID = $book_id";
            mysqli_query($this->db, $delete_item);
            $this->updateTotal($cart_id);
        }
    }

    private function updateTotal($cart_id) {
        $total_query = "SELECT SUM(ci.Quantity * b.Price) as Total 
                        FROM CartItem ci 
                        JOIN Book b ON ci.BookID = b.BookID 
                        WHERE ci.CartID = $cart_id";
        $total_result = mysqli_query($this->db, $total_query);
        $total = mysqli_fetch_assoc($total_result)['Total'] ?? 0;
        $update_total = "UPDATE Cart SET TotalPrice = $total WHERE CartID = $cart_id";
        mysqli_query($this->db, $update_total);
    }
}
?>