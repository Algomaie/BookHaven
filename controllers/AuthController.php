<?php
require_once 'models/User.php';
require_once 'utils/helpers.php';

class AuthController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];
            if ($this->user->login($email, $password)) {
                redirect('/index.php');
            } else {
                $error = "Invalid credentials";
                include 'views/auth/login.php';
            }
        } else {
            include 'views/auth/login.php';
        }
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $address = $_POST['address'];
            $phone = $_POST['phone'];
            $role = $_POST['role'] ?? 'customer';
            if ($this->user->register($name, $email, $password, $address, $phone, $role)) {
                redirect('/views/auth/login.php');
            } else {
                $error = "Registration failed";
                include 'views/auth/register.php';
            }
        } else {
            include 'views/auth/register.php';
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        redirect('/index.php');
    }
}
?>