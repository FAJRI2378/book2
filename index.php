<?php
session_start();
include 'koneksi.php';

// pencarian buku
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

// ambil konten About Us
$aboutRes = mysqli_query($conn, "SELECT * FROM settings WHERE name='about'");
$about = mysqli_fetch_assoc($aboutRes);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>BookStore</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    html { scroll-behavior: smooth; }
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding-top: 70px;
      color: white;
      background: url('assets/bg.jpg') no-repeat center center fixed;
      background-size: cover;
    }
    body::before {
      content: "";
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.6);
      z-index: -1;
    }
    .about {
      padding: 50px 20px;
      background-color: rgba(255,255,255,0.95);
      color: #333;
      margin: 30px auto;
      border-radius: 45px;
      max-width: 1000px;
      /* box-shadow: 0 4px 12px rgba(0,0,0,0.3); */
    }
    .about h2 { font-size: 28px; margin-bottom: 20px; font-weight: bold; text-align:center; }
    .about-content {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 30px;
      flex-wrap: wrap;
    }
    .about-content img {
      max-width: 350px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.4);
    }
    .about-text {
      background-color: #000000a7;
      max-width: 500px;
      text-align: center;
      font-size: 16px;
      line-height: 1.6;
      padding-bottom: 25px;
      border-bottom: 2px solid #ccc;
      color: #ccc;
      border-radius: 25px;
      text-decoration: none;
      box-shadow: #000000a7 0px 4px 10px;
    }
  </style>
</head>
<body>

<!-- ✅ Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"><strong>BookStore</strong></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
        <li class="nav-item"><a class="nav-link" href="#daftar-buku">Daftar Buku</a></li>
        <li class="nav-item"><a class="nav-link" href="cart_view.php">Keranjang</a></li>
        <li class="nav-item"><a class="nav-link" href="orderan_saya.php">Kelola Pesanan</a></li>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link text-danger" href="logout.php">🔓 Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- ✅ About Section -->
<section class="about" id="about">
  <h2>About Us</h2>
  <div class="about-content">
    <?php if (!empty($about['image'])): ?>
      <img src="uploads/<?= htmlspecialchars($about['image']) ?>" alt="About Us">
    <?php endif; ?>
    <div class="about-text">
      <?= nl2br(htmlspecialchars($about['value'] ?? 'Belum ada informasi About Us.')) ?>
    </div>
  </div>
</section>

<!-- ✅ Daftar Buku -->
<section id="daftar-buku" class="container text-dark">
  <h2 class="text-center my-4">📚 Daftar Buku</h2>
  <form method="GET" class="mb-3 text-center">
    <input type="text" name="search" placeholder="Cari judul buku..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit" class="btn btn-success">Cari</button>
  </form>

  <div class="table-responsive mb-5">
    <table class="table table-bordered table-striped bg-white">
      <thead class="table-dark">
        <tr>
          <th>Judul</th>
          <th>Penulis</th>
          <th>Harga</th>
          <th>Kategori</th>
          <th>Stok</th>
          <th>Gambar</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
              <td><?= htmlspecialchars($row['title']) ?></td>
              <td><?= htmlspecialchars($row['author']) ?></td>
              <td>Rp<?= number_format($row['price']) ?></td>
              <td><?= htmlspecialchars($row['category']) ?></td>
              <td><?= $row['stock'] ?></td>
              <td>
                <?php if (!empty($row['image'])): ?>
                  <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= $row['title'] ?>" style="max-height:80px;">
                <?php else: ?>
                  <em>Tidak ada gambar</em>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($row['stock'] > 0): ?>
                  <form method="POST" action="cart.php">
                    <input type="hidden" name="book_id" value="<?= $row['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-primary">Tambah Keranjang</button>
                  </form>
                <?php else: ?>
                  <button disabled class="btn btn-sm btn-secondary">Stok Habis</button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center">Tidak ada buku ditemukan.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
