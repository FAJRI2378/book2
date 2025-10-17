<?php
session_start();
include 'koneksi.php';

// Inisialisasi cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ============ LOGIKA HAPUS ITEM ============
if (isset($_POST['hapus']) && isset($_POST['book_id'])) {
    $bookId = (int)$_POST['book_id'];
    if (isset($_SESSION['cart'][$bookId])) {
        unset($_SESSION['cart'][$bookId]);
    }
    header("Location: cart_view.php");
    exit;
}
// ===========================================

// Ambil isi keranjang
$cart = $_SESSION['cart'];
$books = [];
$total = 0;

if (!empty($cart)) {
    $ids = implode(",", array_map('intval', array_keys($cart)));
    $result = mysqli_query($conn, "SELECT * FROM books WHERE id IN ($ids)");
    while ($row = mysqli_fetch_assoc($result)) {
        $row['jumlah'] = $cart[$row['id']]; // simpan jumlah dari session
        $books[] = $row;
        $total += $row['price'] * $row['jumlah'];
    }
}
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
        body {
            background-color: #cfb8b8ff;
            font-family: Arial, sans-serif;
        }
        .content-wrapper {
            max-width: 1000px;
            margin: 40px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

<div class="content-wrapper">
    <h2 class="mb-4 text-center">🛒 Keranjang Belanja</h2>

    <?php if (empty($books)): ?>
        <div class="text-center p-5">
            <h3>Keranjang kosong</h3>
            <a href="index.php" class="btn btn-primary mt-3">← Kembali ke Toko</a>
        </div>
    <?php else: ?>
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>Judul</th>
                    <th>Penulis</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                    <th>Gambar</th>
                    <th>Checkout</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['author']) ?></td>
                        <td>Rp<?= number_format($row['price'], 0, ',', '.') ?></td>
                        <td><?= $row['jumlah'] ?></td>
                        <td>Rp<?= number_format($row['price'] * $row['jumlah'], 0, ',', '.') ?></td>
                        <td class="text-center">
                            <?php if (!empty($row['image'])): ?>
                                <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['title']) ?>">
                            <?php else: ?>
                                <em>Tidak ada gambar</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <!-- Form checkout untuk item ini -->
                            <form action="proses_checkout.php" method="post">
                                <input type="hidden" name="book_id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="jumlah" value="<?= $row['jumlah'] ?>">

                                <div class="mb-2">
                                    <label>Metode Pembayaran</label>
                                    <select name="payment_method" class="form-select form-select-sm" required>
                                        <option value="">Pilih Pembayaran</option>
                                        <option value="Transfer Bank">Transfer Bank</option>
                                        <option value="COD">COD (Bayar di Tempat)</option>
                                        <option value="E-Wallet">E-Wallet (OVO, DANA, GoPay)</option>
                                    </select>
                                </div>

                                <div class="mb-2">
                                    <input type="text" name="shipping_address" class="form-control" placeholder="Alamat Pengiriman" required>
                                </div>

                                <div class="mb-2">
                                    <select name="shipping_method" class="form-select" required>
                                        <option value="">Pilih Metode Pengiriman</option>
                                        <option value="Kurir">Kurir</option>
                                        <option value="Ambil di Toko">Ambil di Toko</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-success btn-sm w-100">Checkout</button>
                            </form>
                        </td>
                        <td class="text-center">
                            <!-- Tombol hapus langsung di sini -->
                            <form method="post" class="d-inline">
                                <input type="hidden" name="book_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="hapus" class="btn btn-danger btn-sm" 
                                        onclick="return confirm('Yakin ingin menghapus dari keranjang?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="table-secondary fw-bold">
                    <td colspan="4" class="text-end">Total (semua barang):</td>
                    <td colspan="4">Rp<?= number_format($total, 0, ',', '.') ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-primary">← Kembali ke Daftar Buku</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
