<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$book_id = $_POST['book_id'];

$stmt = $conn->prepare("INSERT IGNORE INTO wishlist (user_id, book_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $book_id);
$stmt->execute();

header("Location: index.php");
exit;
?>
