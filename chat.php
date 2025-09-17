<?php
session_start();
include 'koneksi.php';

// ✅ Pastikan user login
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$user_id  = $_SESSION['user_id']; // ambil dari session
$admin_id = 2; // ID admin tetap

// ---------------------------
// Proses kirim pesan
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $msg = mysqli_real_escape_string($conn, $_POST['message']);
    if (!empty($msg)) {
        mysqli_query($conn, "
            INSERT INTO chats (sender_id, receiver_id, message, created_at) 
            VALUES ($user_id, $admin_id, '$msg', NOW())
        ");
    }
    header("Location: chat.php");
    exit;
}

// ---------------------------
// Ambil pesan antara user dan admin
// ---------------------------
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
        body { font-family: Arial, sans-serif; padding-top: 20px; background: #f2f2f2; }
        .chat-box { height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; background: #fff; border-radius: 8px; }
        .bubble { padding: 10px 15px; border-radius: 15px; max-width: 70%; margin-bottom: 10px; }
        .from-me { background: #dcfce7; margin-left: auto; text-align: right; }
        .from-them { background: #e0f2fe; margin-right: auto; text-align: left; }
        .message-row { display: flex; }
    </style>
</head>
<body class="container">
    <h4>💬 Chat dengan Admin</h4>

    <div class="chat-box" id="chat-box">
        <?php while ($row = mysqli_fetch_assoc($messages)): ?>
            <div class="message-row">
                <div class="bubble <?= $row['sender_id'] == $user_id ? 'from-me' : 'from-them' ?>">
                    <?= htmlspecialchars($row['message']) ?><br>
                    <small class="text-muted"><?= $row['created_at'] ?></small>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <form method="post">
        <div class="input-group mb-3">
            <input type="text" name="message" class="form-control" placeholder="Tulis pesan..." required>
            <button type="submit" class="btn btn-success">Kirim</button>
        </div>
    </form>

    <a href="index.php" class="btn btn-secondary mt-3">← Kembali</a>

    <script>
        // Auto scroll ke bawah setiap reload
        const chatBox = document.getElementById('chat-box');
        chatBox.scrollTop = chatBox.scrollHeight;
    </script>
</body>
</html>
