<?php
session_start();
include '../koneksi.php';

// Cek session admin
if (empty($_SESSION['user_id']) || empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

// Proses AJAX update status & catatan
if (isset($_POST['update_status'])) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'];
    $catatan = trim($_POST['catatan'] ?? '');

    $stmt = $conn->prepare("UPDATE orders SET status = ?, catatan_pengiriman = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("ssi", $status, $catatan, $id);
        echo $stmt->execute() ? "success" : "error: " . $stmt->error;
        $stmt->close();
    } else {
        echo "error: " . $conn->error;
    }
    exit;
}

// Ambil data orders dengan statistik
$stmt = $conn->prepare("
    SELECT o.*, u.username, b.title, b.image, b.price
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    JOIN books b ON o.book_id = b.id
    ORDER BY u.username ASC, o.order_date DESC
");
$orders = [];
$stats = ['pending' => 0, 'confirmed' => 0, 'dalam_perjalanan' => 0, 'sampai' => 0, 'cancelled' => 0, 'total' => 0];
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $orders[$row['username']][] = $row;
        $stats[$row['status']]++;
        $stats['total']++;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Pesanan - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --primary: #4361ee;
      --secondary: #3f37c9;
      --success: #06d6a0;
      --warning: #ffd60a;
      --danger: #ef476f;
      --info: #4cc9f0;
      --dark: #2b2d42;
      --light: #f8f9fa;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      font-family: 'Inter', 'Segoe UI', sans-serif;
      min-height: 100vh;
      padding-bottom: 3rem;
    }
    
    /* Header dengan animasi gradient */
    .admin-header {
      background: linear-gradient(135deg, rgba(67, 97, 238, 0.95), rgba(59, 130, 246, 0.95));
      backdrop-filter: blur(10px);
      color: white;
      padding: 2.5rem 0;
      text-align: center;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
      position: relative;
      overflow: hidden;
    }
    
    .admin-header::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
      background-size: 50px 50px;
      animation: moveGrid 20s linear infinite;
    }
    
    @keyframes moveGrid {
      0% { transform: translate(0, 0); }
      100% { transform: translate(50px, 50px); }
    }
    
    .admin-header h1 {
      font-weight: 800;
      font-size: 2.5rem;
      margin-bottom: 0.5rem;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
      position: relative;
      z-index: 1;
    }
    
    .admin-header p {
      font-size: 1.1rem;
      opacity: 0.95;
      position: relative;
      z-index: 1;
    }
    
    /* Container dengan backdrop blur */
    .main-container {
      background: rgba(255,255,255,0.95);
      backdrop-filter: blur(20px);
      border-radius: 30px;
      padding: 2.5rem;
      margin-top: -30px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    
    /* Statistics Cards */
    .stats-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 1.2rem;
      margin-bottom: 2rem;
    }
    
    .stat-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 20px;
      padding: 1.5rem;
      color: white;
      text-align: center;
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .stat-card::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,0.2), transparent);
      transition: all 0.5s ease;
    }
    
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 30px rgba(0,0,0,0.25);
    }
    
    .stat-card:hover::before {
      top: -25%;
      right: -25%;
    }
    
    .stat-card.pending { background: linear-gradient(135deg, #ffd60a, #ff9800); }
    .stat-card.confirmed { background: linear-gradient(135deg, #06d6a0, #00b894); }
    .stat-card.dalam_perjalanan { background: linear-gradient(135deg, #4cc9f0, #0096c7); }
    .stat-card.sampai { background: linear-gradient(135deg, #7209b7, #560bad); }
    .stat-card.cancelled { background: linear-gradient(135deg, #ef476f, #d62828); }
    
    .stat-icon {
      font-size: 2.5rem;
      margin-bottom: 0.5rem;
      opacity: 0.9;
    }
    
    .stat-number {
      font-size: 2rem;
      font-weight: 800;
      margin: 0.3rem 0;
    }
    
    .stat-label {
      font-size: 0.85rem;
      opacity: 0.95;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-weight: 600;
    }
    
    /* Search bar modern */
    .search-wrapper {
      position: relative;
      margin-bottom: 2rem;
    }
    
    #searchInput {
      width: 100%;
      padding: 1rem 3.5rem 1rem 1.5rem;
      border: 2px solid #e0e0e0;
      border-radius: 50px;
      font-size: 1rem;
      transition: all 0.3s ease;
      background: white;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    
    #searchInput:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 6px 25px rgba(67, 97, 238, 0.2);
      transform: translateY(-2px);
    }
    
    .search-icon {
      position: absolute;
      right: 1.5rem;
      top: 50%;
      transform: translateY(-50%);
      color: #999;
      font-size: 1.2rem;
      pointer-events: none;
    }
    
    /* User cards dengan animasi */
    .card-user {
      border-radius: 20px;
      margin-bottom: 1.5rem;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
      border: none;
      overflow: hidden;
      transition: all 0.3s ease;
      background: white;
    }
    
    .card-user:hover {
      box-shadow: 0 12px 35px rgba(0,0,0,0.15);
      transform: translateY(-3px);
    }
    
    .card-user .card-header {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
      font-weight: 600;
      cursor: pointer;
      padding: 1.2rem 1.5rem;
      border: none;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    
    .card-user .card-header:hover {
      background: linear-gradient(135deg, var(--secondary), var(--primary));
    }
    
    .user-badge {
      background: rgba(255,255,255,0.25);
      backdrop-filter: blur(10px);
      padding: 0.3rem 0.8rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
    }
    
    /* Status badges dengan efek */
    .status-badge {
      border-radius: 25px;
      padding: 0.5rem 1rem;
      font-size: 0.85rem;
      font-weight: 600;
      text-transform: capitalize;
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
    }
    
    .status-badge:hover {
      transform: scale(1.05);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .status-pending { background: linear-gradient(135deg, #ffd60a, #ffba08); color: #212529; }
    .status-confirmed { background: linear-gradient(135deg, #06d6a0, #00b894); color: white; }
    .status-dalam_perjalanan { background: linear-gradient(135deg, #4cc9f0, #0096c7); color: white; }
    .status-sampai { background: linear-gradient(135deg, #7209b7, #560bad); color: white; }
    .status-cancelled { background: linear-gradient(135deg, #ef476f, #d62828); color: white; }
    
    /* Table styling modern */
    .table {
      border-collapse: separate;
      border-spacing: 0;
    }
    
    .table thead th {
      background: linear-gradient(135deg, #f0f4ff, #e3ebff);
      color: var(--dark);
      font-weight: 700;
      text-transform: uppercase;
      font-size: 0.8rem;
      letter-spacing: 0.5px;
      padding: 1rem;
      border: none;
    }
    
    .table tbody tr {
      transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
      background: #f8f9ff;
      transform: scale(1.01);
    }
    
    .table tbody td {
      padding: 1rem;
      vertical-align: middle;
      border-bottom: 1px solid #f0f0f0;
    }
    
    /* Book image dengan efek */
    .book-img {
      width: 60px;
      height: 85px;
      object-fit: cover;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      transition: all 0.3s ease;
    }
    
    .book-img:hover {
      transform: scale(1.8) rotate(5deg);
      box-shadow: 0 8px 25px rgba(0,0,0,0.3);
      z-index: 10;
    }
    
    /* Dropdown button modern */
    .btn-status {
      border-radius: 50%;
      background: linear-gradient(135deg, #e3f2fd, #bbdefb);
      color: var(--primary);
      border: none;
      transition: all 0.3s ease;
      padding: 0.5rem 0.7rem;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    
    .btn-status:hover {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
      transform: rotate(180deg) scale(1.1);
      box-shadow: 0 5px 15px rgba(67, 97, 238, 0.4);
    }
    
    .dropdown-menu {
      border-radius: 15px;
      border: none;
      box-shadow: 0 10px 40px rgba(0,0,0,0.15);
      padding: 0.5rem;
      animation: slideDown 0.3s ease;
      background: rgba(255,255,255,0.98);
      backdrop-filter: blur(10px);
    }
    
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .dropdown-menu .dropdown-item {
      border-radius: 10px;
      padding: 0.7rem 1rem;
      display: flex;
      align-items: center;
      gap: 10px;
      transition: all 0.2s ease;
      font-weight: 500;
      margin-bottom: 0.2rem;
    }
    
    .dropdown-menu .dropdown-item:hover {
      background: linear-gradient(135deg, #f0f4ff, #e3ebff);
      color: var(--primary);
      transform: translateX(5px);
    }
    
    /* Button struk modern */
    .btn-struk {
      border-radius: 25px;
      padding: 0.5rem 1.2rem;
      font-size: 0.85rem;
      font-weight: 600;
      background: linear-gradient(135deg, var(--success), #00b894);
      border: none;
      box-shadow: 0 4px 15px rgba(6, 214, 160, 0.3);
      transition: all 0.3s ease;
      color: white;
    }
    
    .btn-struk:hover {
      background: linear-gradient(135deg, #00b894, var(--success));
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(6, 214, 160, 0.4);
      color: white;
    }
    
    /* Back button */
    .btn-back {
      border-radius: 25px;
      padding: 0.7rem 1.5rem;
      font-weight: 600;
      background: rgba(255,255,255,0.95);
      color: var(--dark);
      border: 2px solid rgba(255,255,255,0.3);
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
    }
    
    .btn-back:hover {
      background: white;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0,0,0,0.15);
      color: var(--primary);
    }
    
    /* Modal struk modern */
    .modal-content {
      border-radius: 25px;
      border: none;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      overflow: hidden;
    }
    
    .modal-header {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
      border: none;
      padding: 1.5rem;
    }
    
    .modal-title {
      font-weight: 700;
    }
    
    .modal-body {
      padding: 2rem;
    }
    
    #strukDetail {
      background: #f8f9fa;
      border-radius: 15px;
      padding: 1.5rem;
    }
    
    #strukDetail p {
      margin-bottom: 0.8rem;
      padding-bottom: 0.8rem;
      border-bottom: 1px dashed #dee2e6;
    }
    
    #strukDetail p:last-child {
      border-bottom: none;
    }
    
    /* Empty state */
    .empty-state {
      text-align: center;
      padding: 5rem 2rem;
      color: #6c757d;
    }
    
    .empty-state i {
      font-size: 5rem;
      margin-bottom: 1.5rem;
      opacity: 0.3;
    }
    
    .empty-state h4 {
      font-weight: 700;
      margin-bottom: 0.5rem;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .admin-header h1 {
        font-size: 1.8rem;
      }
      
      .stats-container {
        grid-template-columns: repeat(2, 1fr);
      }
      
      .main-container {
        padding: 1.5rem;
        border-radius: 20px;
      }
      
      .table {
        font-size: 0.85rem;
      }
      
      .book-img {
        width: 45px;
        height: 65px;
      }
    }
    
    /* Loading animation */
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }
    
    .updating {
      animation: pulse 1s ease-in-out infinite;
    }
  </style>
</head>
<body>

<header class="admin-header">
  <h1><i class="fas fa-boxes me-2"></i>Kelola Pesanan</h1>
  <p>Monitor dan update status pesanan pelanggan secara real-time</p>
</header>

<div class="container my-4">
  <div class="main-container">
    <a href="books.php" class="btn btn-back mb-4">
      <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Buku
    </a>

    <!-- Statistics Cards -->
    <div class="stats-container">
      <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
        <div class="stat-number"><?= $stats['total'] ?></div>
        <div class="stat-label">Total Pesanan</div>
      </div>
      <div class="stat-card pending">
        <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
        <div class="stat-number"><?= $stats['pending'] ?></div>
        <div class="stat-label">Pending</div>
      </div>
      <div class="stat-card confirmed">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-number"><?= $stats['confirmed'] ?></div>
        <div class="stat-label">Confirmed</div>
      </div>
      <div class="stat-card dalam_perjalanan">
        <div class="stat-icon"><i class="fas fa-truck"></i></div>
        <div class="stat-number"><?= $stats['dalam_perjalanan'] ?></div>
        <div class="stat-label">Dalam Perjalanan</div>
      </div>
      <div class="stat-card sampai">
        <div class="stat-icon"><i class="fas fa-box-open"></i></div>
        <div class="stat-number"><?= $stats['sampai'] ?></div>
        <div class="stat-label">Sampai</div>
      </div>
      <div class="stat-card cancelled">
        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
        <div class="stat-number"><?= $stats['cancelled'] ?></div>
        <div class="stat-label">Cancelled</div>
      </div>
    </div>

    <!-- Search Bar -->
    <div class="search-wrapper">
      <input type="text" id="searchInput" placeholder="Cari berdasarkan nama pengguna atau judul buku...">
      <i class="fas fa-search search-icon"></i>
    </div>

    <?php if (empty($orders)): ?>
      <div class="empty-state">
        <i class="fas fa-box-open"></i>
        <h4>Belum ada pesanan</h4>
        <p>Pesanan pelanggan akan muncul di sini</p>
      </div>
    <?php else: ?>
      <div id="ordersContainer">
        <?php foreach ($orders as $username => $userOrders): ?>
          <div class="card card-user" data-username="<?= strtolower($username) ?>">
            <div class="card-header" data-bs-toggle="collapse" data-bs-target="#user-<?= md5($username) ?>" aria-expanded="false">
              <div>
                <i class="fas fa-user-circle me-2"></i>
                <strong><?= htmlspecialchars($username) ?></strong>
                <span class="user-badge ms-2"><?= count($userOrders) ?> pesanan</span>
              </div>
              <i class="fas fa-chevron-down"></i>
            </div>
            <div id="user-<?= md5($username) ?>" class="collapse">
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table align-middle mb-0">
                    <thead>
                      <tr>
                        <th>No</th>
                        <th>ID Order</th>
                        <th>Judul Buku</th>
                        <th>Cover</th>
                        <th>Qty</th>
                        <th>Tanggal</th>
                        <th>Pembayaran</th>
                        <th>Alamat Pengiriman</th>
                        <th>Status</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php $no = 1; foreach ($userOrders as $row): ?>
                        <tr id="row-<?= $row['id'] ?>" data-book="<?= strtolower($row['title']) ?>">
                          <td><strong><?= $no++ ?></strong></td>
                          <td><span class="badge bg-primary">#<?= $row['id'] ?></span></td>
                          <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
                          <td>
                            <?php if (!empty($row['image'])): ?>
                              <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" class="book-img" alt="Cover">
                            <?php else: ?>
                              <span class="text-muted"><i class="fas fa-image"></i></span>
                            <?php endif; ?>
                          </td>
                          <td><strong><?= $row['jumlah'] ?></strong></td>
                          <td>
                            <small><i class="far fa-calendar-alt me-1"></i><?= date('d/m/Y', strtotime($row['order_date'])) ?></small><br>
                            <small><i class="far fa-clock me-1"></i><?= date('H:i', strtotime($row['order_date'])) ?></small>
                          </td>
                          <td>
                            <span class="badge" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                              <i class="fas fa-credit-card me-1"></i><?= ucfirst($row['payment_method']) ?>
                            </span>
                          </td>
                          <td style="max-width: 200px;">
                            <small><?= htmlspecialchars($row['shipping_address'] ?? '-') ?></small>
                          </td>
                        <td><?= !empty($row['catatan_pengiriman']) 
                        ? nl2br(htmlspecialchars($row['catatan_pengiriman'])) 
                        : '<span class="text-muted">Belum ada</span>'; ?></td>
                          <td>
                            <span class="status-badge status-<?= htmlspecialchars($row['status']) ?>">
                              <?php
                              $statusIcons = [
                                'pending' => 'fa-hourglass-half',
                                'confirmed' => 'fa-check-circle',
                                'dalam_perjalanan' => 'fa-truck',
                                'sampai' => 'fa-box-open',
                                'cancelled' => 'fa-times-circle'
                              ];
                              ?>
                              <i class="fas <?= $statusIcons[$row['status']] ?? 'fa-circle' ?>"></i>
                              <?= ucwords(str_replace('_', ' ', $row['status'])) ?>
                            </span>
                            <div class="dropdown d-inline ms-1">
                              <button class="btn btn-status dropdown-toggle" data-bs-toggle="dropdown" title="Ubah Status">
                                <i class="fas fa-sync-alt"></i>
                              </button>
                              <ul class="dropdown-menu">
                                <li><button class="dropdown-item" onclick="updateStatus(<?= $row['id'] ?>, 'pending')">
                                  <i class="fas fa-hourglass-half text-warning"></i> Pending
                                </button></li>
                                <li><button class="dropdown-item" onclick="updateStatus(<?= $row['id'] ?>, 'confirmed')">
                                  <i class="fas fa-check-circle text-success"></i> Confirmed
                                </button></li>
                                <li><button class="dropdown-item" onclick="updateStatus(<?= $row['id'] ?>, 'dalam_perjalanan')">
                                  <i class="fas fa-truck text-info"></i> Dalam Perjalanan
                                </button></li>
                                <li><button class="dropdown-item" onclick="updateStatus(<?= $row['id'] ?>, 'sampai')">
                                  <i class="fas fa-box-open text-primary"></i> Sampai
                                </button></li>
                                <li><button class="dropdown-item" onclick="updateStatus(<?= $row['id'] ?>, 'cancelled')">
                                  <i class="fas fa-times-circle text-danger"></i> Cancelled
                                </button></li>
                                <button class="btn btn-warning" style="border-radius:25px; font-size:0.85rem; font-weight:600;"
                                onclick="openCatatanModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['catatan_pengiriman'] ?? '') ?>')">
                                <i class="fas fa-sticky-note me-1"></i> Catatan
                              </button>
                              </ul>
                            </div>
                          </td>
                          <td>
                            <button class="btn btn-struk" onclick="cetakStruk(
                              '<?= $row['id'] ?>',
                              '<?= htmlspecialchars($username) ?>',
                              '<?= htmlspecialchars($row['title']) ?>',
                              '<?= $row['jumlah'] ?>',
                              '<?= date('d/m/Y H:i', strtotime($row['order_date'])) ?>',
                              '<?= ucwords(str_replace('_', ' ', $row['status'])) ?>',
                              '<?= ucfirst($row['payment_method']) ?>',
                              '<?= htmlspecialchars($row['shipping_address']) ?>'
                            )">
                              <i class="fas fa-receipt me-1"></i> Struk
                            </button>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal Struk -->
<div class="modal fade" id="modalStruk" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-receipt me-2"></i>Struk Pesanan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="strukDetail"></div>
      </div>
      <div class="modal-footer justify-content-center border-0">
        <button type="button" class="btn btn-primary" style="border-radius: 25px; padding: 0.7rem 2rem;" onclick="printStruk()">
          <i class="fas fa-print me-2"></i>Print Struk
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Catatan Pengiriman -->
<div class="modal fade" id="modalCatatan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-sticky-note me-2"></i>Catatan Pengiriman</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="catatanOrderId">
        <textarea id="catatanText" class="form-control" rows="5" placeholder="Tuliskan catatan pengiriman..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" onclick="simpanCatatan()">Simpan</button>
      </div>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updateStatus(id, status) {
  if (!confirm(`Yakin mengubah status menjadi "${status.replace(/_/g, ' ')}"?`)) return;
  
  const row = document.getElementById(`row-${id}`);
  row.classList.add('updating');
  
  const formData = new FormData();
  formData.append('update_status', true);
  formData.append('id', id);
  formData.append('status', status);

  fetch(window.location.href, { method: 'POST', body: formData })
  .then(r => r.text())
  .then(res => {
    row.classList.remove('updating');
    if (res.trim() === "success") {
      const badge = document.querySelector(`#row-${id} .status-badge`);
      badge.className = `status-badge status-${status}`;
      
      const statusIcons = {
        'pending': 'fa-hourglass-half',
        'confirmed': 'fa-check-circle',
        'dalam_perjalanan': 'fa-truck',
        'sampai': 'fa-box-open',
        'cancelled': 'fa-times-circle'
      };
      
      badge.innerHTML = `<i class="fas ${statusIcons[status]}"></i> ${status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())}`;
      
      // Update statistics
      updateStatistics();
      
      // Show success notification
      showNotification('Status berhasil diperbarui!', 'success');
    } else {
      showNotification('Gagal update status: ' + res, 'error');
    }
  })
  .catch(err => {
    row.classList.remove('updating');
    showNotification('Terjadi kesalahan: ' + err, 'error');
  });
}

function openCatatanModal(orderId, currentNote) {
  document.getElementById('catatanOrderId').value = orderId;
  document.getElementById('catatanText').value = currentNote || '';
  new bootstrap.Modal(document.getElementById('modalCatatan')).show();
}

function simpanCatatan() {
  const id = document.getElementById('catatanOrderId').value;
  const catatan = document.getElementById('catatanText').value;

  const formData = new FormData();
  formData.append('update_status', true);
  formData.append('id', id);
  formData.append('status', document.querySelector(`#row-${id} .status-badge`).className.split('status-')[1]);
  formData.append('catatan', catatan);

  fetch(window.location.href, { method: 'POST', body: formData })
    .then(r => r.text())
    .then(res => {
      if (res.trim() === "success") {
        showNotification('Catatan pengiriman berhasil disimpan!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalCatatan')).hide();
      } else {
        showNotification('Gagal menyimpan catatan: ' + res, 'error');
      }
    })
    .catch(err => showNotification('Terjadi kesalahan: ' + err, 'error'));
}


// Search filter dengan highlight
document.getElementById('searchInput').addEventListener('input', function() {
  const keyword = this.value.toLowerCase();
  const userCards = document.querySelectorAll('.card-user');
  let visibleCount = 0;

  userCards.forEach(card => {
    const username = card.dataset.username;
    let match = username.includes(keyword);

    const rows = card.querySelectorAll('tbody tr');
    let anyVisible = false;

    rows.forEach(row => {
      const bookTitle = row.dataset.book;
      if (username.includes(keyword) || bookTitle.includes(keyword)) {
        row.style.display = '';
        anyVisible = true;
      } else {
        row.style.display = 'none';
      }
    });

    if (match || anyVisible) {
      card.style.display = '';
      visibleCount++;
    } else {
      card.style.display = 'none';
    }
  });

  // Show no results message
  const container = document.getElementById('ordersContainer');
  let noResultsMsg = document.getElementById('noResultsMsg');
  
  if (visibleCount === 0 && keyword !== '') {
    if (!noResultsMsg) {
      noResultsMsg = document.createElement('div');
      noResultsMsg.id = 'noResultsMsg';
      noResultsMsg.className = 'empty-state';
      noResultsMsg.innerHTML = `
        <i class="fas fa-search"></i>
        <h4>Tidak ada hasil</h4>
        <p>Tidak ditemukan pesanan dengan kata kunci "${keyword}"</p>
      `;
      container.appendChild(noResultsMsg);
    }
  } else if (noResultsMsg) {
    noResultsMsg.remove();
  }
});

function cetakStruk(id, user, book, jumlah, date, status, payment, address) {
  const detail = `
    <p><strong><i class="fas fa-hashtag me-2"></i>ID Order:</strong> ${id}</p>
    <p><strong><i class="fas fa-user me-2"></i>Pelanggan:</strong> ${user}</p>
    <p><strong><i class="fas fa-book me-2"></i>Judul Buku:</strong> ${book}</p>
    <p><strong><i class="fas fa-shopping-cart me-2"></i>Jumlah:</strong> ${jumlah} item</p>
    <p><strong><i class="far fa-calendar-alt me-2"></i>Tanggal:</strong> ${date}</p>
    <p><strong><i class="fas fa-info-circle me-2"></i>Status:</strong> <span class="badge bg-primary">${status}</span></p>
    <p><strong><i class="fas fa-credit-card me-2"></i>Metode Bayar:</strong> ${payment}</p>
    <p><strong><i class="fas fa-map-marker-alt me-2"></i>Alamat Pengiriman:</strong><br>${address}</p>
  `;
  document.getElementById("strukDetail").innerHTML = detail;
  new bootstrap.Modal(document.getElementById('modalStruk')).show();
}

function printStruk() {
  const html = document.getElementById("strukDetail").innerHTML;
  const printWindow = window.open("", "", "width=400,height=600");
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Struk Pesanan</title>
      <style>
        body {
          font-family: 'Courier New', monospace;
          padding: 20px;
          max-width: 350px;
          margin: 0 auto;
        }
        h2 {
          text-align: center;
          border-bottom: 2px dashed #000;
          padding-bottom: 10px;
        }
        p {
          margin: 10px 0;
          line-height: 1.6;
        }
        strong {
          display: inline-block;
          width: 150px;
        }
        .footer {
          text-align: center;
          margin-top: 30px;
          padding-top: 15px;
          border-top: 2px dashed #000;
          font-size: 12px;
        }
      </style>
    </head>
    <body>
      <h2>STRUK PESANAN</h2>
      ${html}
      <div class="footer">
        Terima kasih atas pesanan Anda!<br>
        ${new Date().toLocaleString('id-ID')}
      </div>
    </body>
    </html>
  `);
  printWindow.document.close();
  setTimeout(() => {
    printWindow.print();
  }, 250);
}

function updateStatistics() {
  const statusCounts = {
    pending: 0,
    confirmed: 0,
    dalam_perjalanan: 0,
    sampai: 0,
    cancelled: 0,
    total: 0
  };
  
  document.querySelectorAll('.status-badge').forEach(badge => {
    const status = badge.className.match(/status-(\w+)/)[1];
    if (statusCounts.hasOwnProperty(status)) {
      statusCounts[status]++;
      statusCounts.total++;
    }
  });
  
  // Update stat cards
  const statCards = document.querySelectorAll('.stat-card');
  statCards[0].querySelector('.stat-number').textContent = statusCounts.total;
  statCards[1].querySelector('.stat-number').textContent = statusCounts.pending;
  statCards[2].querySelector('.stat-number').textContent = statusCounts.confirmed;
  statCards[3].querySelector('.stat-number').textContent = statusCounts.dalam_perjalanan;
  statCards[4].querySelector('.stat-number').textContent = statusCounts.sampai;
  statCards[5].querySelector('.stat-number').textContent = statusCounts.cancelled;
}

function showNotification(message, type) {
  const notification = document.createElement('div');
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 1.5rem;
    background: ${type === 'success' ? 'linear-gradient(135deg, #06d6a0, #00b894)' : 'linear-gradient(135deg, #ef476f, #d62828)'};
    color: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    z-index: 9999;
    font-weight: 600;
    animation: slideInRight 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
  `;
  
  const icon = type === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-circle"></i>';
  notification.innerHTML = `${icon} ${message}`;
  
  document.body.appendChild(notification);
  
  setTimeout(() => {
    notification.style.animation = 'slideOutRight 0.3s ease';
    setTimeout(() => notification.remove(), 300);
  }, 3000);
}

// Add animation keyframes
const style = document.createElement('style');
style.textContent = `
  @keyframes slideInRight {
    from {
      transform: translateX(100%);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
  
  @keyframes slideOutRight {
    from {
      transform: translateX(0);
      opacity: 1;
    }
    to {
      transform: translateX(100%);
      opacity: 0;
    }
  }
`;
document.head.appendChild(style);

// Auto-collapse cards on load
document.addEventListener('DOMContentLoaded', function() {
  // Add smooth scroll behavior
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });
});
</script>
</body>
</html>

