<?php
session_start();
include '../koneksi.php';

// Ambil semua user kecuali admin (misalnya admin punya ID = 1)
$users = mysqli_query($conn, "SELECT * FROM users WHERE id != 1");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Chat Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3>💬 Chat dengan User</h3>
    <ul class="list-group">
        <?php while ($user = mysqli_fetch_assoc($users)): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= htmlspecialchars($user['nama']) ?>
                <a href="chat_room.php?user_id=<?= $user['id'] ?>" class="btn btn-sm btn-primary">Buka Chat</a>
            </li>
        <?php endwhile; ?>
    </ul>
</div>
</body>
</html>
