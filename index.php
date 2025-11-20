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
// 🔍 AJAX FILTER & SEARCH
// ==============================
// ==============================
// 🔍 AJAX FILTER & SEARCH + PAGINATION
// ==============================
if (isset($_GET['ajax_filter'])) {
    $search = trim($_GET['search'] ?? '');
    $category = $_GET['category'] ?? '';
    $sort = $_GET['sort'] ?? '';
    $min_price = $_GET['min_price'] ?? '';
    $max_price = $_GET['max_price'] ?? '';
    $rating = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = 4; // jumlah buku per halaman
    $offset = ($page - 1) * $limit;

    // --- hitung total buku ---
    $countQuery = "
        SELECT COUNT(DISTINCT books.id) AS total
        FROM books
        JOIN categories ON books.category_id = categories.id
        LEFT JOIN reviews rv ON books.id = rv.book_id
        WHERE 1=1
    ";

    if ($search) {
        $searchEsc = $conn->real_escape_string($search);
        $countQuery .= " AND (books.title LIKE '%$searchEsc%' OR books.author LIKE '%$searchEsc%')";
    }
    if ($category !== '') $countQuery .= " AND categories.id = " . intval($category);
    if ($min_price !== '' && $max_price !== '') $countQuery .= " AND books.price BETWEEN " . intval($min_price) . " AND " . intval($max_price);

    $countResult = $conn->query($countQuery);
    $totalBooks = ($countResult && $countResult->num_rows > 0) ? $countResult->fetch_assoc()['total'] : 0;
    $totalPages = ceil($totalBooks / $limit);

    // --- query data buku utama ---
    $query = "
        SELECT books.*, categories.name AS category,
               IFNULL(AVG(rv.rating), 0) AS avg_rating,
               COUNT(rv.id) AS total_review
        FROM books
        JOIN categories ON books.category_id = categories.id
        LEFT JOIN reviews rv ON books.id = rv.book_id
        WHERE 1=1
    ";

    if ($search) {
        $searchEsc = $conn->real_escape_string($search);
        $query .= " AND (books.title LIKE '%$searchEsc%' OR books.author LIKE '%$searchEsc%')";
    }
    if ($category !== '') $query .= " AND categories.id = " . intval($category);
    if ($min_price !== '' && $max_price !== '') $query .= " AND books.price BETWEEN " . intval($min_price) . " AND " . intval($max_price);

    $query .= " GROUP BY books.id ";
    if ($rating > 0) $query .= " HAVING avg_rating >= $rating ";

    switch ($sort) {
        case 'low_high': $query .= " ORDER BY books.price ASC"; break;
        case 'high_low': $query .= " ORDER BY books.price DESC"; break;
        case 'newest': $query .= " ORDER BY books.id DESC"; break;
        case 'bestseller': $query .= " ORDER BY books.sold_count DESC"; break;
        default: $query .= " ORDER BY books.title ASC";
    }

    $query .= " LIMIT $limit OFFSET $offset"; // 🔹 ION LIMIT

    $result = $conn->query($query);


    if ($result->num_rows == 0) {
        echo '<div class="no-books" data-aos="fade-up">
                <i class="fas fa-search fa-shake"></i>
                <h4>Tidak ada buku yang ditemukan</h4>
                <p>Coba ubah filter atau kata kunci pencarian.</p>
              </div>';
    } else {
        echo '<div class="row g-4">';
        while ($row = $result->fetch_assoc()) {
            $rating = round($row['avg_rating'], 1);
            $stars = str_repeat('⭐', floor($rating));
            if ($rating > floor($rating)) $stars .= '✩';

            echo '
            <div class="col-sm-6 col-md-4 col-lg-3" data-aos="zoom-in" data-aos-duration="800">
              <div class="card book-card h-100">
                <img src="'.(!empty($row['image']) ? 'uploads/'.htmlspecialchars($row['image']) : 'assets/no-image.png').'" class="card-img-top" alt="'.htmlspecialchars($row['title']).'">
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title">'.htmlspecialchars($row['title']).'</h5>
                  <p class="card-text"><i class="fas fa-user me-1"></i>'.htmlspecialchars($row['author']).'</p>
                  <p class="card-text"><i class="fas fa-tag me-1"></i>'.htmlspecialchars($row['category']).'</p>
                  <p class="card-text"><i class="fas fa-boxes me-1"></i>Stok: '.intval($row['stock']).'</p>
                  <p class="card-text mb-2"><strong>Rp '.number_format($row['price'], 0, ',', '.').'</strong></p>
                  <p class="text-warning mb-2 d-none">'.$stars.'</p>
                   <small class="text-muted d-none">('.number_format($rating,1).' dari '.$row['total_review'].' ulasan)</small>
                </div>
                <div class="mt-auto">
                  <form method="POST" action="wishlist_action.php" class="mt-1 d-none">
                    <input type="hidden" name="book_id" value="'.$row['id'].'">
                    <button type="submit" class="btn btn-outline-warning w-100 mb-2">
                    <i class="fas fa-heart"></i> Tambah ke Wishlist
                    </button>
                  </form>  
            ';


                   $currentInCart = $_SESSION['cart'][$row['id']] ?? 0;

                if ($row['stock'] > 0) {
                    echo '
                    <form method="POST" action="cart.php" class="add-to-cart-form">
                        <input type="hidden" name="book_id" value="'.$row['id'].'">
                        <button 
                            type="submit" 
                            class="btn btn-primary w-100 add-cart-btn"
                            data-stock="'.$row['stock'].'"
                            data-current="'.$currentInCart.'"
                            onclick="return cekKeranjang(this)"
                        >
                            <i class="fas fa-cart-plus me-1"></i> Tambah ke Keranjang
                        </button>
                    </form>';
                } else {
                    echo '
                    <button class="btn btn-secondary w-100" disabled>
                        Stok Habis
                    </button>';
                }

        echo '
            </div>
          </div>
        </div>
        ';
        

        }
        echo '</div>';

        // 🔹 Tampilkan navigasi pagination
        if ($totalPages > 1) {
            echo '<nav aria-label="Page navigation"><ul class="pagination justify-content-center mt-4">';
            for ($i = 1; $i <= $totalPages; $i++) {
                $active = ($i == $page) ? 'active' : '';
                echo '<li class="page-item '.$active.'">
                        <a class="page-link page-btn" href="#" data-page="'.$i.'">'.$i.'</a>
                      </li>';
            }
            echo '</ul></nav>';
        }


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
<link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
:root {
  --bg-color: #f4f6f8;
  --text-color: #212529;
  --card-bg: #fff;
  --navbar-bg: #0d6efd;
}

body.dark-mode {
  --bg-color: #121212;
  --text-color: #f1f1f1;
  --card-bg: #1e1e1e;
  --navbar-bg: #1f1f1f;
}

body {
  font-family: 'Segoe UI', sans-serif;
  background: var(--bg-color);
  color: var(--text-color);
  padding-top: 80px;
  transition: 0.4s background, 0.4s color;
}
.pagination .page-item.active .page-link {
  background-color: #0d6efd;
  border-color: #0d6efd;
  color: white;
}

.pagination .page-link {
  color: #0d6efd;
  border-radius: 6px;
  margin: 0 2px;
}

.pagination .page-link:hover {
  background-color: #e8f0ff;
}
.pagination {
  z-index: 99;
  position: relative;
  margin-bottom: 60px;
}

.pagination .page-item.active .page-link {
  background-color: #0d6efd !important;
  color: #fff !important;
}

.pagination .page-link {
  background-color: #fff;
  border: 1px solid #dee2e6;
}


.navbar { background-color: var(--navbar-bg) !important; transition: 0.3s; }
.book-card { border:none; border-radius:15px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.1); transition:0.3s; background: var(--card-bg); }
.book-card:hover { transform:translateY(-5px); box-shadow:0 8px 25px rgba(0,0,0,0.15); }
.book-card img { height:260px; object-fit:cover; transition:0.3s; }
.book-card:hover img { transform: scale(1.05); }
.price-tag { color:#28a745; font-weight:bold; }
.filter-box { background: var(--card-bg); border-radius:12px; padding:20px; box-shadow:0 4px 12px rgba(0,0,0,0.05); margin-bottom:30px; transition: 0.3s; }
.no-books { text-align:center; padding:50px; color:#666; }
.chat-badge { background:red; color:white; font-size:12px; border-radius:50%; padding:2px 6px; margin-left:4px; }
.dark-mode .no-books { color:#bbb; }
@media (max-width:768px){ .book-card img{height:220px;} }
</style>
</head>
<body>

<!-- 🔵 Navbar -->
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
        <li class="nav-item me-2"><a class="nav-link" href="about.php"><i class="fas fa-circle-info me-1"></i>About</a></li>
        <!-- <li class="nav-item me-2"><a class="nav-link" href="wishlist.php"><i class="fas fa-heart me-1"></i>Wishlist</a></li> -->
        <li class="nav-item me-2 position-relative">
          <a id="chatDropdown" class="nav-link position-relative" href="chat.php?admin_id=2">
            <i class="fas fa-comments me-1"></i>Chat
          </a>
        </li>
        <li class="nav-item me-3">
        <!-- <select id="languageSelect" class="form-select form-select-sm">
          <option value="id">🇮🇩 Indonesia</option>
          <option value="en">🇬🇧 English</option>
        </select>
      </li> -->
        <li class="nav-item me-3 d-none">
          <button id="toggleTheme" class="btn btn-outline-light btn-sm"><i class="fas fa-moon"></i></button>
        </li>
        <li class="nav-item"><button id="btn-logout" class="btn btn-sm btn-danger">Logout</button></li>
      </ul>
    </div>
  </div>
</nav>

<!-- 🧮 Filter Section -->
<section class="container">
  <div class="filter-box" data-aos="fade-down">
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
      <option value="newest">Terbaru</option>
      <option value="bestseller">Terlaris</option>
      <option value="low_high">Termurah</option>
      <option value="high_low">Termahal</option>
    </select>
      </div>
      <div class="col-md-2 d-flex">
        <input type="number" id="minPrice" class="form-control me-2" placeholder="Min">
        <input type="number" id="maxPrice" class="form-control" placeholder="Max">
      </div>
      <div class="col-md-2">
      <!-- <select id="ratingFilter" class="form-select">
        <option value="">Semua Rating</option>
        <option value="5">⭐⭐⭐⭐⭐ (5)</option>
        <option value="4">⭐⭐⭐⭐ ke atas</option>
        <option value="3">⭐⭐⭐ ke atas</option>
        <option value="2">⭐⭐ ke atas</option>
        <option value="1">⭐ ke atas</option>
      </select> -->
    </div>

      <div class="col-md-2 d-grid">
        <button id="applyFilter" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Terapkan</button>
      </div>
        <!-- <div class="col-md-2 d-flex justify-content-center align-items-center">
          <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-filter me-1"></i> Terapkan
          </button>
        </div> -->
    </div>
  </div>

  <div id="bookResults"></div>
</section>

<audio id="notifSound" src="assets/notify.mp3" preload="auto"></audio>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script>
AOS.init({ duration: 800, once: true });
function loadBooks(page = 1){
  const params = {
    ajax_filter: 1,
    search: $('#searchInput').val(),
    category: $('#categoryFilter').val(),
    sort: $('#sortFilter').val(),
    min_price: $('#minPrice').val(),
    max_price: $('#maxPrice').val(),
    rating: $('#ratingFilter').val(),
    page: page
  };

  $.get('', params, function(data){
    $('#bookResults').html(data);
    AOS.refresh();

    // 🔹 Aktifkan event pagination setelah data dimuat ulang
    $('#bookResults').off('click', '.page-btn').on('click', '.page-btn', function(e){
      e.preventDefault();
      let selectedPage = $(this).data('page');
      loadBooks(selectedPage);

      // Scroll ke atas agar user lihat hasilnya
      $('html, body').animate({scrollTop: $('#bookResults').offset().top - 80}, 500);
    });
  });
}



// 🔔 Cek pesan baru dari admin tiap 5 detik
// setInterval(function(){
//   $.get('', {check_new_chat: true}, function(data){
//     let unread = parseInt(data);
//     let badge = $('#chatDropdown .chat-badge');
//     if (unread > 0) {
//       if (badge.length === 0) {
//         $('#chatDropdown').append('<span class="chat-badge">'+unread+'</span>');
//       } else {
//         badge.text(unread);
//       }
//       if (!window.lastUnread || unread > window.lastUnread) {
//         Swal.fire({
//           title: 'Pesan Baru!',
//           text: 'Admin mengirim pesan baru.',
//           icon: 'info',
//           toast: true,
//           position: 'top-end',
//           showConfirmButton: false,
//           timer: 4000,
//           timerProgressBar: true
//         });
//         document.getElementById('notifSound').play().catch(()=>{});
//       }
//     } else {
//       badge.remove();
//     }
//     window.lastUnread = unread;
//   });
// }, 5000);

$(document).ready(function(){
  loadBooks();
  $('#applyFilter').on('click', loadBooks);
  $('#searchInput').on('input', () => setTimeout(loadBooks, 300));

  // 🌙 DARK MODE
  const themeBtn = $('#toggleTheme');
  const currentTheme = localStorage.getItem('theme');
  if (currentTheme === 'dark') {
    $('body').addClass('dark-mode');
    themeBtn.html('<i class="fas fa-sun"></i>');
  }

  themeBtn.on('click', function(){
    $('body').toggleClass('dark-mode');
    const isDark = $('body').hasClass('dark-mode');
    themeBtn.html(isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
  });
});

// 🌍 Translator Otomatis (Indo ↔ English)
const translations = {
  id: {
    "Cari buku...": "Cari buku...",
    "Semua Kategori": "Semua Kategori",
    "Urutkan": "Urutkan",
    "Harga: Murah → Mahal": "Harga: Murah → Mahal",
    "Harga: Mahal → Murah": "Harga: Mahal → Murah",
    "Terapkan": "Terapkan",
    "Keranjang": "Keranjang",
    "Pesanan": "Pesanan",
    "Tentang": "Tentang",
    "Wishlist": "Wishlist",
    "Chat": "Chat",
    "Logout": "Logout",
    "Semua Rating": "Semua Rating"
  },
  en: {
    "Cari buku...": "Search books...",
    "Semua Kategori": "All Categories",
    "Urutkan": "Sort",
    "Harga: Murah → Mahal": "Price: Low → High",
    "Harga: Mahal → Murah": "Price: High → Low",
    "Terapkan": "Apply",
    "Keranjang": "Cart",
    "Pesanan": "Orders",
    "Tentang": "About",
    "Wishlist": "Wishlist",
    "Chat": "Chat",
    "Logout": "Logout",
    "Semua Rating": "All Ratings"
  }
};

$('#languageSelect').on('change', function() {
  const lang = $(this).val();
  localStorage.setItem('language', lang);
  translatePage(lang);
});

function translatePage(lang) { 
  $('[placeholder], option, button, a, h4, h5, p, span').each(function() {
    let text = $(this).text().trim();
    let placeholder = $(this).attr('placeholder');
    if (translations[lang][text]) $(this).text(translations[lang][text]);
    if (placeholder && translations[lang][placeholder]) {
      $(this).attr('placeholder', translations[lang][placeholder]);
    }
  });
}

$(document).ready(function() {
  const savedLang = localStorage.getItem('language') || 'id';
  $('#languageSelect').val(savedLang);
  translatePage(savedLang);
});
document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".add-cart-btn");

    buttons.forEach(btn => {
        btn.addEventListener("click", function (e) {

            const stock = parseInt(btn.dataset.stock);

            // Jika stok habis
            if (stock <= 0) {
                e.preventDefault();
                alert("Stok habis! Tidak dapat menambahkan ke keranjang.");
                return;
            }

         
        });
    });
});
function cekKeranjang(button) {
    let stock = parseInt(button.dataset.stock);
    let current = parseInt(button.dataset.current);

    if (current + 1 > stock) {
        alert("Jangann membeli barang ini lagii,stock sudah habiss!");
        return false; // cegah submit
    }

    return true; // lanjutkan submit form
}

// 🚪 Logout
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
