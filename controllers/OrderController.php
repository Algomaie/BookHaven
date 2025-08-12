<?php
require_once 'models/Order.php';
require_once 'utils/helpers.php';

class OrderController {
    private $order;

    public function __construct() {
        $this->order = new Order();
    }

    public function cancel() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'customer') {
            $order_id = $_POST['order_id'];
            $this->order->cancel($order_id, $_SESSION['user_id']);
            redirect('/views/customer/orders.php');
        }
    }
}
?>