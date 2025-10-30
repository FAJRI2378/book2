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
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

// ==============================
// 🔔 CEK CHAT BARU (AJAX POLLING)
// ==============================
if (isset($_GET['check_new_chat'])) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM chats WHERE receiver_id = ? AND sender_id = 2 AND is_read = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($unread);
    $stmt->fetch();
    $stmt->close();
    echo $unread;
    exit;
}

// ==============================
// 📚 AMBIL KATEGORI
// ==============================
$categories = [];
$result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
while ($row = $result->fetch_assoc()) $categories[] = $row;

// ==============================
// 📚 AJAX SEARCH + FILTER
// ==============================
if (isset($_GET['ajax_filter'])) {
    $search = trim($_GET['search'] ?? '');
    $category = $_GET['category'] ?? '';
    $sort = $_GET['sort'] ?? '';
    $min_price = $_GET['min_price'] ?? '';
    $max_price = $_GET['max_price'] ?? '';

    $query = "
        SELECT books.*, categories.name AS category
        FROM books
        JOIN categories ON books.category_id = categories.id
        WHERE 1=1
    ";

    if ($search) {
        $searchEsc = "%$search%";
        $query .= " AND (books.title LIKE '$searchEsc' OR books.author LIKE '$searchEsc')";
    }

    if ($category !== '') {
        $query .= " AND categories.id = " . intval($category);
    }

    if ($min_price !== '' && $max_price !== '') {
        $query .= " AND books.price BETWEEN " . intval($min_price) . " AND " . intval($max_price);
    }

    if ($sort === 'low_high') {
        $query .= " ORDER BY books.price ASC";
    } elseif ($sort === 'high_low') {
        $query .= " ORDER BY books.price DESC";
    } else {
        $query .= " ORDER BY books.title ASC";
    }

    $result = $conn->query($query);

    if ($result->num_rows == 0) {
        echo '<div class="no-books">
                <i class="fas fa-search fa-shake"></i>
                <h4>Tidak ada buku yang ditemukan</h4>
                <p>Coba ubah filter atau kata kunci pencarian.</p>
              </div>';
    } else {
        echo '<div class="row g-4">';
        while ($row = $result->fetch_assoc()) {
            echo '<div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="card book-card h-100">
                      <img src="'.(!empty($row['image'])?'uploads/'.htmlspecialchars($row['image']):'assets/no-image.png').'" class="card-img-top" alt="'.htmlspecialchars($row['title']).'">
                      <div class="card-body d-flex flex-column">
                        <h5 class="card-title">'.htmlspecialchars($row['title']).'</h5>
                        <p class="card-text"><i class="fas fa-user me-1"></i>'.htmlspecialchars($row['author']).'</p>
                        <p class="card-text"><i class="fas fa-tag me-1"></i>'.htmlspecialchars($row['category']).'</p>
                        <p class="card-text"><strong class="price-tag">Rp '.number_format($row['price'],0,',','.').'</strong></p>
                        <div class="mt-auto">';
            if ($row['stock'] > 0) {
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
body { font-family:'Segoe UI', sans-serif; background:#f4f6f8; padding-top:80px; }
.navbar { background-color:#0d6efd !important; }
.book-card { border:none; border-radius:15px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.1); transition:0.3s; background:white; }
.book-card:hover { transform:translateY(-5px); box-shadow:0 8px 25px rgba(0,0,0,0.15); }
.book-card img { height:260px; object-fit:cover; }
.price-tag { color:#28a745; font-weight:bold; }
.filter-box { background:white; border-radius:12px; padding:20px; box-shadow:0 4px 12px rgba(0,0,0,0.05); margin-bottom:30px; }
.no-books { text-align:center; padding:50px; color:#666; }
.chat-badge { background:red; color:white; font-size:12px; border-radius:50%; padding:2px 6px; margin-left:4px; }
@media (max-width:768px){ .book-card img{height:220px;} }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#"><i class="fas fa-book-open me-2"></i>BookStore</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav align-items-center">
        <li class="nav-item me-2"><a class="nav-link" href="cart_view.php"><i class="fas fa-shopping-cart me-1"></i>Keranjang</a></li>
        <li class="nav-item me-2"><a class="nav-link" href="orderan_saya.php"><i class="fas fa-box me-1"></i>Pesanan</a></li>
        <li class="nav-item me-2 position-relative">
          <a id="chatDropdown" class="nav-link position-relative" href="chat.php?admin_id=2">
            <i class="fas fa-comments me-1"></i>Chat
          </a>
        </li>
        <li class="nav-item"><button id="btn-logout" class="btn btn-sm btn-danger">Logout</button></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Filter Section -->
<section class="container">
  <div class="filter-box">
    <div class="row g-3">
      <div class="col-md-3">
        <input type="text" id="searchInput" class="form-control" placeholder="Cari buku...">
      </div>
      <div class="col-md-3">
        <select id="categoryFilter" class="form-select">
          <option value="">Semua Kategori</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select id="sortFilter" class="form-select">
          <option value="">Urutkan</option>
          <option value="low_high">Harga: Murah → Mahal</option>
          <option value="high_low">Harga: Mahal → Murah</option>
        </select>
      </div>
      <div class="col-md-2 d-flex">
        <input type="number" id="minPrice" class="form-control me-2" placeholder="Min">
        <input type="number" id="maxPrice" class="form-control" placeholder="Max">
      </div>
      <div class="col-md-2 d-grid">
        <button id="applyFilter" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Terapkan</button>
      </div>
    </div>
  </div>

  <div id="bookResults"></div>
</section>

<!-- 🔔 Sound file (opsional) -->
<audio id="notifSound" src="assets/notify.mp3" preload="auto"></audio>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function loadBooks(){
  const params = {
    ajax_filter: 1,
    search: $('#searchInput').val(),
    category: $('#categoryFilter').val(),
    sort: $('#sortFilter').val(),
    min_price: $('#minPrice').val(),
    max_price: $('#maxPrice').val()
  };
  $.get('', params, function(data){
    $('#bookResults').html(data);
  });
}

// 🔔 CEK PESAN BARU DARI ADMIN SETIAP 5 DETIK
setInterval(function(){
  $.get('', {check_new_chat: true}, function(data){
    let unread = parseInt(data);
    let badge = $('#chatDropdown .chat-badge');
    if (unread > 0) {
      if (badge.length === 0) {
        $('#chatDropdown').append('<span class="chat-badge">'+unread+'</span>');
      } else {
        badge.text(unread);
      }

      // 🔔 Jika ada pesan baru
      if (!window.lastUnread || unread > window.lastUnread) {
        Swal.fire({
          title: 'Pesan Baru!',
          text: 'Admin mengirim pesan baru.',
          icon: 'info',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 4000,
          timerProgressBar: true
        });
        // 🔊 Putar suara notifikasi
        document.getElementById('notifSound').play().catch(()=>{});
      }
    } else {
      badge.remove();
    }
    window.lastUnread = unread;
  });
}, 5000);

$(document).ready(function(){
  loadBooks();
  $('#applyFilter').on('click', loadBooks);
  $('#searchInput').on('input', () => setTimeout(loadBooks, 300));
});

$('#btn-logout').click(() => {
  Swal.fire({
    title: 'Yakin ingin logout?',
    text: 'Anda akan keluar dari akun ini.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, logout',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#dc3545'
  }).then(result => {
    if(result.isConfirmed) window.location.href='logout.php';
  });
});
</script>
</body>
</html>
