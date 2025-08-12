<?php
session_start();
require_once 'config/database.php';
require_once 'models/Book.php';
require_once 'models/User.php';
require_once 'models/Order.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /index.php");
    exit;
}
$book_model = new Book();
$user_model = new User();
$order_model = new Order();
$pending_books = $book_model->getPending();
$users = $user_model->getAll();
$orders = $order_model->getByCustomer(0); // Simplified for demo
?>
<?php include 'views/admin/includes/admin-header.php'; ?>
<?php include 'views/admin/includes/admin-sidebar.php'; ?>
<div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-4">Admin Dashboard</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded shadow">
            <h3 class="text-xl font-bold">Pending Books</h3>
            <p><?php echo mysqli_num_rows($pending_books); ?> books awaiting approval</p>
            <a href="pending-approvals.php" class="text-blue-500">View</a>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <h3 class="text-xl font-bold">Users</h3>
            <p><?php echo mysqli_num_rows($users); ?> registered users</p>
            <a href="users.php" class="text-blue-500">View</