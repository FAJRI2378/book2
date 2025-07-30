<?php
include 'koneksi.php';

$sender = $_POST['sender'] ?? 'anonymous';
$message = $_POST['message'] ?? '';

if ($message) {
    $stmt = $conn->prepare("INSERT INTO chats (sender, message) VALUES (?, ?)");
    $stmt->bind_param("ss", $sender, $message);
    $stmt->execute();
}
?>
