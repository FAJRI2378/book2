<?php
include '../includes/auth.php';
checkRole('admin');
include '../koneksi.php';

// ===============================
// 🔍 BAGIAN 1: AJAX UNTUK LIVE SEARCH + PAGINATION + FILTERING
// ===============================
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    $search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
    $category = mysqli_real_escape_string($conn, $_GET['category'] ?? '');
    $sort = mysqli_real_escape_string($conn, $_GET['sort'] ?? 'id_desc');
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 8; // Jumlah buku per halaman
    $offset = ($page - 1) * $limit;

    $where = [];
    if (!empty($search)) {
        $where[] = "(books.title LIKE '%$search%' 
                  OR books.author LIKE '%$search%' 
                  OR books.isbn LIKE '%$search%')";
    }
    
    if (!empty($category)) {
        $where[] = "books.category_id = '$category'";
    }
    
    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    
    // Sort options
    $sortOptions = [
        'id_desc' => 'books.id DESC',
        'id_asc' => 'books.id ASC',
        'title_asc' => 'books.title ASC',
        'title_desc' => 'books.title DESC',
        'price_asc' => 'books.price ASC',
        'price_desc' => 'books.price DESC',
        'stock_asc' => 'books.stock ASC',
        'stock_desc' => 'books.stock DESC'
    ];
    $orderBy = $sortOptions[$sort] ?? 'books.id DESC';

    $total = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT COUNT(*) AS total 
        FROM books 
        LEFT JOIN categories ON books.category_id = categories.id 
        $whereClause
    "))['total'];

    $query = "
        SELECT books.*, categories.name AS category
        FROM books
        LEFT JOIN categories ON books.category_id = categories.id
        $whereClause
        ORDER BY $orderBy
        LIMIT $limit OFFSET $offset
    ";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 0) {
        echo "<div class='col-12 text-center py-5'>
                <div class='empty-state'>
                    <img src='../assets/images/empty-books.svg' alt='No books found' class='mb-4' style='max-width: 200px; opacity: 0.7;'>
                    <h5 class='text-muted'>Tidak ada buku yang ditemukan</h5>
                    <p class='text-muted'>Coba ubah kata kunci pencarian atau filter</p>
                </div>
              </div>";
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
            
            // Stock status
            $stockStatus = '';
            $stockClass = '';
            if ($book['stock'] == 0) {
                $stockStatus = 'Habis';
                $stockClass = 'bg-danger';
            } elseif ($book['stock'] < 5) {
                $stockStatus = 'Terbatas';
                $stockClass = 'bg-warning';
            } else {
                $stockStatus = 'Tersedia';
                $stockClass = 'bg-success';
            }
?>
    <div class="col-md-3 col-sm-6 mb-4 book-item" data-id="<?= $book['id'] ?>">
        <div class="card shadow-sm h-100 book-card">
            <div class="position-relative">
                <img src="../uploads/<?= !empty($book['image']) ? htmlspecialchars($book['image']) : 'noimage.png' ?>" 
                     class="card-img-top book-image" 
                     alt="<?= htmlspecialchars($book['title']) ?>"
                     loading="lazy">
                <div class="position-absolute top-0 end-0 p-2">
                    <span class="badge <?= $stockClass ?>"><?= $stockStatus ?></span>
                </div>
                <?php if ($adaDalamPerjalanan): ?>
                <div class="position-absolute top-0 start-0 p-2">
                    <span class="badge bg-info">Dalam Perjalanan</span>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-body d-flex flex-column">
                <h6 class="fw-bold mb-1 text-truncate book-title"><?= htmlspecialchars($book['title']) ?></h6>
                <p class="text-muted small mb-2 book-author"><?= htmlspecialchars($book['author']) ?></p>
                <?php if (!empty($book['isbn'])): ?>
                <p class="text-muted small mb-2">ISBN: <?= htmlspecialchars($book['isbn']) ?></p>
                <?php endif; ?>
                <div class="mt-1 mb-2">
                    <span class="badge bg-secondary book-category"><?= htmlspecialchars($book['category'] ?? '-') ?></span>
                </div>
                <div class="mt-auto">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-danger fw-bold book-price">Rp <?= number_format($book['price'], 0, ',', '.') ?></span>
                        <span class="text-muted small book-stock">Stok: <?= $book['stock'] ?></span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="books/view.php?id=<?= $book['id'] ?>" class="btn btn-outline-primary btn-sm flex-fill" title="Lihat Detail">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="books/edit.php?id=<?= $book['id'] ?>" class="btn btn-warning btn-sm flex-fill" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <?php if ($adaDalamPerjalanan): ?>
                            <button class="btn btn-secondary btn-sm flex-fill" disabled title="Tidak dapat dihapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        <?php else: ?>
                            <a href="books/delete.php?id=<?= $book['id'] ?>" 
                               onclick="return confirm('Yakin hapus buku ini?')" 
                               class="btn btn-danger btn-sm flex-fill" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
        }
    }

    // Pagination links
    $totalPages = ceil($total / $limit);
    if ($totalPages > 1) {
        echo "<div class='col-12 mt-4 d-flex justify-content-between align-items-center'>";
        echo "<div class='text-muted'>Menampilkan " . (($page - 1) * $limit + 1) . " - " . min($page * $limit, $total) . " dari $total buku</div>";
        echo "<nav><ul class='pagination mb-0'>";
        
        // Previous button
        $prevDisabled = ($page == 1) ? 'disabled' : '';
        echo "<li class='page-item $prevDisabled'>
                <a class='page-link' href='#' onclick='loadBooks(" . ($page - 1) . ")' tabindex='-1'>
                    <i class='bi bi-chevron-left'></i>
                </a>
              </li>";
        
        // Page numbers
        $maxVisiblePages = 5;
        $startPage = max(1, $page - floor($maxVisiblePages / 2));
        $endPage = min($totalPages, $startPage + $maxVisiblePages - 1);
        
        if ($startPage > 1) {
            echo "<li class='page-item'>
                    <a class='page-link' href='#' onclick='loadBooks(1)'>1</a>
                  </li>";
            if ($startPage > 2) {
                echo "<li class='page-item disabled'><span class='page-link'>...</span></li>";
            }
        }
        
        for ($i = $startPage; $i <= $endPage; $i++) {
            $active = ($i == $page) ? 'active' : '';
            echo "<li class='page-item $active'>
                    <a class='page-link' href='#' onclick='loadBooks($i)'>$i</a>
                  </li>";
        }
        
        if ($endPage < $totalPages) {
            if ($endPage < $totalPages - 1) {
                echo "<li class='page-item disabled'><span class='page-link'>...</span></li>";
            }
            echo "<li class='page-item'>
                    <a class='page-link' href='#' onclick='loadBooks($totalPages)'>$totalPages</a>
                  </li>";
        }
        
        // Next button
        $nextDisabled = ($page == $totalPages) ? 'disabled' : '';
        echo "<li class='page-item $nextDisabled'>
                <a class='page-link' href='#' onclick='loadBooks(" . ($page + 1) . ")'>
                    <i class='bi bi-chevron-right'></i>
                </a>
              </li>";
        
        echo "</ul></nav></div>";
    }
    exit;
}

// ===============================
// BAGIAN 2: TAMPILAN UTAMA
// ===============================
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - BookStore</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<style>
:root {
    --primary-color: #3498db;
    --secondary-color: #2c3e50;
    --accent-color: #e74c3c;
    --light-bg: #f8f9fa;
    --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

body { 
    background-color: var(--light-bg);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.navbar-brand {
    font-weight: 700;
    font-size: 1.5rem;
}

.navbar-dark .navbar-nav .nav-link.active {
    color: #fff;
    font-weight: 600;
}

.book-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: none;
    border-radius: 10px;
    overflow: hidden;
}

.book-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
}

.book-image {
    height: 220px;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.book-card:hover .book-image {
    transform: scale(1.05);
}

.book-title {
    font-size: 0.95rem;
    line-height: 1.3;
    height: 2.6em;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.book-author {
    height: 1.2em;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.book-price {
    font-size: 1.1rem;
}

.book-stock {
    font-size: 0.85rem;
}

.book-category {
    font-size: 0.75rem;
}

.search-box {
    max-width: 350px;
}

.filter-section {
    background-color: white;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
    box-shadow: var(--card-shadow);
}

.btn-group .btn {
    border-radius: 0;
}

.btn-group .btn:first-child {
    border-top-left-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
}

.btn-group .btn:last-child {
    border-top-right-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}

.empty-state {
    padding: 40px 20px;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}

.selection-mode .book-card {
    border: 2px solid var(--primary-color);
}

.selection-mode .book-card.selected {
    background-color: rgba(52, 152, 219, 0.1);
}

.selection-mode .book-card .selection-checkbox {
    display: block;
}

.selection-checkbox {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 10;
    display: none;
}

.bulk-actions {
    display: none;
    background-color: white;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
    box-shadow: var(--card-shadow);
}

.bulk-actions.show {
    display: block;
}

@media (max-width: 768px) {
    .book-card {
        margin-bottom: 15px;
    }
    
    .filter-section {
        padding: 10px;
    }
    
    .d-flex.flex-wrap {
        flex-direction: column;
        gap: 10px;
    }
    
    .search-box {
        max-width: 100%;
    }
}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="#">
        <i class="bi bi-book-half me-2"></i>BookStore Admin
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-book me-1"></i>Daftar Buku</a></li>
        <li class="nav-item"><a class="nav-link" href="list_user.php"><i class="bi bi-people me-1"></i>List User</a></li>
        <li class="nav-item"><a class="nav-link" href="../admin/chat/index.php"><i class="bi bi-chat-dots me-1"></i>Chat</a></li>
        <li class="nav-item"><a class="nav-link" href="../admin/order_list.php"><i class="bi bi-receipt me-1"></i>Pesanan</a></li>
        <li class="nav-item"><a class="nav-link" href="../kategori/index.php"><i class="bi bi-tags me-1"></i>Kelola Kategori</a></li>
        <li class="nav-item"><a class="nav-link" href="../admin/edit_about.php"><i class="bi bi-info-circle me-1"></i>Edit About Us</a></li>
        <li class="nav-item"><a class="nav-link text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
    <h2><i class="bi bi-book-half me-2"></i>Daftar Buku</h2>
    <div class="d-flex gap-2 mt-2 mt-md-0">
        <button id="toggleSelection" class="btn btn-outline-secondary">
            <i class="bi bi-check-square me-1"></i>Pilih
        </button>
        <a href="books/create.php" class="btn btn-success">
            <i class="bi bi-plus-circle me-1"></i>Tambah Buku
        </a>
    </div>
  </div>

  <!-- Bulk Actions -->
  <div id="bulkActions" class="bulk-actions">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span id="selectedCount">0</span> buku dipilih
        </div>
        <div class="d-flex gap-2">
            <button id="bulkDelete" class="btn btn-danger btn-sm">
                <i class="bi bi-trash me-1"></i>Hapus
            </button>
            <button id="bulkExport" class="btn btn-primary btn-sm">
                <i class="bi bi-download me-1"></i>Export
            </button>
            <button id="cancelSelection" class="btn btn-secondary btn-sm">
                Batal
            </button>
        </div>
    </div>
  </div>

  <!-- Filter Section -->
  <div class="filter-section">
    <div class="row g-3">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" id="searchInput" class="form-control" 
                       placeholder="Cari judul, penulis, ISBN...">
            </div>
        </div>
        <div class="col-md-3">
            <select id="categoryFilter" class="form-select">
                <option value="">Semua Kategori</option>
                <?php
                $categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
                while ($cat = mysqli_fetch_assoc($categories)) {
                    echo "<option value='{$cat['id']}'>{$cat['name']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-3">
            <select id="sortFilter" class="form-select">
                <option value="id_desc">Terbaru</option>
                <option value="id_asc">Terlama</option>
                <option value="title_asc">Judul (A-Z)</option>
                <option value="title_desc">Judul (Z-A)</option>
                <option value="price_asc">Harga (Rendah ke Tinggi)</option>
                <option value="price_desc">Harga (Tinggi ke Rendah)</option>
                <option value="stock_asc">Stok (Rendah ke Tinggi)</option>
                <option value="stock_desc">Stok (Tinggi ke Rendah)</option>
            </select>
        </div>
        <div class="col-md-2">
            <button id="resetFilters" class="btn btn-outline-secondary w-100">
                <i class="bi bi-arrow-clockwise me-1"></i>Reset
            </button>
        </div>
    </div>
  </div>

  <!-- Loading Overlay -->
  <div id="loadingOverlay" class="loading-overlay d-none">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
  </div>

  <!-- Books Container -->
  <div id="bookContainer" class="row"></div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let timeout = null;
let currentPage = 1;
let selectionMode = false;
let selectedBooks = new Set();

// Fungsi load buku
function loadBooks(page = 1) {
  const keyword = document.getElementById('searchInput').value.trim();
  const category = document.getElementById('categoryFilter').value;
  const sort = document.getElementById('sortFilter').value;
  
  showLoading();
  
  fetch(`books.php?ajax=1&page=${page}&search=${encodeURIComponent(keyword)}&category=${category}&sort=${sort}`)
    .then(res => res.text())
    .then(html => {
      document.getElementById('bookContainer').innerHTML = html;
      currentPage = page;
      hideLoading();
      
      // Re-attach event listeners for selection mode
      if (selectionMode) {
        attachSelectionListeners();
      }
    })
    .catch(() => {
      document.getElementById('bookContainer').innerHTML =
        "<div class='col-12 text-center text-danger py-5'>Gagal memuat data. Silakan coba lagi.</div>";
      hideLoading();
    });
}

// Show/hide loading overlay
function showLoading() {
  document.getElementById('loadingOverlay').classList.remove('d-none');
}

function hideLoading() {
  document.getElementById('loadingOverlay').classList.add('d-none');
}

// Toggle selection mode
document.getElementById('toggleSelection').addEventListener('click', function() {
  selectionMode = !selectionMode;
  document.body.classList.toggle('selection-mode');
  document.getElementById('bulkActions').classList.toggle('show', selectionMode);
  
  if (selectionMode) {
    this.innerHTML = '<i class="bi bi-x-square me-1"></i>Batal Pilih';
    attachSelectionListeners();
  } else {
    this.innerHTML = '<i class="bi bi-check-square me-1"></i>Pilih';
    selectedBooks.clear();
    updateSelectedCount();
  }
});

// Cancel selection
document.getElementById('cancelSelection').addEventListener('click', function() {
  selectionMode = false;
  document.body.classList.remove('selection-mode');
  document.getElementById('bulkActions').classList.remove('show');
  document.getElementById('toggleSelection').innerHTML = '<i class="bi bi-check-square me-1"></i>Pilih';
  selectedBooks.clear();
  updateSelectedCount();
  loadBooks(currentPage);
});

// Attach selection listeners to book cards
function attachSelectionListeners() {
  document.querySelectorAll('.book-item').forEach(item => {
    const bookId = item.dataset.id;
    const card = item.querySelector('.book-card');
    
    // Add checkbox if not exists
    if (!item.querySelector('.selection-checkbox')) {
      const checkbox = document.createElement('input');
      checkbox.type = 'checkbox';
      checkbox.className = 'form-check-input selection-checkbox';
      checkbox.checked = selectedBooks.has(bookId);
      
      item.querySelector('.position-relative').appendChild(checkbox);
      
      checkbox.addEventListener('change', function() {
        if (this.checked) {
          selectedBooks.add(bookId);
          card.classList.add('selected');
        } else {
          selectedBooks.delete(bookId);
          card.classList.remove('selected');
        }
        updateSelectedCount();
      });
      
      // Toggle selection on card click
      card.addEventListener('click', function(e) {
        if (e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON' && e.target.tagName !== 'I') {
          checkbox.checked = !checkbox.checked;
          checkbox.dispatchEvent(new Event('change'));
        }
      });
      
      // Update card selected state
      card.classList.toggle('selected', selectedBooks.has(bookId));
    }
  });
}

// Update selected count
function updateSelectedCount() {
  document.getElementById('selectedCount').textContent = selectedBooks.size;
}

// Bulk delete
document.getElementById('bulkDelete').addEventListener('click', function() {
  if (selectedBooks.size === 0) return;
  
  if (confirm(`Yakin ingin menghapus ${selectedBooks.size} buku yang dipilih?`)) {
    // In a real implementation, you would send an AJAX request to delete the books
    // For now, we'll just show a message
    alert(`Fitur hapus massal untuk ${selectedBooks.size} buku akan segera tersedia.`);
    // After successful deletion:
    // selectionMode = false;
    // document.body.classList.remove('selection-mode');
    // document.getElementById('bulkActions').classList.remove('show');
    // document.getElementById('toggleSelection').innerHTML = '<i class="bi bi-check-square me-1"></i>Pilih';
    // selectedBooks.clear();
    // updateSelectedCount();
    // loadBooks(currentPage);
  }
});

// Bulk export
document.getElementById('bulkExport').addEventListener('click', function() {
  if (selectedBooks.size === 0) return;
  
  // In a real implementation, you would send an AJAX request to export the books
  // For now, we'll just show a message
  alert(`Fitur export massal untuk ${selectedBooks.size} buku akan segera tersedia.`);
});

// Event listeners for filters
document.getElementById('searchInput').addEventListener('keyup', () => {
  clearTimeout(timeout);
  timeout = setTimeout(() => loadBooks(1), 300);
});

document.getElementById('categoryFilter').addEventListener('change', () => {
  loadBooks(1);
});

document.getElementById('sortFilter').addEventListener('change', () => {
  loadBooks(1);
});

document.getElementById('resetFilters').addEventListener('click', () => {
  document.getElementById('searchInput').value = '';
  document.getElementById('categoryFilter').value = '';
  document.getElementById('sortFilter').value = 'id_desc';
  loadBooks(1);
});

// Load pertama kali
loadBooks();
</script>
</body>
</html>