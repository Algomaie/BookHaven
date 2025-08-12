<?php
require_once 'models/Seller.php';
require_once 'utils/helpers.php';

class SellerController {
    private $seller;

    public function __construct() {
        $this->seller = new Seller();
    }

    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'seller') {
            $store_name = $_POST['store_name'];
            if ($this->seller->updateProfile($_SESSION['user_id'], $store_name)) {
                redirect('/views/seller/profile.php?success=Profile updated');
            } else {
                $error = "Failed to update profile";
                include 'views/seller/profile.php';
            }
        } else {
            include 'views/seller/profile.php';
        }
    }
}
?>