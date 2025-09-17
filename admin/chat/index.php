<?php
include '../../koneksi.php';

// Ambil daftar semua user
$users = mysqli_query($conn, "SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Chat</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h3>💬 Pilih User untuk Chat</h3>
  <a href="../books.php" class="btn btn-primary mb-3" 
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
