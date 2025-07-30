<?php
include '../koneksi.php';

$result = mysqli_query($conn, "SELECT books.*, categories.name AS category FROM books JOIN categories ON books.category_id = categories.id");

if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}
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

<!-- NAVBAR LANGSUNG DI SINI -->
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
                <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link text-danger" href="../logout.php">🔓 Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- ISI HALAMAN -->
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>📚 Daftar Buku</h2>
        <a href="books/create.php" class="btn btn-primary">+ Tambah Buku</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
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
                <?php while ($book = mysqli_fetch_assoc($result)): ?>
                    <tr>
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
                <?php endwhile ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
