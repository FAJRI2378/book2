<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $bookId = (int)$_POST['book_id'];

    $result = mysqli_query($conn, "SELECT * FROM books WHERE id = $bookId");
    $book = mysqli_fetch_assoc($result);

    if ($book) {
        echo "<h2>Terima kasih sudah membeli:</h2>";
        echo "<strong>{$book['title']}</strong> oleh {$book['author']}<br>";
        echo "Harga: Rp" . number_format($book['price']) . "<br>";
    } else {
        echo "Buku tidak ditemukan.";
    }

    echo "<br><a href='index.php'>Kembali ke Daftar Buku</a>";
}
?>
