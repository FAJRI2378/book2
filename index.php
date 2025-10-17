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

$user_id = $_SESSION['user_id'];
$username = '';

// ==============================
// 👤 AMBIL USERNAME
// ==============================
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($username);
    $stmt->fetch();
    $stmt->close();
} else {
    error_log("Error preparing username query: " . $conn->error);
    $username = 'User';
}

// ==============================
// 🔔 HITUNG PESAN BELUM DIBACA
// ==============================
$notif_unread = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM chats WHERE receiver_id = ? AND sender_id = 2 AND is_read = 0");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($notif_unread);
    $stmt->fetch();
    $stmt->close();
}

// ==============================
// 📚 AMBIL ABOUT US
// ==============================
$about = ['value' => 'Belum ada informasi About Us.', 'image' => ''];
$stmt = $conn->prepare("SELECT value, image FROM settings WHERE name = ?");
if ($stmt) {
    $name = 'about';
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $about = $row;
    }
    $stmt->close();
}

// ==============================
// 🔍 AJAX SEARCH HANDLER
// ==============================
if(isset($_GET['ajax_search'])){
    $search = trim($_GET['ajax_search']);
    $books = [];

    if ($search) {
        $stmt = $conn->prepare("
            SELECT books.*, categories.name AS category
            FROM books
            JOIN categories ON books.category_id = categories.id
            WHERE books.title LIKE ? OR books.author LIKE ?
            ORDER BY books.title ASC
        ");
        if ($stmt) {
            $like = "%$search%";
            $stmt->bind_param("ss", $like, $like);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }
            $stmt->close();
        }
    } else {
        $stmt = $conn->prepare("
            SELECT books.*, categories.name AS category
            FROM books
            JOIN categories ON books.category_id = categories.id
            ORDER BY books.title ASC
        ");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }
            $stmt->close();
        }
    }

    if (empty($books)) {
        echo '<div class="no-books">
            <i class="fas fa-search fa-shake"></i>
            <h4>Tidak ada buku yang ditemukan</h4>
            <p>Coba cari dengan kata kunci lain atau lihat semua buku.</p>
        </div>';
    } else {
        echo '<div class="row g-4">';
        foreach ($books as $row) {
            echo '<div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card book-card h-100">
                    <img src="'.(!empty($row['image'])?'uploads/'.htmlspecialchars($row['image']):'assets/no-image.png').'" class="card-img-top" alt="'.htmlspecialchars($row['title']).'" loading="lazy">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">'.htmlspecialchars($row['title']).'</h5>
                        <p class="card-text"><i class="fas fa-user me-1"></i><strong>Penulis:</strong> '.htmlspecialchars($row['author']).'</p>
                        <p class="card-text"><i class="fas fa-tag me-1"></i><strong>Kategori:</strong> '.htmlspecialchars($row['category']).'</p>
                        <p class="card-text"><i class="fas fa-dollar-sign me-1"></i><strong class="price-tag">Rp'.number_format($row['price'],0,',','.').'</strong></p>
                        <p class="card-text mb-3"><i class="fas fa-box me-1"></i><strong>Stok:</strong> 
                            <span class="stock-tag '.($row['stock']>0?'stock-available':'stock-out').'">'.$row['stock'].' unit</span>
                        </p>
                        <div class="mt-auto">';
            if($row['stock']>0){
                echo '<form method="POST" action="cart.php">
                        <input type="hidden" name="book_id" value="'.$row['id'].'">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-cart-plus me-1"></i>Tambah ke Keranjang</button>
                      </form>';
            } else {
                echo '<button disabled class="btn btn-secondary w-100"><i class="fas fa-ban me-1"></i>Stok Habis</button>';
            }
            echo '</div></div></div></div>';
        }
        echo '</div>';
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BookStore - Dashboard User</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
/* (Sama seperti sebelumnya, CSS navbar, books, about, search) */
:root { --primary-color:#007bff; --secondary-color:#6c757d; --success-color:#28a745; --danger-color:#dc3545; --bg-overlay: rgba(0,0,0,0.7);}
html { scroll-behavior:smooth; }
body { font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin:0; padding-top:70px; color:#333; background: url('assets/bg.jpg') no-repeat center center fixed; background-size: cover; min-height:100vh;}
body::before { content:""; position:fixed; top:0; left:0; width:100%; height:100%; background:var(--bg-overlay); z-index:-1; }
/* Navbar */
.navbar { backdrop-filter: blur(10px); background-color: rgba(0,0,0,0.9) !important; }
.navbar-brand { font-size:1.5rem; font-weight:bold; }
.nav-link { transition: color 0.2s ease; }
.nav-link:hover { color: var(--primary-color) !important; }
/* About Section */
.about { padding:50px 30px; background-color: rgba(255,255,255,0.98); color:#333; margin:30px auto; border-radius:20px; max-width:1000px; box-shadow:0 8px 32px rgba(0,0,0,0.3); backdrop-filter:blur(10px);}
.about h2 { text-align:center; color: var(--primary-color); margin-bottom:30px; }
.about img { max-width:100%; height:auto; border-radius:15px; box-shadow:0 4px 15px rgba(0,0,0,0.2); }
.about-text { line-height:1.8; font-size:1.1rem; text-align:justify; }
/* Books */
.book-card { border-radius:15px; overflow:hidden; transition:all 0.3s ease; border:none; background: rgba(255,255,255,0.95); backdrop-filter:blur(10px); box-shadow:0 4px 15px rgba(0,0,0,0.1);}
.book-card:hover { transform: translateY(-10px) scale(1.02); box-shadow:0 12px 30px rgba(0,0,0,0.2);}
.book-card .card-img-top { height:280px; object-fit:cover; transition: transform 0.3s ease;}
.book-card:hover .card-img-top { transform: scale(1.05);}
.card-title { font-size:1.2rem; font-weight:600; min-height:50px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;}
.card-text { font-size:0.95rem; color:#555; margin-bottom:0.5rem;}
.price-tag { font-size:1.1rem; font-weight:bold; color:var(--success-color);}
.stock-tag { padding:0.25rem 0.5rem; border-radius:20px; font-size:0.85rem; font-weight:500; }
.stock-available { background-color:#d4edda; color:#155724; }
.stock-out { background-color:#f8d7da; color:#721c24; }
.book-card .btn { border-radius:25px; font-weight:600; padding:0.5rem 1rem; transition: all 0.2s ease;}
.book-card .btn:hover { transform:translateY(-2px);}
.no-books { text-align:center; padding:50px; color:#666; }
.no-books i { font-size:4rem; color:var(--secondary-color); margin-bottom:20px; }
/* Search */
.search-form { max-width:500px; margin:0 auto 30px; }
.search-form input { border-radius:25px 0 0 25px; border:2px solid var(--primary-color); padding:0.75rem 1rem; }
.search-form button { border-radius:0 25px 25px 0; background-color:var(--primary-color); border:none; padding:0.75rem 1.5rem; }
.search-form button:hover { background-color:#0056b3; }
@media (max-width:768px) { .about { margin:20px 10px; padding:30px 20px; } .book-card .card-img-top { height:220px; } .search-form { max-width:100%; } }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"><i class="fas fa-book-open me-2"></i>BookStore</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="about.php"><i class="fas fa-info-circle me-1"></i>About</a></li>
        <li class="nav-item"><a class="nav-link" href="#daftar-buku"><i class="fas fa-list me-1"></i>Daftar Buku</a></li>
        <li class="nav-item"><a class="nav-link" href="cart_view.php"><i class="fas fa-shopping-cart me-1"></i>Keranjang</a></li>
        <li class="nav-item"><a class="nav-link" href="orderan_saya.php"><i class="fas fa-box me-1"></i>Pesanan Saya</a></li>
        <li class="nav-item dropdown position-relative">
          <a class="nav-link dropdown-toggle" href="#" id="chatDropdown" role="button" data-bs-toggle="dropdown">
            <i class="fas fa-comments me-1"></i>Chat Admin
            <?php if ($notif_unread > 0): ?>
              <span class="badge bg-danger rounded-pill"><?= $notif_unread ?></span>
            <?php endif; ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-dark">
            <li>
              <a class="dropdown-item" href="chat.php?admin_id=2">
                <i class="fas fa-globe me-2"></i>Chat Web
                <?php if ($notif_unread > 0): ?>
                  <span class="badge bg-danger float-end"><?= $notif_unread ?></span>
                <?php endif; ?>
              </a>
            </li>
            <li><a class="dropdown-item" href="https://wa.me/6287872594546" target="_blank"><i class="fab fa-whatsapp me-2"></i>WhatsApp</a></li>
            <li><a class="dropdown-item" href="mailto:armanfajri008@gmail.com" target="_blank"><i class="fas fa-envelope me-2"></i>Email</a></li>
          </ul>
        </li>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item"><span class="nav-link"><i class="fas fa-user me-1"></i><?= htmlspecialchars($username) ?></span></li>
        <li class="nav-item">
          <button id="btn-logout" class="btn btn-link nav-link text-danger"><i class="fas fa-sign-out-alt me-1"></i>Logout</button>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- About Section -->
<!-- <section class="about" id="about">
  <h2><i class="fas fa-building me-2"></i>About Us</h2>
  <?php if (!empty($about['image'])): ?>
    <img src="uploads/<?= htmlspecialchars($about['image']) ?>" alt="About Us" class="img-fluid mb-4" loading="lazy">
  <?php endif; ?>
  <div class="about-text"><?= nl2br(htmlspecialchars($about['value'])) ?></div>
</section> -->

<!-- Books Section -->
<section id="daftar-buku" class="container py-5">
  <h2 class="text-center mb-4"><i class="fas fa-book me-2"></i>Daftar Buku</h2>
  
  <div class="search-form">
    <div class="input-group">
      <input type="text" id="searchInput" class="form-control" placeholder="Cari judul atau penulis buku...">
      <button class="btn btn-primary"><i class="fas fa-search"></i></button>
    </div>
  </div>

  <div id="bookResults"></div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.getElementById('btn-logout').addEventListener('click', function(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Yakin ingin logout?',
        text: "Anda akan keluar dari akun ini.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, logout!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'logout.php';
        }
    });
});

// Smooth scroll for nav links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({behavior: 'smooth'});
    });
});

// ================================
// 🔍 AJAX SEARCH
// ================================
function loadBooks(query='') {
    $.get('', {ajax_search: query}, function(data){
        $('#bookResults').html(data);
    });
}

// Load all books initially
loadBooks();

// Search on typing
$('#searchInput').on('input', function(){
    loadBooks($(this).val());
});
</script>
</body>
</html>
