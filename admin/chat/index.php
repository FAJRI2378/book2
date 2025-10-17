<?php
session_start();
include '../../koneksi.php';

// Cek login admin
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

// Ambil semua user (default)
$users = $conn->query("SELECT id, username FROM users WHERE role='user' ORDER BY username ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar Chat User</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; padding: 20px; }
    .user-list { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    .search-box { margin-bottom: 20px; }
    .user-item { padding: 10px 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
    .user-item:last-child { border-bottom: none; }
    .username { font-weight: 500; }
    .badge { font-size: 0.8rem; }
  </style>
</head>
<body class="container">
  <h4 class="mb-4">📨 Daftar Chat User</h4>

  <div class="user-list">
    <!-- Search -->
    <div class="input-group search-box">
      <input type="text" id="searchUser" class="form-control" placeholder="Cari nama user...">
      <button class="btn btn-outline-primary" type="button">🔍</button>
    </div>

    <!-- Daftar user -->
    <div id="userContainer">
      <?php while ($u = $users->fetch_assoc()): ?>
        <?php
        //   // Hitung pesan belum dibaca dari user ini
        //   $unread = $conn->query("
        //       SELECT COUNT(*) AS total 
        //       FROM chats 
        //       WHERE sender_id = {$u['id']} 
        //         AND receiver_id = $admin_id 
        //         AND is_read = 0
        //   ")->fetch_assoc()['total'];
        // ?>
        <div class="user-item">
          <span class="username"><?= htmlspecialchars($u['username']) ?></span>
          <div>
            
            <a href="chat_room.php?user_id=<?= $u['id'] ?>" class="btn btn-sm btn-primary ms-2">Chat</a>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
  <a href="../books.php" class="btn btn-secondary mt-3">← Kembali</a>

  <script>
    const searchInput = document.getElementById('searchUser');
    const userContainer = document.getElementById('userContainer');

    // AJAX search saat mengetik
    searchInput.addEventListener('keyup', function() {
      const keyword = this.value.trim();
      fetch('search_user.php?q=' + encodeURIComponent(keyword))
        .then(res => res.text())
        .then(html => {
          userContainer.innerHTML = html;
        });
    });
  </script>
</body>
</html>
