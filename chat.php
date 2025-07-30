<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$admin_id = 1; // ganti dengan ID admin yang sesuai

// Proses kirim pesan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $msg = mysqli_real_escape_string($conn, $_POST['message']);
    mysqli_query($conn, "INSERT INTO chats (sender_id, receiver_id, message) VALUES ($user_id, $admin_id, '$msg')");
}

// Ambil pesan antara user dan admin
$messages = mysqli_query($conn, "
    SELECT * FROM chats 
    WHERE (sender_id = $user_id AND receiver_id = $admin_id) 
       OR (sender_id = $admin_id AND receiver_id = $user_id)
    ORDER BY created_at ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Chat Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .chat-box {
        height: 400px;
        overflow-y: scroll;
        border: 1px solid #ddd;
        padding: 15px;
        background: #f9f9f9;
        border-radius: 10px;
        margin-bottom: 10px;
    }
    .bubble {
        padding: 10px 15px;
        border-radius: 15px;
        margin-bottom: 10px;
        max-width: 75%;
        display: inline-block;
    }
    .from-me {
        background: #dcfce7;
        align-self: flex-end;
        float: right;
        text-align: right;
    }
    .from-them {
        background: #e0f2fe;
        align-self: flex-start;
        float: left;
        text-align: left;
    }
  </style>
</head>
<body class="container py-4">

<h2>💬 Chat dengan Admin</h2>

<div class="chat-box">
    <?php while ($msg = mysqli_fetch_assoc($messages)): ?>
        <div class="bubble <?= $msg['sender_id'] == $user_id ? 'from-me' : 'from-them' ?>">
            <?= htmlspecialchars($msg['message']) ?><br>
            <small class="text-muted"><?= $msg['created_at'] ?></small>
        </div>
        <div style="clear: both;"></div>
    <?php endwhile ?>
</div>

<form method="post" class="d-flex">
    <input type="text" name="message" class="form-control me-2" placeholder="Tulis pesan..." required>
    <button class="btn btn-success">Kirim</button>
</form>

<a href="index.php" class="btn btn-secondary mt-3">← Kembali</a>

</body>
</html>
