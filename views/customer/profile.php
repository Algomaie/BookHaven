<?php
session_start();
require_once 'config/database.php';
require_once 'models/User.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: /index.php");
    exit;
}
$user_model = new User();
$user = mysqli_fetch_assoc($user_model->getAll("WHERE u.UserID = {$_SESSION['user_id']}"));
?>
<?php include 'views/includes/header.php'; ?>
<div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-4">Your Profile</h1>
    <div class="bg-white p-4 rounded shadow">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['Name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($user['Address']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['Phone']); ?></p>
    </div>
</div>
<?php include 'views/includes/footer.php'; ?>
</body>
</html>