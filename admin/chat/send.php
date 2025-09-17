<?php
session_start();
include '../../koneksi.php';

// Validasi login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$sender_id   = $_SESSION['user_id'];
$receiver_id = isset($_POST['receiver_id']) ? (int) $_POST['receiver_id'] : 0;
$message     = trim($_POST['message']);

if ($receiver_id && $message !== '') {
    $message = mysqli_real_escape_string($conn, $message);
    $query = "INSERT INTO chats (sender_id, receiver_id, message, created_at) 
              VALUES ($sender_id, $receiver_id, '$message', NOW())";
    mysqli_query($conn, $query) or die("Gagal kirim pesan: " . mysqli_error($conn));
}


exit;
