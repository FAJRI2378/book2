<?php
session_start();
include 'koneksi.php';

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

if (empty($cart)) {
    echo "<p>Keranjang kosong.</p>";
    echo "<a href='index.php'>Kembali ke toko</a>";
    exit;
}

$ids = implode(",", array_map('intval', $cart));
$result = mysqli_query($conn, "SELECT * FROM books WHERE id IN ($ids)");
?>

<h2>Keranjang Belanja</h2>
<table border="1" cellpadding="8">
    <tr>
        <th>Judul</th>
        <th>Penulis</th>
        <th>Harga</th>
        <th>Aksi</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $row['title'] ?></td>
            <td><?= $row['author'] ?></td>
            <td>Rp<?= number_format($row['price']) ?></td>
            <td>
    <form action="checkout.php" method="post" style="display:inline;">
        <input type="hidden" name="book_id" value="<?= $row['id'] ?>">
        <button type="submit">Beli Sekarang</button>
    </form>
    <form action="hapus_keranjang.php" method="post" style="display:inline;">
        <input type="hidden" name="book_id" value="<?= $row['id'] ?>">
        <button type="submit" onclick="return confirm('Yakin ingin menghapus dari keranjang?')">Hapus</button>
    </form>
</td>


        </tr>
    <?php endwhile; ?>
    
</table>


<br>
<a href="index.php">← Kembali ke Daftar Buku</a>
