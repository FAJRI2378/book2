<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id'])) {
    echo "<p>Silakan login untuk melihat pesanan Anda.</p>";
    exit;
}

$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn, "
    SELECT o.id, b.title, b.author, b.price, o.order_date 
    FROM orders o 
    JOIN books b ON o.book_id = b.id 
    WHERE o.user_id = $user_id 
    ORDER BY o.order_date DESC
");

echo "<h2>Daftar Pesanan Saya</h2>";
echo "<ul>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<li>";
    echo "<strong>" . htmlspecialchars($row['title']) . "</strong> oleh " . htmlspecialchars($row['author']);
    echo " | Harga: Rp" . number_format($row['price'], 0, ',', '.') . "";
    echo " | Dibeli pada: " . $row['order_date'];
    echo "</li>";
}

echo "</ul>";
?>
