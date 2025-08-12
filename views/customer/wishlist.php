<?php
session_start();
require_once 'config/database.php';
require_once 'models/Wishlist.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: /index.php");
    exit;
}
$wishlist_model = new Wishlist();
$wishlist = $wishlist_model->getByCustomer($_SESSION['user_id']);
?>
<?php include 'views/includes/header.php'; ?>
<div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-4">Your Wishlist</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <?php while ($item = mysqli_fetch_assoc($wishlist)): ?>
            <div class="bg-white p-4 rounded shadow">
                <?php if ($item['CoverImage']): ?>
                    <img src="/Uploads/book_covers/<?php echo htmlspecialchars($item['CoverImage']); ?>" alt="Cover" class="w-full h-48 object-cover mb-4">
                <?php endif; ?>
                <h3 class="text-xl font-bold"><?php echo htmlspecialchars($item['Title']); ?></h3>
                <p>Price: $<?php echo number_format($item['Price'], 2); ?></p>
                <form method="POST" action="/controllers/WishlistController.php?action=remove">
                    <input type="hidden" name="book_id" value="<?php echo $item['BookID']; ?>">
                    <button type="submit" class="text-red-500 mt-2">Remove</button>
                </form>
                <form method="POST" action="/controllers/CartController.php?action=add">
                    <input type="hidden" name="book_id" value="<?php echo $item['BookID']; ?>">
                    <input type="number" name="quantity" value="1" min="1" class="p-2 border rounded w-20">
                    <button type="submit" class="bg-blue-500 text-white p-2 rounded mt-2">Add to Cart</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<?php include 'views/includes/footer.php'; ?>
</body>
</html>