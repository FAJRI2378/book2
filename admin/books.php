<?php
include '../includes/auth.php';
checkRole('admin');
include '../koneksi.php';

// === BAGIAN 1: HANDLE REQUEST AJAX UNTUK LIVE SEARCH ===
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    $search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');

    $query = "
      SELECT books.*, categories.name AS category
      FROM books
      LEFT JOIN categories ON books.category_id = categories.id
    ";

    if (!empty($search)) {
        $query .= " WHERE books.title LIKE '%$search%' 
                    OR books.author LIKE '%$search%' 
                    OR categories.name LIKE '%$search%'";
    }

    $result = mysqli_query($conn, $query);
    $no = 1;

    if (mysqli_num_rows($result) === 0) {
        echo "<tr><td colspan='8' class='text-center text-muted'>Tidak ada data ditemukan.</td></tr>";
    } else {
        while ($book = mysqli_fetch_assoc($result)) {
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
                <td><?= htmlspecialchars($book['category'] ?? '-') ?></td>
                <td>Rp <?= number_format($book['price'], 0, ',', '.') ?></td>
                <td><?= $book['stock'] ?></td>
                <td>
                    <?php if (!empty($book['image'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($book['image']) ?>" width="80" alt="<?= htmlspecialchars($book['title']) ?>">
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
            <?php
        }
    }
    exit; // Stop agar HTML utama tidak ikut dikirim
}

// === BAGIAN 2: JIKA BUKAN AJAX, TAMPILKAN HALAMAN UTAMA ===
$result = mysqli_query($conn, "
  SELECT books.*, categories.name AS category
  FROM books
  LEFT JOIN categories ON books.category_id = categories.id
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin - BookStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      body { background-color: #f8f9fa; }
      .table img { width: 80px; border-radius: 5px; }
      .search-box { max-width: 350px; }
      th, td { vertical-align: middle !important; }
    </style>
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
        <li class="nav-item"><a class="nav-link" href="../kategori/index.php">Kelola Kategori</a></li>
        <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">🔓 Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
    <h2>📚 Daftar Buku</h2>
    <div class="d-flex search-box mt-2 mt-md-0">
        <input type="text" id="searchInput" class="form-control me-2" 
               placeholder="Cari judul, penulis, kategori...">
        <a href="books/create.php" class="btn btn-success">+ Tambah Buku</a>
    </div>
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
      <tbody id="bookTable">
        <?php
        $no = 1;
        if (mysqli_num_rows($result) === 0):
          echo "<tr><td colspan='8' class='text-center text-muted'>Tidak ada data buku.</td></tr>";
        else:
          while ($book = mysqli_fetch_assoc($result)):
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
          <td><?= htmlspecialchars($book['category'] ?? '-') ?></td>
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
        <?php endwhile; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
// === Live Search Real-time ===
const input = document.getElementById('searchInput');
const tableBody = document.getElementById('bookTable');
let timeout = null;

input.addEventListener('keyup', () => {
  clearTimeout(timeout);
  timeout = setTimeout(() => {
    const keyword = input.value.trim();
    fetch(`books.php?ajax=1&search=${encodeURIComponent(keyword)}`)
      .then(res => res.text())
      .then(html => {
        tableBody.innerHTML = html;
      })
      .catch(() => {
        tableBody.innerHTML = "<tr><td colspan='8' class='text-center text-danger'>Gagal memuat data.</td></tr>";
      });
  }, 300);
});
</script>
</body>
</html>
