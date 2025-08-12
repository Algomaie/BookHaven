<?php
require_once 'config/database.php';

class Order {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conn;
    }

    public function getByCustomer($customer_id) {
        $query = "SELECT o.*, oi.*, b.Title 
                  FROM `Order` o 
                  JOIN OrderItem oi ON o.OrderID = oi.OrderID 
                  JOIN Book b ON oi.BookID = b.BookID 
                  WHERE o.CustomerID = $customer_id";
        return mysqli_query($this->db, $query);
    }

    public function getBySeller($seller_id) {
        $query = "SELECT o.*, oi.*, b.Title, u.Name 
                  FROM `Order` o 
                  JOIN OrderItem oi ON o.OrderID = oi.OrderID 
                  JOIN Book b ON oi.BookID = b.BookID 
                  JOIN User u ON o.CustomerID = u.UserID 
                  WHERE b.SellerID = $seller_id";
        return mysqli_query($this->db, $query);
    }

    public function createFromCart($customer_id) {
        $cart_query = "SELECT c.*, ci.*, b.Price 
                       FROM Cart c 
                       JOIN CartItem ci ON c.CartID = ci.CartID 
                       JOIN Book b ON ci.BookID = b.BookID 
                       WHERE c.CustomerID = $customer_id";
        $cart_result = mysqli_query($this->db, $cart_query);
        if (mysqli_num_rows($cart_result) > 0) {
            $total_amount = 0;
            while ($item = mysqli_fetch_assoc($cart_result)) {
                $total_amount += $item['Price'] * $item['Quantity'];
            }
            mysqli_data_seek($cart_result, 0);
            $order_query = "INSERT INTO `Order` (CustomerID, TotalAmount) VALUES ($customer_id, $total_amount)";
            mysqli_query($this->db, $order_query);
            $order_id = mysqli_insert_id($this->db);
            while ($item = mysqli_fetch_assoc($cart_result)) {
                $subtotal = $item['Price'] * $item['Quantity'];
                $order_item_query = "INSERT INTO OrderItem (OrderID, BookID, Quantity, Subtotal) 
                                     VALUES ($order_id, {$item['BookID']}, {$item['Quantity']}, $subtotal)";
                mysqli_query($this->db, $order_item_query);
                $update_stock = "UPDATE Book SET Stock = Stock - {$item['Quantity']} WHERE BookID = {$item['BookID']}";
                mysqli_query($this->db, $update_stock);
            }
            $delete_cart_items = "DELETE FROM CartItem WHERE CartID = {$item['CartID']}";
            mysqli_query($this->db, $delete_cart_items);
            $update_cart = "UPDATE Cart SET TotalPrice = 0 WHERE CartID = {$item['CartID']}";
            mysqli_query($this->db, $update_cart);
            return true;
        }
        return false;
    }

    public function cancel($order_id, $customer_id) {
        $query = "UPDATE `Order` SET Status = 'Canceled' 
                  WHERE OrderID = $order_id AND CustomerID = $customer_id AND Status = 'Pending'";
        return mysqli_query($this->db, $query);
    }
}
?>