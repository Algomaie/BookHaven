<?php
session_start();
require_once 'config/database.php';
require_once 'models/Book.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer' || !isset($_GET['book_id'])) {
    header("Location: /index.php");
    exit;
}
$book_model = new Book();
$book_id = intval($_GET['book_id']);
$book = mysqli_fetch_assoc($book_model->getById($book_id));
?>
<?php include 'views/includes/header.php'; ?>
<div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-4">Write Review for <?php echo htmlspecialchars($book['Title']); ?></h1>
    <?php if (isset($error)): ?>
        <p class="text-red-500 mb-4"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST" action="/controllers/ReviewController.php?action=add">
        <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
        <div class="mb-4">
            <label class="block text-gray-700">Rating (1-5)</label>
            <input type="number" name="rating" min="1" max="5" class="w-full p-2 border rounded" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Comment</label>
            <textarea name="comment" class="w-full p-2 border rounded" rows="4"></textarea>
        </div>
        <button type="submit" class="bg-blue-500 text-white p-2 rounded">Submit Review</button>
    </form>
</div>
<?php include 'views/includes/footer.php'; ?>
</body>
</html>