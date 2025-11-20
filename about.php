<?php
session_start();
include 'koneksi.php';

// ==============================
// 🔒 CEK AKSES USER
// ==============================
if (empty($_SESSION['user_id']) || empty($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

// ==============================
// 👤 AMBIL USERNAME
// ==============================
$user_id = $_SESSION['user_id'];
$username = '';
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

// ==============================
// 📄 ABOUT US DATA
// ==============================
$about = ['value' => 'Belum ada informasi About Us.', 'image' => ''];
$stmt = $conn->prepare("SELECT value, image FROM settings WHERE name = ?");
$name = 'about';
$stmt->bind_param("s", $name);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $about = $row;
}
$stmt->close();

// ==============================
// 📚 PAGINATION PRODUK
// ==============================
$limit = 4; // jumlah produk per halaman
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Hitung total data buku
$total_books = $conn->query("SELECT COUNT(*) as total FROM books")->fetch_assoc()['total'];
$total_pages = ceil($total_books / $limit);

$stmt = $conn->prepare("
    SELECT books.*, categories.name AS category 
    FROM books 
    JOIN categories ON books.category_id = categories.id 
    ORDER BY books.title ASC 
    LIMIT ? OFFSET ?
");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$books = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tentang Kami - BookStore</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: url('assets/bg.jpg') no-repeat center center fixed;
  background-size: cover;
  color: #333;
  margin: 0;
  padding-top: 70px;
}
body::before {
  content: "";
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0, 0, 0, 0.7);
  z-index: -1;
}
.navbar {
  backdrop-filter: blur(10px);
  background-color: rgba(0, 0, 0, 0.9) !important;
}
.about {
  padding: 50px 30px;
  background-color: rgba(255,255,255,0.98);
  border-radius: 20px;
  width: 2000px;
  height: auto;
  margin: 30px auto;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}
.about h2 {
  text-align: center;
  color: #007bff;
  margin-bottom: 30px;
}
.about img {
  width: 500px;
  height: auto;
  border-radius: 15px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  margin-bottom: 20px;
}
.about-text {
  text-align: justify;
  line-height: 1.8;
  font-size: 1.1rem;
}
/* Produk */
.book-card {
  border-radius: 15px;
  overflow: hidden;
  transition: all 0.3s ease;
  border: none;
  background: rgba(255,255,255,0.95);
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.book-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 25px rgba(0,0,0,0.2);
}
.book-card img {
  height: 260px;
  object-fit: cover;
}
.book-card .card-body {
  display: flex;
  flex-direction: column;
}
.price-tag { color: #28a745; font-weight: bold; }
.stock-out { color: red; }
/* Pagination */
.pagination {
  margin-top: 40px;
}
.pagination .page-link {
  border-radius: 10px;
  margin: 0 5px;
  border: none;
  font-weight: 600;
  color: #007bff;
}
.pagination .page-item.active .page-link {
  background-color: #007bff;
  color: white;
  box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}
.pagination .page-link:hover {
  background-color: #e9ecef;
}
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="user_dashboard.php"><i class="fas fa-book-open me-2"></i>BookStore</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
        <li class="nav-item"><a class="nav-link active" href="about.php">Tentang Kami</a></li>
      </ul>
    </div>
  </div>
</nav>


<!-- ABOUT US -->
<section class="about text-center">
  <!-- <h2><i class="fas fa-building me-2"></i>  BOOK STORE</h2> -->
   <h2>BOOK STORE</h2>
  <?php if (!empty($about['image'])): ?>
    <img src="uploads/<?= htmlspecialchars($about['image']) ?>" alt="About Us">
  <?php endif; ?>
<div class="about-text mt-3" id="aboutText">
  <?php
    $rawText = htmlspecialchars($about['value']);
    $limit = 20000; // batas karakter sebelum "Read More"

    // Hapus HTML tapi pertahankan baris baru agar bisa diubah jadi <br>
    $plainText = strip_tags($about['value']);

    if (strlen($plainText) > $limit) {
        $shortText = nl2br(substr($plainText, 0, $limit)) . '...';
        $fullText  = nl2br($plainText);

        echo '<span id="shortText">' . $shortText . '</span>';
        echo '<span id="fullText" style="display:none;">' . $fullText . '</span>';
    } else {
        echo nl2br($plainText);
    }
  ?>
</div>

  <!-- <?php if (strlen(strip_tags($about['value'])) > $limit): ?>
    <button id="readMoreBtn" class="btn btn-outline-primary mt-3">Baca Selengkapnya</button>
  <?php endif; ?> -->
</section>


<!-- PRODUK -->
<section class="container my-5">
  <h2 class="text-center mb-4"><i class="fas fa-book me-2"></i>Produk Kami</h2>
  <div class="row g-4">
    <?php if (empty($books)): ?>
      <div class="col-12 text-center">
        <p class="text-muted">Belum ada produk tersedia.</p>
      </div>
    <?php else: ?>
      <?php foreach ($books as $b): ?>
        <div class="col-md-6 col-lg-4 col-xl-3">
          <div class="card book-card h-100">
            <img src="<?= !empty($b['image']) ? 'uploads/'.htmlspecialchars($b['image']) : 'assets/no-image.png' ?>" class="card-img-top" alt="<?= htmlspecialchars($b['title']) ?>">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($b['title']) ?></h5>
              <p><strong>Penulis:</strong> <?= htmlspecialchars($b['author']) ?></p>
              <p><strong>Kategori:</strong> <?= htmlspecialchars($b['category']) ?></p>
              <p><strong>Harga:</strong> <span class="price-tag">Rp<?= number_format($b['price'], 0, ',', '.') ?></span></p>
              <p><strong>Stok:</strong> 
                <?= $b['stock'] > 0 
                  ? '<span class="text-success">'.$b['stock'].' unit</span>' 
                  : '<span class="stock-out">Habis</span>' ?>
              </p>
              <div class="mt-auto">
                <?php if ($b['stock'] > 0): ?>
                  <form method="POST" action="cart.php">
                    <input type="hidden" name="book_id" value="<?= $b['id'] ?>">
                    <button type="submit" class="btn btn-primary w-100">
                      <i class="fas fa-cart-plus me-1"></i> Tambah ke Keranjang
                    </button>
                  </form>
                <?php else: ?>
                  <button class="btn btn-secondary w-100" disabled>
                    <i class="fas fa-ban me-1"></i> Stok Habis
                  </button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>


    <!-- PAGINATION -->
  <!-- <?php if ($total_pages >= 1): ?>
  <nav aria-label="Pagination" class="mt-5">
    <ul class="pagination justify-content-center"> -->

      <!-- Tombol Sebelumnya -->
      <?php if ($page > 1): ?>
        <li class="page-item">
          <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Sebelumnya">
            <span aria-hidden="true">&laquo;</span>
          </a>
        </li>
      <?php else: ?>
        <li class="page-item disabled">
          <span class="page-link">&laquo;</span>
        </li>
      <?php endif; ?>

      <!-- Nomor Halaman -->
      <?php for ($i = 1; $i <= max(1, $total_pages); $i++): ?>
        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
          <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>

      <!-- Tombol Berikutnya -->
      <?php if ($page < $total_pages): ?>
        <li class="page-item">
          <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Berikutnya">
            <span aria-hidden="true">&raquo;</span>
          </a>
        </li>
      <?php else: ?>
        <li class="page-item disabled">
          <span class="page-link">&raquo;</span>
        </li>
      <?php endif; ?>

    </ul>
  </nav>
  <?php endif; ?>

</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
  const btn = document.getElementById('readMoreBtn');
  const shortText = document.getElementById('shortText');
  const fullText = document.getElementById('fullText');

  if (btn) {
    btn.addEventListener('click', function() {
      if (fullText.style.display === 'none') {
        fullText.style.display = 'inline';
        shortText.style.display = 'none';
        btn.textContent = 'Tampilkan Lebih Sedikit';
      } else {
        fullText.style.display = 'none';
        shortText.style.display = 'inline';
        btn.textContent = 'Baca Selengkapnya';
      }
    });
  }
});
</script>
</body>
</html>
