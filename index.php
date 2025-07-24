<?php
session_start();
include 'koneksi.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';

if ($search) {
    $query = "SELECT books.*, categories.name AS category 
              FROM books 
              JOIN categories ON books.category_id = categories.id 
              WHERE books.title LIKE '%$search%'";
} else {
    $query = "SELECT books.*, categories.name AS category 
              FROM books 
              JOIN categories ON books.category_id = categories.id";
}

$result = mysqli_query($conn, $query);
?>

<h2>Daftar Buku</h2>

<a href="cart_view.php">Lihat Keranjang 🛒</a><br><br>

<table border="1" cellpadding="8">
    <tr>
        <th>Judul</th>
        <th>Penulis</th>
        <th>Harga</th>
        <th>Kategori</th>
        <th>Gambar</th>
        <th>Aksi</th> <!-- Tambahkan kolom Aksi -->
    </tr>
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?= $row['title'] ?></td>
                <td><?= $row['author'] ?></td>
                <td>Rp<?= number_format($row['price']) ?></td>
                <td><?= $row['category'] ?></td>
                <td>
                    <?php if (!empty($row['image'])): ?>
                        <img src="uploads/<?= $row['image'] ?>" alt="<?= $row['title'] ?>" width="80">
                    <?php else: ?>
                        <span>Tidak ada gambar</span>
                    <?php endif; ?>
                </td>
                <td>
                    <form method="POST" action="cart.php" style="display:inline;">
                        <input type="hidden" name="book_id" value="<?= $row['id'] ?>">
                        <button type="submit">Tambah ke Keranjang</button>
                    </form>
                    <form method="POST" action="buy.php" style="display:inline;">
                        <input type="hidden" name="book_id" value="<?= $row['id'] ?>">
                        <button type="submit">Beli</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    <?php else: ?>
        <tr>
            <td colspan="6">Tidak ada buku ditemukan.</td>
        </tr>
    <?php endif; ?>
</table>
