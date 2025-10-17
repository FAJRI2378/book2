<?php
session_start();
include '../../koneksi.php';

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit('Unauthorized');
}

$sender_id = $_SESSION['user_id'];
$receiver_id = (int) $_POST['receiver_id'];
$message = trim($_POST['message']);
$reply_to = isset($_POST['reply_to']) ? (int) $_POST['reply_to'] : null;

if ($message !== '') {
    $stmt = $conn->prepare("
        INSERT INTO chats (sender_id, receiver_id, message, reply_to, is_read, created_at)
        VALUES (?, ?, ?, ?, 0, NOW())
    ");
    $stmt->bind_param("iisi", $sender_id, $receiver_id, $message, $reply_to);
    $stmt->execute();
    $stmt->close();
}
