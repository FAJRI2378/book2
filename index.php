<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Welcome to the Book Management System</h1>
    <p>Please choose an option:</p>
    <ul>
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
       <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <a href="admin/categories.php">Admin Categories</a>
<?php endif; ?>

    </ul>

    <form method="GET">
    <input type="text" name="search" placeholder="Cari buku...">
    <button type="submit">Cari</button>
</form>

</body>
</html>