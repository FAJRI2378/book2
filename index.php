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

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>BookStore</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    html {
      scroll-behavior: smooth;
    }
    body {
      font-family: Arial, sans-serif;
      background-color: #f3f4f6;
      margin: 0;
      padding-top: 70px; /* offset navbar */
    }
    header {
      background-color: #2563eb;
      color: white;
      padding: 40px;
      text-align: center;
    }
    .about {
      padding: 40px 20px;
      background-color: #ffffff;
      text-align: center;
    }
    .about h2 {
      font-size: 26px;
      color: #1f2937;
      margin-bottom: 10px;
    }
    .about p {
      color: #4b5563;
      max-width: 700px;
      margin: 0 auto;
    }
    .search-bar {
      text-align: center;
      margin: 30px 0 20px;
    }
    .search-bar input[type="text"] {
      width: 300px;
      padding: 8px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }
    .search-bar button {
      padding: 8px 16px;
      border: none;
      background-color: #10b981;
      color: white;
      border-radius: 6px;
      cursor: pointer;
    }
    h2.section-title {
      text-align: center;
      margin-top: 40px;
      color: #1f2937;
    }
    table {
      width: 90%;
      margin: 20px auto;
      border-collapse: collapse;
      background: white;
      border-radius: 12px;
      overflow: hidden;
    }
    th, td {
      padding: 12px 16px;
      text-align: left;
      border-bottom: 1px solid #e5e7eb;
    }
    th {
      background-color: #f9fafb;
      color: #111827;
    }
    tr:hover {
      background-color: #f1f5f9;
    }
    img {
      max-height: 80px;
      border-radius: 6px;
    }
    .aksi-btn {
      display: flex;
      gap: 8px;
    }
    .aksi-btn form button {
      padding: 6px 12px;
      border: none;
      background-color: #3b82f6;
      color: white;
      border-radius: 6px;
      cursor: pointer;
    }
    .aksi-btn form button:hover {
      background-color: #2563eb;
    }
    .whatsapp {
      text-align: center;
      margin: 40px 0;
    }
    .whatsapp a {
      text-decoration: none;
      color: #10b981;
      font-weight: bold;
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
        <li class="nav-item">
          <a class="nav-link" href="#about">About</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#daftar-buku">Daftar Buku</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="cart_view.php">Keranjang</a>
        </li>
      </ul>

      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link text-danger" href="logout.php">🔓 Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- ✅ Header -->
 <section class="about" id="about">
<header>
  <h1>Selamat Datang di Toko Buku Online</h1>
</header>
 </section>

<!-- ✅ About Section -->
<section class="about" id="">
  <h2>Tentang Kami</h2>
  <p>
    Toko Buku Online menyediakan berbagai buku berkualitas, dari fiksi hingga non-fiksi, buku pelajaran, dan literatur lainnya. 
    Dapatkan kemudahan berbelanja dan pelayanan cepat langsung dari rumah!
  </p>
</section>

<!-- ✅ Daftar Buku Section -->
<section id="daftar-buku">
  <div class="search-bar">
    <form method="GET">
      <input type="text" name="search" placeholder="Cari judul buku..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit">Cari</button>
    </form>
  </div>

  <h2 class="section-title">📚 Daftar Buku</h2>

  <table>
    <tr>
      <th>Judul</th>
      <th>Penulis</th>
      <th>Harga</th>
      <th>Kategori</th>
      <th>Stok</th>
      <th>Gambar</th>
      <th>Aksi</th>
    </tr>
    <?php if (mysqli_num_rows($result) > 0): ?>
      <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
          <td><?= $row['title'] ?></td>
          <td><?= $row['author'] ?></td>
          <td>Rp<?= number_format($row['price']) ?></td>
          <td><?= $row['category'] ?></td>
          <td><?= $row['stock'] ?></td>
          <td>
            <?php if (!empty($row['image'])): ?>
              <img src="uploads/<?= $row['image'] ?>" alt="<?= $row['title'] ?>">
            <?php else: ?>
              <em>Tidak ada gambar</em>
            <?php endif; ?>
          </td>
          <td>
           <div class="aksi-btn">
  <?php if ($row['stock'] > 0): ?>
    <form method="POST" action="cart.php">
      <input type="hidden" name="book_id" value="<?= $row['id'] ?>">
      <button type="submit">Tambah</button>
    </form>
    <form method="POST" action="buy.php">
      <input type="hidden" name="book_id" value="<?= $row['id'] ?>">
      <button type="submit" style="background-color: #f59e0b;">Beli</button>
    </form>
  <?php else: ?>
    <button disabled style="background-color: #9ca3af; color: white; padding: 6px 12px; border-radius: 6px; cursor: not-allowed;">
      Stok Habis
    </button>
  <?php endif; ?>
</div>
    

            </div>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr>
        <td colspan="7" style="text-align: center;">Tidak ada buku ditemukan.</td>
      </tr>
    <?php endif; ?>
  </table>

  <div class="whatsapp">
    <p>🔔 Butuh bantuan? <a href="https://wa.me/6287872594546" target="_blank">Chat Admin via WhatsApp</a></p>
  </div>
</section>

<div class="text-center my-4">
  <a href="admin/chat/index.php" class="btn btn-outline-success">💬 Chat Admin</a>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
