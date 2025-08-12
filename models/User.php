<?php
require_once 'config/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conn;
    }

    public function login($email, $password) {
        $email = mysqli_real_escape_string($this->db, $email);
        $query = "SELECT * FROM User WHERE Email = '$email'";
        $result = mysqli_query($this->db, $query);
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($password, $user['Password'])) {
                $_SESSION['user_id'] = $user['UserID'];
                $_SESSION['role'] = $this->getRole($user['UserID']);
                return true;
            }
        }
        return false;
    }

    public function register($name, $email, $password, $address, $phone, $role = 'customer') {
        $name = mysqli_real_escape_string($this->db, $name);
        $email = mysqli_real_escape_string($this->db, $email);
        $password = password_hash($password, PASSWORD_DEFAULT);
        $address = mysqli_real_escape_string($this->db, $address);
        $phone = mysqli_real_escape_string($this->db, $phone);
        $query = "INSERT INTO User (Name, Email, Password) VALUES ('$name', '$email', '$password')";
        if (mysqli_query($this->db, $query)) {
            $user_id = mysqli_insert_id($this->db);
            if ($role === 'customer') {
                $query = "INSERT INTO Customer (UserID, Address, Phone) VALUES ($user_id, '$address', '$phone')";
            } elseif ($role === 'seller') {
                $store_name = mysqli_real_escape_string($this->db, $_POST['store_name'] ?? 'Default Store');
                $query = "INSERT INTO Seller (UserID, StoreName) VALUES ($user_id, '$store_name')";
            }
            return mysqli_query($this->db, $query);
        }
        return false;
    }

    public function delete($user_id) {
        $query = "DELETE FROM User WHERE UserID = $user_id";
        return mysqli_query($this->db, $query);
    }

    public function getAll() {
        $query = "SELECT u.*, c.Address, s.StoreName FROM User u 
                  LEFT JOIN Customer c ON u.UserID = c.UserID 
                  LEFT JOIN Seller s ON u.UserID = s.UserID";
        return mysqli_query($this->db, $query);
    }

    private function getRole($user_id) {
        $roles = ['Admin', 'Customer', 'Seller'];
        foreach ($roles as $role) {
            $query = "SELECT * FROM $role WHERE UserID = $user_id";
            $result = mysqli_query($this->db, $query);
            if (mysqli_num_rows($result) > 0) {
                return strtolower($role);
            }
        }
        return 'guest';
    }
}
?>