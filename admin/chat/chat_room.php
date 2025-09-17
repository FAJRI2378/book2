<?php
include '../../koneksi.php';

$admin_id = 2; // admin fix
$target_user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;

if ($target_user_id == 0) {
    die("User ID tidak valid!");
}

// Proses kirim pesan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    mysqli_query($conn, "INSERT INTO chats (sender_id, receiver_id, message, created_at) 
                         VALUES ($admin_id, $target_user_id, '$message', NOW())");
    header("Location: chat_room.php?user_id=$target_user_id");
    exit;
}

// Ambil pesan
$chat = mysqli_query($conn, "
    SELECT * FROM chats 
    WHERE (sender_id = $admin_id AND receiver_id = $target_user_id)
       OR (sender_id = $target_user_id AND receiver_id = $admin_id)
    ORDER BY created_at ASC
");

// Ambil username target
$get_target_user = mysqli_query($conn, "SELECT username FROM users WHERE id = $target_user_id");
$target_user = mysqli_fetch_assoc($get_target_user);
$target_user_username = $target_user['username'] ?? 'Pengguna';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Chat Admin dengan <?= htmlspecialchars($target_user_username) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .chat-box {height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; background: #f9f9f9; border-radius: 8px;}
    .bubble {padding: 10px 15px; border-radius: 15px; max-width: 70%; margin-bottom: 10px;}
    .from-me {background: #dcfce7; margin-left: auto; text-align: right;}
    .from-them {background: #e0f2fe; margin-right: auto; text-align: left;}
    .message-row {display: flex;}
  </style>
</head>
<body class="container py-4">
<h4>💬 Chat dengan <strong><?= htmlspecialchars($target_user_username) ?></strong></h4>

<div class="chat-box">
  <?php while ($row = mysqli_fetch_assoc($chat)): ?>
    <div class="message-row">
      <div class="bubble <?= $row['sender_id'] == $admin_id ? 'from-me' : 'from-them' ?>">
        <?= htmlspecialchars($row['message']) ?><br>
        <small class="text-muted"><?= $row['created_at'] ?></small>
      </div>
    </div>
  <?php endwhile; ?>
</div>

<form method="post">
  <div class="input-group">
    <input type="text" name="message" class="form-control" placeholder="Tulis pesan..." required>
    <button type="submit" class="btn btn-success">Kirim</button>
  </div>
</form>

 <a href="index.php" class="btn btn-secondary mt-3">← Kembali</a>
</body>
</html>
