<?php
session_start();
include 'koneksi.php';

if (!isset($_GET['id'])) {
  die("ID buku tidak ditemukan!");
}

$book_id = (int) $_GET['id'];

// 🔐 CSRF token untuk keamanan form
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Ambil data buku
$book = $conn->query("SELECT * FROM books WHERE id = $book_id")->fetch_assoc();
if (!$book) die("Buku tidak ditemukan!");

// Ambil semua ulasan
$reviews = $conn->query("SELECT r.*, u.username 
                         FROM reviews r 
                         JOIN users u ON r.user_id = u.id 
                         WHERE book_id = $book_id 
                         ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($book['title']) ?> - Detail Buku</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      transition: background-color 0.3s ease, color 0.3s ease;
    }
    .card {
      border: none;
      border-radius: 12px;
    }
    .review-card {
      transition: transform 0.2s ease;
    }
    .review-card:hover {
      transform: scale(1.01);
    }
    .dark-mode-toggle {
      position: fixed;
      bottom: 20px;
      right: 20px;
      border-radius: 50%;
      padding: 10px 13px;
      font-size: 18px;
    }
  </style>
</head>
<body class="bg-light text-dark">

<div class="container my-5">
  <!-- DETAIL BUKU -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h2 class="card-title mb-3 text-primary"><?= htmlspecialchars($book['title']) ?></h2>
      <p><strong>Penulis:</strong> <?= htmlspecialchars($book['author']) ?></p>
      <p><strong>Deskripsi:</strong><br> <?= nl2br(htmlspecialchars($book['description'])) ?></p>
      <a href="index.php" class="btn btn-secondary mt-3">⬅ Kembali ke Beranda</a>
    </div>
  </div>

  <!-- ULASAN -->
  <div class="mb-4">
    <h4 class="mb-3">💬 Ulasan Pengguna</h4>
    <?php if ($reviews->num_rows > 0): ?>
      <?php while ($r = $reviews->fetch_assoc()): ?>
        <div class="review-card border rounded p-3 mb-3 bg-white shadow-sm">
          <strong class="text-primary"><?= htmlspecialchars($r['username']) ?></strong><br>
          <span class="text-warning"><?= str_repeat('⭐', $r['rating']) ?></span>
          <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
          <small class="text-muted"><?= $r['created_at'] ?></small>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="text-muted fst-italic">Belum ada ulasan untuk buku ini.</p>
    <?php endif; ?>
  </div>

  <!-- FORM ULASAN -->
  <?php if (isset($_SESSION['user_id'])): ?>
  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="mb-3">📝 Tulis Ulasanmu</h5>
      <form method="POST" action="submit_review.php">
        <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="mb-3">
          <label class="form-label">Rating:</label>
          <select name="rating" class="form-select" required>
            <option value="5">⭐⭐⭐⭐⭐ (5)</option>
            <option value="4">⭐⭐⭐⭐ (4)</option>
            <option value="3">⭐⭐⭐ (3)</option>
            <option value="2">⭐⭐ (2)</option>
            <option value="1">⭐ (1)</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Review:</label>
          <textarea name="comment" class="form-control" placeholder="Tulis ulasanmu di sini..." rows="3" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Kirim Ulasan</button>
      </form>
    </div>
  </div>
  <?php else: ?>
    <div class="alert alert-info mt-4">
      Silakan <a href="login.php" class="alert-link">login</a> untuk menulis ulasan.
    </div>
  <?php endif; ?>
</div>

<!-- Tombol Mode Gelap -->
<button class="btn btn-dark dark-mode-toggle" id="toggleTheme">🌙</button>

<script>
  const toggleBtn = document.getElementById('toggleTheme');
  const html = document.documentElement;
  let isDark = false;

  toggleBtn.addEventListener('click', () => {
    isDark = !isDark;
    html.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
    toggleBtn.textContent = isDark ? '☀️' : '🌙';
  });
</script>

</body>
</html>
