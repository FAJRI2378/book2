<?php
session_start();
include '../koneksi.php';

// Cek session admin
if (empty($_SESSION['user_id']) || empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

// Ambil semua user dengan prepared statement
$stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE id != ? ORDER BY username ASC");
if ($stmt) {
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();
} else {
    error_log("Error preparing users query: " . $conn->error);
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar User Terdaftar - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --primary-color: #007bff;
      --secondary-color: #6c757d;
      --success-color: #28a745;
      --danger-color: #dc3545;
      --warning-color: #ffc107;
    }
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .admin-header {
      background: linear-gradient(135deg, var(--primary-color), #0056b3);
      color: white;
      padding: 2rem 0;
    }
    .admin-header h1 {
      font-weight: bold;
      margin-bottom: 0.5rem;
    }
    .admin-header p {
      opacity: 0.9;
      margin-bottom: 0;
    }
    .users-table {
      max-width: 1000px;
      margin: 0 auto;
    }
    .table {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    .table th {
      background-color: var(--primary-color);
      color: white;
      font-weight: 600;
      border: none;
      padding: 1rem;
    }
    .table td {
      padding: 1rem;
      vertical-align: middle;
      border-color: #dee2e6;
    }
    .role-badge {
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-weight: 500;
      font-size: 0.875rem;
    }
    .role-user { background-color: #d1ecf1; color: #0c5460; }
    .role-admin { background-color: #d4edda; color: #155724; }
    .no-users {
      text-align: center;
      padding: 3rem;
      color: var(--secondary-color);
    }
    .no-users i {
      font-size: 4rem;
      margin-bottom: 1rem;
      opacity: 0.5;
    }
    .back-btn {
      border-radius: 25px;
      padding: 0.75rem 1.5rem;
      font-weight: 600;
      background: var(--secondary-color);
      border: none;
      transition: all 0.2s ease;
      color: white;
      text-decoration: none;
    }
    .back-btn:hover {
      background: #5a6268;
      color: white;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .admin-header { padding: 1.5rem 0; }
      .table { font-size: 0.9rem; }
      .table th, .table td { padding: 0.75rem; }
    }
  </style>
</head>
<body>

<!-- Header -->
<header class="admin-header text-center">
  <div class="container">
    <h1><i class="fas fa-users me-2"></i>Daftar User Terdaftar</h1>
    <p>Kelola dan lihat daftar pengguna sistem</p>
  </div>
</header>

<!-- Main Content -->
<div class="container my-5">
  <div class="users-table">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="text-muted mb-0"><i class="fas fa-list me-2"></i>Total: <?= count($users) ?> User</h3>
      <a href="books.php" class="btn back-btn">
        <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Buku
      </a>
    </div>

    <?php if (empty($users)): ?>
      <div class="no-users">
        <i class="fas fa-user-slash"></i>
        <h4>Tidak ada user terdaftar</h4>
        <p>Belum ada pengguna yang terdaftar di sistem.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th><i class="fas fa-hashtag me-1"></i>No</th>
              <th><i class="fas fa-user me-1"></i>Username</th>
              <th><i class="fas fa-envelope me-1"></i>Email</th>
              <th><i class="fas fa-user-tag me-1"></i>Role</th>
              <th><i class="fas fa-user-tag me-1"></i>Created_at</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; foreach ($users as $user): ?>
              <tr>
                <td><?= $no ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td>
                  <span class="role-badge role-<?= htmlspecialchars($user['role']) ?>">
                    <?= ucfirst(htmlspecialchars($user['role'])) ?>
                  </span>
                </td>
                <td>
                  <?php
                    // Ambil created_at dari database
                    $stmt = $conn->prepare("SELECT created_at FROM users WHERE id = ?");
                    $stmt->bind_param("i", $user['id']);
                    $stmt->execute();
                    $stmt->bind_result($created_at);
                    $stmt->fetch();
                    $stmt->close();
                    echo htmlspecialchars($created_at);
                  ?>
              </tr>
              <?php $no++; endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>