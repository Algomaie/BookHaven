<?php
session_start();
require_once 'config/database.php';
require_once 'models/Cart.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: index.php");
    exit;
}
$cart_model = new Cart();
$cart_items = $cart_model->getByCustomer($_SESSION['user_id']);
$cart = mysqli_fetch_assoc($cart_items);
?>
<?php include 'views/includes/header.php'; ?>
<div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-4">Your Cart</h1>
    <?php if ($cart && mysqli_num_rows($cart_items) > 0): ?>
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-2">Book</th>
                    <th class="p-2">Price</th>
                    <th class="p-2">Quantity</th>
                    <th class="p-2">Subtotal</th>
                    <th class="p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php mysqli_data_seek($cart_items, 0); while ($item = mysqli_fetch_assoc($cart_items)): ?>
                    <tr>
                        <td class="p-2"><?php echo htmlspecialchars($item['Title']); ?></td>
                        <td class="p-2">$<?php echo number_format($item['Price'], 2); ?></td>
                        <td class="p-2"><?php echo $item['Quantity']; ?></td>
                        <td class="p-2">$<?php echo number_format($item['Price'] * $item['Quantity'], 2); ?></td>
                        <td class="p-2">
                            <form method="POST" action="/controllers/CartController.php?action=remove">
                                <input type="hidden" name="book_id" value="<?php echo $item['BookID']; ?>">
                                <button type="submit" class="text-red-500">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <h3 class="text-xl font-bold mt-4">Total: $<?php echo number_format($cart['TotalPrice'], 2); ?></h3>
        <a href="/checkout.php" class="bg-green-500 text-white p-2 rounded mt-4 inline-block">Proceed to Checkout</a>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</div>
<?php include 'views/includes/footer.php'; ?>
</body>
</html>