<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

$query = "SELECT  users.username, books.title, books.image, categories.name, books.stock, orders.order_date, books.price
        FROM orders
          JOIN users ON orders.user_id = users.id
          JOIN books ON orders.book_id = books.id
        JOIN categories ON books.category_id = categories.id

          ORDER BY orders.order_date DESC";

$result = mysqli_query($conn, $query);
?>

<h2>Daftar Pesanan</h2>
<table border="1" cellpadding="10" cellspacing="0">
    <tr>
        <th>Username</th>
        <th>Judul Buku</th>
        <th>Foto</th>
        <th>Kategori</th>
        <th>Stok</th>
        <th>Tanggal Pesan</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><img src="../uploads/<?= htmlspecialchars($row['image']) ?>" alt="foto" width="50"></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= $row['stock'] ?></td>
            <td><?= $row['order_date'] ?></td>
        </tr>
    <?php endwhile; ?>
</table>
