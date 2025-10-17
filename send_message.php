<?php
session_start();
include 'koneksi.php';

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'user') exit;

$user_id  = $_SESSION['user_id'];
$admin_id = 2;
$msg = trim($_POST['message'] ?? '');
$reply_to = (int)($_POST['reply_to'] ?? 0);

if ($msg !== '') {
    $msg = mysqli_real_escape_string($conn, $msg);
    mysqli_query($conn, "
        INSERT INTO chats (sender_id, receiver_id, message, reply_to, is_read, created_at)
        VALUES ($user_id, $admin_id, '$msg', $reply_to, 0, NOW())
    ");
}
