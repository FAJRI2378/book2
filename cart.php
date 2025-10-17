<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $bookId = (int)$_POST['book_id'];

    // Inisialisasi cart kalau belum ada
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Jika buku sudah ada, tambahkan qty, kalau belum set qty=1
    if (isset($_SESSION['cart'][$bookId])) {
        $_SESSION['cart'][$bookId]++;
    } else {
        $_SESSION['cart'][$bookId] = 1;
    }

    header("Location: cart_view.php");
    exit;
}
?>
