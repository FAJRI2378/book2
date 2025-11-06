<?php
session_start();
include 'koneksi.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
  die("Harus login dulu!");
}

// Cek CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
  die("CSRF token tidak valid!");
}

$book_id = (int) $_POST['book_id'];
$rating  = (int) $_POST['rating'];
$comment = trim($_POST['comment']);
$user_id = $_SESSION['user_id'];

if ($rating < 1 || $rating > 5 || empty($comment)) {
  die("Input tidak valid!");
}

$stmt = $conn->prepare("INSERT INTO reviews (book_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("iiis", $book_id, $user_id, $rating, $comment);
$stmt->execute();

header("Location: book_detail.php?id=$book_id");
exit;
