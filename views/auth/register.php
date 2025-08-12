<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="/assets/css/tailwindcss.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center">Register</h2>
        <?php if (isset($error)): ?>
            <p class="text-red-500 mb-4"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" action="/controllers/AuthController.php?action=register">
            <div class="mb-4">
                <label class="block text-gray-700">Name</label>
                <input type="text" name="name" class="w-full p-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Email</label>
                <input type="email" name="email" class="w-full p-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Password</label>
                <input type="password" name="password" class="w-full p-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Address</label>
                <input type="text" name="address" class="w-full p-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Phone</label>
                <input type="text" name="phone" class="w-full p-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Role</label>
                <select name="role" class="w-full p-2 border rounded">
                    <option value="customer">Customer</option>
                    <option value="seller">Seller</option>
                </select>
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded">Register</button>
        </form>
    </div>
</body>
</html>