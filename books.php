<?php
require_once 'config/database.php';
require_once 'models/Book.php';
$book_model = new Book();
$books = $book_model->getAll();
?>
<?php include 'views/includes/header.php'; ?>
<div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-4">All Books</h1>
    <div class="mb-4">
        <form method="GET">
            <input type="text" name="search" placeholder="Search books..." class="p-2 border rounded">
            <button type="submit" class="bg-blue-500 text-white p-2 rounded">Search</button>
        </form>
    </div>
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
            </div>
        <?php endwhile; ?>
    </div>
</div>
<?php include 'views/includes/footer.php'; ?>
</body>
</html>