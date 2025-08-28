<?php
session_start();
include '../../koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
  header("Location: ../../login.php");
  exit;
}

$current_user_id = $_SESSION['user_id'];

// Ambil data user yang sedang login
$query = mysqli_query($conn, "SELECT * FROM users WHERE id = $current_user_id");
$me = mysqli_fetch_assoc($query);

// Cek role (admin atau user)
$is_admin = $me['role'] === 'admin';

// Ambil daftar pengguna yang bisa diajak chat
if ($is_admin) {
  // Admin bisa chat semua user selain dirinya sendiri
  $users = mysqli_query($conn, "SELECT * FROM users WHERE id != $current_user_id");
} else {
  // User hanya bisa chat admin
  $users = mysqli_query($conn, "SELECT * FROM users WHERE role = 'admin' LIMIT 1");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Chat - <?= htmlspecialchars($me['username']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h3>💬 Chat dengan <?= $is_admin ? 'Pengguna' : 'Admin' ?></h3>
  <a href="../books.php" class="btn btn-primary" 
   style="background:#007bff; padding:8px 14px; border-radius:4px; text-decoration:none; color:white;">
   ← Kembali ke Daftar Buku
</a>

  <ul class="list-group">
    <?php while ($u = mysqli_fetch_assoc($users)): ?>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <?= htmlspecialchars($u['username']) ?>
        <a href="chat_room.php?user_id=<?= $u['id'] ?>" class="btn btn-sm btn-primary">Buka Chat</a>
      </li>
    <?php endwhile; ?>
  </ul>

</div>
</body>
</html>
