<?php
include '../koneksi.php';

$result = mysqli_query($conn, "SELECT * FROM books");
if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}

// Ambil data About Us
$aboutRes = mysqli_query($conn, "SELECT * FROM settings WHERE name='about'");
$about = mysqli_fetch_assoc($aboutRes);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin - BookStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>.table img { width: 80px; border-radius: 5px; }</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="#">BookStore Admin</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link active" href="#">Daftar Buku</a></li>
        <li class="nav-item"><a class="nav-link" href="list_user.php">List User</a></li>
        <li class="nav-item"><a class="nav-link" href="../admin/chat/index.php">💬 Chat</a></li>
        <li class="nav-item"><a class="nav-link" href="../admin/order_list.php">Pesanan</a></li>
        <li class="nav-item"><a class="nav-link" href="edit_about.php">✏️ Edit About Us</a></li>
        <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">🔓 Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<section class="about" id="about">
  <h2>About Us</h2>
  <div class="about-content text-center">
    <?php if (!empty($about['image'])): ?>
      <img src="../uploads/<?= htmlspecialchars($about['image']) ?>" style="max-width:200px;">
    <?php endif; ?>
    <p><?= nl2br(htmlspecialchars($about['value'] ?? 'Belum ada informasi About Us.')) ?></p>
  </div>
</section>

<div class="container mt-4">
    <div class="d-flex justify-content-between mb-3">
        <h2>📚 Daftar Buku</h2>
        <a href="books/create.php" class="btn btn-primary">+ Tambah Buku</a>
    </div>
    <div class="table-responsive mb-5">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Judul</th>
                    <th>Penulis</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Gambar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
               <?php $no = 1; while ($book = mysqli_fetch_assoc($result)): ?>
                <?php
                  $cekOrder = mysqli_query($conn, "
                      SELECT COUNT(*) AS jml 
                      FROM orders 
                      WHERE book_id = '{$book['id']}'
                        AND status NOT IN ('sampai', 'cancelled')
                  ");
                  $cek = mysqli_fetch_assoc($cekOrder);
                  $adaDalamPerjalanan = $cek['jml'] > 0;
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($book['title']) ?></td>
                    <td><?= htmlspecialchars($book['author']) ?></td>
                    <td>Rp <?= number_format($book['price'], 0, ',', '.') ?></td>
                    <td><?= $book['stock'] ?></td>
                    <td>
                        <?php if (!empty($book['image'])): ?>
                            <img src="../uploads/<?= htmlspecialchars($book['image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                        <?php else: ?>
                            <span class="text-muted">Tidak ada gambar</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="books/edit.php?id=<?= $book['id'] ?>" class="btn btn-sm btn-warning mb-1">Edit</a>
                        <?php if ($adaDalamPerjalanan): ?>
                            <button class="btn btn-sm btn-secondary" disabled>Hapus (Menunggu)</button>
                        <?php else: ?>
                            <a href="books/delete.php?id=<?= $book['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">Hapus</a>
                        <?php endif; ?>
                    </td>
                </tr>
               <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
