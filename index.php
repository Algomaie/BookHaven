<?php
require_once 'config/database.php';
require_once 'models/Book.php';
$book_model = new Book();
$books = $book_model->getAll();
?>
<?php include 'views/includes/header.php'; ?>
<div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-4">Welcome to Our Bookstore</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <?php while ($book = mysqli_fetch_assoc($books)): ?>
            <div class="bg-white p-4 rounded shadow">
                <?php if ($book['CoverImage']): ?>
                    <img src="/Uploads/book_covers/<?php echo htmlspecialchars($book['CoverImage']); ?>" alt="Cover" class="w-full h-48 object-cover mb-4">
                <?php endif; ?>
                <h3 class="text-xl font-bold"><?php echo htmlspecialchars($book['Title']); ?></h3>
                <p>Author: <?php echo htmlspecialchars($book['Author']); ?></p>
                <p>Price: $<?php echo number_format($book['Price'], 2); ?></p>
                <p>Category: <?php echo htmlspecialchars($book['CategoryName']); ?></p>
                <p>Rating: <?php echo number_format($book['Rating'], 1); ?>/5</p>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
                    <form method="POST" action="/controllers/CartController.php?action=add">
                        <input type="hidden" name="book_id" value="<?php echo $book['BookID']; ?>">
                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $book['Stock']; ?>" class="p-2 border rounded w-20">
                        <button type="submit" class="bg-blue-500 text-white p-2 rounded mt-2">Add to Cart</button>
                    </form>
                    <a href="/controllers/WishlistController.php?action=add&book_id=<?php echo $book['BookID']; ?>" class="text-blue-500 mt-2 block">Add to Wishlist</a>
                    <a href="/views/customer/review.php?book_id=<?php echo $book['BookID']; ?>" class="text-blue-500 mt-2 block">Write Review</a>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<?php include 'views/includes/footer.php'; ?>
</body>
</html>