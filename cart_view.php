<?php
session_start();
include 'koneksi.php';

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

if (empty($cart)) {
    echo '<div class="container mt-5 text-center">
            <h3>🛒 Keranjang kosong</h3>
            <a href="index.php" class="btn btn-primary mt-3">← Kembali ke Toko</a>
          </div>';
    exit;
}

$ids = implode(",", array_map('intval', $cart));
$result = mysqli_query($conn, "SELECT * FROM books WHERE id IN ($ids)");
$total = 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        img {
            width: 80px;
            height: auto;
            object-fit: cover;
            border-radius: 6px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2 class="mb-4 text-center">🛒 Keranjang Belanja</h2>

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark text-center">
            <tr>
                <th>Judul</th>
                <th>Penulis</th>
                <th>Harga</th>
                <th>Gambar</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): 
                $total += $row['price'];
            ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['author']) ?></td>
                    <td>Rp<?= number_format($row['price'], 0, ',', '.') ?></td>
                    <td class="text-center">
                        <?php if (!empty($row['image'])): ?>
                            <img src="uploads/<?= $row['image'] ?>" alt="<?= $row['title'] ?>">
                        <?php else: ?>
                            <em>Tidak ada gambar</em>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <form action="buy.php" method="post" class="d-inline">
                            <input type="hidden" name="book_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-success btn-sm mb-1">Checkout</button>
                        </form>
                        <form action="hapus_keranjang.php" method="post" class="d-inline">
                            <input type="hidden" name="book_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('Yakin ingin menghapus dari keranjang?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr class="table-secondary fw-bold">
                <td colspan="2" class="text-end">Total:</td>
                <td colspan="3">Rp<?= number_format($total, 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-primary">← Kembali ke Daftar Buku</a>
    </div>
</div>

</body>
</html>
