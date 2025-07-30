<?php
session_start();
include '../../koneksi.php';

// Validasi koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Ambil dan validasi data input
$sender_id = isset($_POST['sender_id']) ? (int) $_POST['sender_id'] : null;
$receiver_id = isset($_POST['receiver_id']) ? (int) $_POST['receiver_id'] : null;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if (!$sender_id || !$receiver_id || $message === '') {
    die("Data tidak lengkap.");
}

// Amankan input
$message = mysqli_real_escape_string($conn, $message);

// Simpan ke database
$query = "INSERT INTO chats (sender_id, receiver_id, message) VALUES ($sender_id, $receiver_id, '$message')";
if (!mysqli_query($conn, $query)) {
    die("Gagal mengirim pesan: " . mysqli_error($conn));
}

// Arahkan kembali ke halaman chat
header("Location: chat_room.php?user_id=$receiver_id");
exit;
