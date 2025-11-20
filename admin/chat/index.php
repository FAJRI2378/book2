<?php
session_start();
include '../../koneksi.php';

// ==============================
// 🔒 CEK LOGIN ADMIN
// ==============================
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

// ==============================
// 🧩 CEK CHAT BARU (UNTUK AJAX POLLING)
// ==============================
// if (isset($_GET['check_new_chat'])) {
//     $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM chats WHERE receiver_id = ? AND is_read = 0");
//     $stmt->bind_param("i", $admin_id);
//     $stmt->execute();
//     $stmt->bind_result($unread);
//     $stmt->fetch();
//     $stmt->close();
//     echo $unread;
//     exit;
// }

// ==============================
// 👤 AMBIL SEMUA USER
// ==============================
$users = $conn->query("SELECT id, username FROM users WHERE role='user' ORDER BY username ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar Chat User</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
  <h4 class="mb-4">
    📨 Daftar Chat User 
    <span id="notifBadge" class="badge bg-danger ms-2" style="display:none;">0</span>
  </h4>

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
          // Hitung pesan belum dibaca dari user ini
          $unread = $conn->query("
              SELECT COUNT(*) AS total 
              FROM chats 
              WHERE sender_id = {$u['id']} 
                AND receiver_id = $admin_id 
                AND is_read = 0
          ")->fetch_assoc()['total'];
        ?>
        <div class="user-item">
          <span class="username"><?= htmlspecialchars($u['username']) ?></span>
          <div>
            <?php if ($unread > 0): ?>
              <span class="badge bg-danger"><?= $unread ?></span>
            <?php endif; ?>
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
    // const notifBadge = document.getElementById('notifBadge');
    let lastUnreadCount = 0;

    // ================================
    // 🔍 AJAX Search
    // ================================
    searchInput.addEventListener('keyup', function() {
      const keyword = this.value.trim();
      fetch('search_user.php?q=' + encodeURIComponent(keyword))
        .then(res => res.text())
        .then(html => {
          userContainer.innerHTML = html;
        });
    });

    // ================================
    // 🔔 CEK CHAT BARU (POLLING)
    // ================================
    // setInterval(() => {
    //   $.get('', { check_new_chat: true }, function(data) {
    //     const unread = parseInt(data);
    //     if (unread > 0) {
    //       notifBadge.style.display = 'inline-block';
    //       notifBadge.textContent = unread;

    //       // Kalau ada pesan baru dibanding sebelumnya, munculkan notifikasi
    //       if (unread > lastUnreadCount) {
    //         Swal.fire({
    //           title: 'Pesan Baru!',
    //           text: 'Ada pesan baru dari user.',
    //           icon: 'info',
    //           toast: true,
    //           position: 'top-end',
    //           showConfirmButton: false,
    //           timer: 4000,
    //           timerProgressBar: true
    //         });

    //         // 🔊 Mainkan suara notifikasi
    //         new Audio('../../assets/notify.mp3').play();
    //       }
    //     } else {
    //       notifBadge.style.display = 'none';
    //     }

    //     lastUnreadCount = unread;
    //   });
    // }, 5000); // cek tiap 5 detik
  </script>
</body>
</html>
