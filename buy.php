<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    die("Silakan login dulu.");
}

$user_id = $_SESSION['user_id'];
$book_id = $_POST['book_id'];
$jumlah  = (int) $_POST['jumlah']; // pastikan integer

if ($jumlah < 1) {
    die("Jumlah pembelian tidak valid.");
}

// Cek stok buku dulu
$stok_query = mysqli_prepare($conn, "SELECT stock FROM books WHERE id = ?");
mysqli_stmt_bind_param($stok_query, "i", $book_id);
mysqli_stmt_execute($stok_query);
$result = mysqli_stmt_get_result($stok_query);
$book = mysqli_fetch_assoc($result);

if (!$book || $book['stock'] < $jumlah) {
    die("Stok tidak mencukupi.");
}

// Simpan ke tabel orders
$sql = "INSERT INTO orders (user_id, book_id, jumlah, order_date) VALUES (?, ?, ?, NOW())";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "iii", $user_id, $book_id, $jumlah);
mysqli_stmt_execute($stmt);

// Kurangi stok buku
$update_stock_sql = "UPDATE books SET stock = stock - ? WHERE id = ?";
$update_stmt = mysqli_prepare($conn, $update_stock_sql);
mysqli_stmt_bind_param($update_stmt, "ii", $jumlah, $book_id);
mysqli_stmt_execute($update_stmt);

// Hapus dari keranjang
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    if (($key = array_search($book_id, $_SESSION['cart'])) !== false) {
        unset($_SESSION['cart'][$key]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
}

header("Location: cart_view.php");
exit;
