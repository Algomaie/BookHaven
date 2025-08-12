<?php
require_once 'models/User.php';
require_once 'utils/helpers.php';

class AdminController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    public function deleteUser() {
        if ($_SESSION['role'] === 'admin' && isset($_POST['user_id'])) {
            $this->user->delete($_POST['user_id']);
            redirect('/views/admin/users.php');
        }
    }
}
?>