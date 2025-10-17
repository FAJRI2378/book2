<?php
session_start();
include 'koneksi.php';

// Cek session user
if (empty($_SESSION['user_id']) || empty($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

// Ambil data About Us dari tabel settings
$about = ['value' => 'Belum ada informasi About Us.', 'image' => ''];
$stmt = $conn->prepare("SELECT value, image FROM settings WHERE name = ?");
if ($stmt) {
    $name = 'about';
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $about = $row;
    }
    $stmt->close();
} else {
    error_log("Error preparing about query: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us - BookStore</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: url('assets/bg.jpg') no-repeat center center fixed;
      background-size: cover;
      color: #333;
      margin: 0;
      padding-top: 70px;
    }
    body::before {
      content: "";
      position: fixed;
      top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.7);
      z-index: -1;
    }
    .about {
      padding: 50px 30px;
      background-color: rgba(255, 255, 255, 0.98);
      border-radius: 20px;
      max-width: 10000px;
      margin: 30px auto;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }
    .about h2 {
      text-align: center;
      color: #007bff;
      margin-bottom: 30px;
    }
    .about img {
      max-width: 10000px;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
      margin-bottom: 200px;
      
    }
    .about-text {
      text-align: justify;
      line-height: 1.8;
      font-size: 1.1rem;
    }
    .navbar {
      backdrop-filter: blur(10px);
      background-color: rgba(0, 0, 0, 0.9) !important;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="user_dashboard.php"><i class="fas fa-book-open me-2"></i>BookStore</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Lanjutkan</a></li>
      </ul>
    </div>
  </div>
</nav>

<section class="about">
  <h2><i class="fas fa-building me-2"></i>About Us</h2>
  <div class="text-center">
    <?php if (!empty($about['image'])): ?>
      <img src="assets/foto1.png" alt="About Us">
      <img src="assets/foto1.png" alt="About Us">
      <img src="assets/foto1.png" alt="About Us">
    <?php endif; ?>
    <div class="about-text mt-3">
      <?= nl2br(htmlspecialchars($about['value'])) ?>
    </div>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
