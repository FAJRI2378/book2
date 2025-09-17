<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    die("Silakan login dulu.");
}

$user_id = $_SESSION['user_id'];
$book_id = (int) $_POST['book_id'];
$jumlah  = (int) $_POST['jumlah'];
$payment = $_POST['payment_method'];
$shipping_address = $_POST['shipping_address'] ?? '';
$shipping_method  = $_POST['shipping_method'] ?? '';

if ($jumlah < 1) {
    die("Jumlah pembelian tidak valid.");
}

// cek stok
$stmt = mysqli_prepare($conn, "SELECT stock FROM books WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $book_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$book = mysqli_fetch_assoc($result);

if (!$book || $book['stock'] < $jumlah) {
    die("Stok tidak mencukupi.");
}

// simpan ke orders dengan prepared statement
$sql = "INSERT INTO orders 
        (user_id, book_id, jumlah, order_date, payment_method, shipping_address, shipping_method, status) 
        VALUES (?, ?, ?, NOW(), ?, ?, ?, 'pending')";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "iiisss", $user_id, $book_id, $jumlah, $payment, $shipping_address, $shipping_method);
mysqli_stmt_execute($stmt);

// kurangi stok
$update_sql = "UPDATE books SET stock = stock - ? WHERE id = ?";
$update_stmt = mysqli_prepare($conn, $update_sql);
mysqli_stmt_bind_param($update_stmt, "ii", $jumlah, $book_id);
mysqli_stmt_execute($update_stmt);

// hapus dari keranjang
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    if (($key = array_search($book_id, $_SESSION['cart'])) !== false) {
        unset($_SESSION['cart'][$key]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
}

header("Location: orderan_saya.php?success=1");
exit;
