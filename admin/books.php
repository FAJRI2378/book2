<?php
include '../koneksi.php';

$result = mysqli_query($conn, "SELECT books.*, categories.name AS category FROM books JOIN categories ON books.category_id = categories.id");

if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}

// Ambil data About Us
$aboutRes = mysqli_query($conn, "SELECT value FROM settings WHERE name='about'");
$about = mysqli_fetch_assoc($aboutRes);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Buku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table img {
            width: 80px;
            height: auto;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="#">BookStore</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link active" href="#">Daftar Buku</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="list_user.php">List User</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../admin/chat/index.php">💬 Chat</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../admin/order_list.php">Pesanan</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../kategori/index.php">Kategori</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="edit_about.php">Edit About Us</a>
        </li>
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link text-danger" href="../logout.php">🔓 Logout</a>
          </li>
        </ul>
      </ul>
    </div>
  </div>
</nav>

 <!-- ABOUT US -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">ℹ️ About Us</h5>
        </div>
        <div class="card-body">
            <?= nl2br(htmlspecialchars($about['value'] ?? 'Belum ada informasi About Us.')) ?>
        </div>
    </div>
</div>
<!-- ISI HALAMAN -->
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
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
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Gambar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
               <?php $no = 1; while ($book = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($book['title']) ?></td>
                    <td><?= htmlspecialchars($book['author']) ?></td>
                    <td><?= htmlspecialchars($book['category']) ?></td>
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
                        <a href="books/delete.php?id=<?= $book['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">Hapus</a>
                    </td>
                </tr>
               <?php endwhile; ?>
            </tbody>
        </table>
    </div>

   

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
