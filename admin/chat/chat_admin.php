<?php
session_start();
include '../../koneksi.php';

// pastikan admin login
$current_user_id = $_SESSION['user_id'] ?? null;
$target_user_id = $_GET['user_id'] ?? null;

if (!$current_user_id || !$target_user_id) {
    die("Chat tidak valid.");
}

// Ambil username target
$get_target_user = mysqli_query($conn, "SELECT username FROM users WHERE id = $target_user_id");
$target_user_data = mysqli_fetch_assoc($get_target_user);
$target_user_username = $target_user_data['username'] ?? 'Pengguna';

// Ambil chat
$chat = mysqli_query($conn, "
    SELECT * FROM chats
    WHERE (sender_id = $current_user_id AND receiver_id = $target_user_id)
       OR (sender_id = $target_user_id AND receiver_id = $current_user_id)
    ORDER BY created_at ASC
");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Chat Room</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .chat-box { height:400px; overflow-y:scroll; border:1px solid #ccc; padding:15px; margin-bottom:20px; background:#f9f9f9; border-radius:8px;}
    .bubble { padding:10px 15px; border-radius:15px; margin-bottom:10px; max-width:70%; display:inline-block; clear:both;}
    .from-me { background:#dcfce7; float:right; text-align:right;}
    .from-them { background:#e0f2fe; float:left; text-align:left;}
  </style>
</head>
<body class="container py-4">
<h4>Chat antara Anda dengan <strong><?= htmlspecialchars($target_user_username) ?></strong></h4>

<div class="chat-box">
  <?php while ($row = mysqli_fetch_assoc($chat)): ?>
    <div class="bubble <?= $row['sender_id'] == $current_user_id ? 'from-me' : 'from-them' ?>">
      <?= htmlspecialchars($row['message']) ?><br>
      <small class="text-muted"><?= $row['created_at'] ?></small>
    </div>
  <?php endwhile ?>
</div>

<form method="post" action="send.php">
  <div class="input-group">
    <input type="hidden" name="sender_id" value="<?= $current_user_id ?>">
    <input type="hidden" name="receiver_id" value="<?= $target_user_id ?>">
    <input type="text" name="message" class="form-control" placeholder="Tulis pesan..." required>
    <button type="submit" class="btn btn-success">Kirim</button>
  </div>
</form>
<a href="index.php" class="btn btn-secondary mt-3">← Kembali</a>
</body>
</html>
