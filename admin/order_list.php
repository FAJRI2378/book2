<?php
session_start();
include '../koneksi.php';

// Ambil semua pesanan, join dengan users, books, dan categories
$sql = "SELECT orders.jumlah, orders.id, users.username, books.title, books.price, orders.order_date, books.image, categories.name AS kategori
        FROM orders
        JOIN users ON orders.user_id = users.id
        JOIN books ON orders.book_id = books.id
        JOIN categories ON books.category_id = categories.id
        ORDER BY orders.order_date DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4 text-center">📦 Daftar Pesanan Buku</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
    <table class="table table-bordered table-hover">
        <thead class="table-dark text-center">
            <tr>
                <th>No </th>
                <th>Nama Pengguna</th>
                <th>Judul Buku</th>
                <th>Harga</th>
                <th>Jumlah Barang</th>
                <th>Gambar</th>
                <th>Kategori</th>
                <th>Waktu Order</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td>Rp<?= number_format($row['price'], 0, ',', '.') ?></td>
                <td class="text-center"><?= (int)$row['jumlah'] ?></td>
                <td class="text-center">
                    <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" alt="Gambar Buku" width="60">
                </td>
                <td class="text-center"><?= htmlspecialchars($row['kategori']) ?></td>
                <td class="text-center"><?= date('d-m-Y H:i', strtotime($row['order_date'])) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="alert alert-warning text-center">Belum ada pesanan.</div>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="books.php" class="btn btn-secondary">← Kembali ke Dashboard</a>
    </div>
</div>
</body>
</html>
