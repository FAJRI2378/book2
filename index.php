<?php
session_start();
include 'koneksi.php';

// Cek session user
if (empty($_SESSION['user_id']) || empty($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$user_id  = $_SESSION['user_id'];

// Ambil daftar buku
$search = isset($_GET['search']) ? $_GET['search'] : '';
if ($search) {
    $stmt = $conn->prepare("
        SELECT books.*, categories.name AS category
        FROM books 
        JOIN categories ON books.category_id = categories.id
        WHERE books.title LIKE ?
    ");
    $like = "%$search%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $query  = "SELECT books.*, categories.name AS category 
               FROM books 
               JOIN categories ON books.category_id = categories.id";
    $result = mysqli_query($conn, $query);
}

// Ambil About Us
$aboutRes = mysqli_query($conn, "SELECT * FROM settings WHERE name='about'");
$about    = mysqli_fetch_assoc($aboutRes);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>BookStore - User</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
      position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0,0,0,0.6); z-index: -1;
    }
    .about {
      padding: 50px 20px; background-color: rgba(255,255,255,0.95);
      color: #333; margin: 30px auto; border-radius: 45px;
      max-width: 1000px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    .about h2 { text-align:center; }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"><strong>BookStore</strong></a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
        <li class="nav-item"><a class="nav-link" href="#daftar-buku">Daftar Buku</a></li>
        <li class="nav-item"><a class="nav-link" href="cart_view.php">Keranjang</a></li>
        <li class="nav-item"><a class="nav-link" href="orderan_saya.php">Pesanan Saya</a></li>
        <li class="nav-item"><a class="nav-link" href="chat.php">Chat Admin</a></li>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item"><span class="nav-link">👤 User ID: <?= htmlspecialchars($user_id) ?></span></li>
        <li class="nav-item">
          <button id="btn-logout" class="btn btn-link nav-link text-danger" style="padding:0;">
            🔓 Logout
          </button>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- About -->
<section class="about" id="about">
  <h2>About Us</h2>
  <div class="about-content text-center">
    <?php if (!empty($about['image'])): ?>
      <img src="uploads/<?= htmlspecialchars($about['image']) ?>" alt="About Us" class="mb-3">
    <?php endif; ?>
    <div class="about-text">
      <?= nl2br(htmlspecialchars($about['value'] ?? 'Belum ada informasi About Us.')) ?>
    </div>
  </div>
</section>

<!-- Daftar Buku -->
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
          <th>Stok</th>
          <th>Gambar</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['author']) ?></td>
            <td>Rp<?= number_format($row['price']) ?></td>
            <td><?= $row['stock'] ?></td>
            <td>
              <?php if (!empty($row['image'])): ?>
                <img src="uploads/<?= htmlspecialchars($row['image']) ?>" style="max-height:80px;">
              <?php else: ?><em>Tidak ada gambar</em><?php endif; ?>
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
      </tbody>
    </table>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById('btn-logout').addEventListener('click', function(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Yakin ingin logout?',
        text: "Anda akan keluar dari akun ini.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, logout',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'logout.php';
        }
    });
});
</script>

</body>
</html>
