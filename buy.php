<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    die("Silakan login dulu.");
}

$user_id = $_SESSION['user_id'];
$book_id = $_POST['book_id'];

// Simpan ke tabel orders
$sql = "INSERT INTO orders (user_id, book_id) VALUES (?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $user_id, $book_id);
mysqli_stmt_execute($stmt);

// Kurangi stok buku
$update_stock_sql = "UPDATE books SET stock = stock - 1 WHERE id = ? AND stock > 0";
$update_stmt = mysqli_prepare($conn, $update_stock_sql);
mysqli_stmt_bind_param($update_stmt, "i", $book_id);
mysqli_stmt_execute($update_stmt);

// Hapus dari keranjang jika ada
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    if (($key = array_search($book_id, $_SESSION['cart'])) !== false) {
        unset($_SESSION['cart'][$key]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
}

header("Location: cart_view.php");
exit;
