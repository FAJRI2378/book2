<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $bookId = (int)$_POST['book_id'];

    // Inisialisasi session cart jika belum ada
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Tambah buku ke keranjang
    $_SESSION['cart'][] = $bookId;

    header("Location: cart_view.php"); // tampilkan keranjang setelah ditambah
    exit;
}
?>
