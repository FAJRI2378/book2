<?php
session_start();
include '../koneksi.php';

// Cek session admin
if (empty($_SESSION['user_id']) || empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

// Proses AJAX update status
if (isset($_POST['update_status'])) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "error: " . $conn->error;
    }
    exit;
}

// Ambil data orders dengan prepared statement
$stmt = $conn->prepare("
    SELECT o.*, u.username, b.stock, b.image 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    JOIN books b ON o.book_id = b.id
    ORDER BY o.order_date DESC
");
$orders = [];
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    $stmt->close();
} else {
    error_log("Error preparing orders query: " . $conn->error);
    $orders = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Pesanan - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --primary-color: #007bff;
      --secondary-color: #6c757d;
      --success-color: #28a745;
      --danger-color: #dc3545;
      --warning-color: #ffc107;
      --info-color: #17a2b8;
      --bg-light: #f8f9fa;
    }
    body {
      background-color: var(--bg-light);
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
    .orders-table {
      max-width: 1200px;
      margin: 0 auto;
    }
    .table {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      margin-bottom: 0;
    }
    .table th {
      background-color: var(--primary-color);
      color: white;
      font-weight: 600;
      border: none;
      padding: 1rem;
      position: sticky;
      top: 0;
      z-index: 10;
    }
    .table td {
      padding: 1rem;
      vertical-align: middle;
      border-color: #dee2e6;
    }
    .status-badge {
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-weight: 500;
      font-size: 0.875rem;
      text-transform: capitalize;
    }
    .status-pending { background-color: var(--warning-color); color: #212529; }
    .status-confirmed { background-color: var(--success-color); color: white; }
    .status-dalam_perjalanan { background-color: var(--info-color); color: white; }
    .status-sampai { background-color: #20c997; color: white; }
    .status-cancelled { background-color: var(--danger-color); color: white; }
    .book-img {
      width: 60px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    .dropdown {
      position: relative;
      display: inline-block;
    }
    .dropbtn {
      background: none;
      border: none;
      font-size: 18px;
      cursor: pointer;
      color: var(--secondary-color);
      padding: 0.25rem;
    }
    .dropdown-content {
      display: none;
      position: absolute;
      background: white;
      min-width: 160px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      border-radius: 8px;
      z-index: 1000;
      right: 0;
    }
    .dropdown-content button {
      width: 100%;
      padding: 0.75rem 1rem;
      text-align: left;
      border: none;
      background: white;
      cursor: pointer;
      color: #333;
      transition: background 0.2s ease;
    }
    .dropdown-content button:hover {
      background: #f8f9fa;
    }
    .dropdown:hover .dropdown-content {
      display: block;
    }
    .btn-struk {
      border-radius: 20px;
      padding: 0.5rem 1rem;
      font-weight: 600;
      background: var(--success-color);
      border: none;
      transition: all 0.2s ease;
    }
    .btn-struk:hover {
      background: #218838;
      transform: translateY(-1px);
    }
    .search-form {
      max-width: 400px;
      margin: 0 auto 1.5rem;
    }
    .search-form .form-control {
      border-radius: 25px 0 0 25px;
      border: 2px solid var(--primary-color);
    }
    .search-form .btn {
      border-radius: 0 25px 25px 0;
      background: var(--primary-color);
      border: none;
    }
    .no-orders {
      text-align: center;
      padding: 3rem;
      color: var(--secondary-color);
    }
    .no-orders i {
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
      color: white;
      text-decoration: none;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
    }
    .back-btn:hover {
      background: #5a6268;
      color: white;
    }

    /* Modal */
    .modal-content {
      border-radius: 15px;
      border: none;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }
    .modal-header {
      border-bottom: none;
      justify-content: center;
    }
    .modal-body {
      text-align: center;
    }
    .struk-detail p {
      margin-bottom: 0.75rem;
      text-align: left;
    }
    .btn-print {
      border-radius: 25px;
      padding: 0.75rem 1.5rem;
      font-weight: 600;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .admin-header { padding: 1.5rem 0; }
      .table { font-size: 0.9rem; }
      .table th, .table td { padding: 0.75rem; }
      .book-img { width: 50px; height: 65px; }
      .search-form { max-width: 100%; }
    }
  </style>
</head>
<body>

<!-- Header -->
<header class="admin-header text-center">
  <div class="container">
    <h1><i class="fas fa-boxes me-2"></i>Kelola Pesanan</h1>
    <p>Monitor dan update status pesanan pelanggan</p>
  </div>
</header>

<!-- Main Content -->
<div class="container my-5">
  <div class="orders-table">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="text-muted mb-0"><i class="fas fa-list me-2"></i>Total: <?= count($orders) ?> Pesanan</h3>
      <a href="books.php" class="back-btn">
        <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Buku
      </a>
    </div>

    <!-- Search Form -->
    <form class="search-form mb-4">
      <div class="input-group">
        <input type="text" id="searchInput" class="form-control" placeholder="🔍 Cari ID order, user, atau buku...">
        <button type="button" class="btn btn-primary" onclick="searchOrders()">
          <i class="fas fa-search"></i>
        </button>
      </div>
    </form>

    <?php if (empty($orders)): ?>
      <div class="no-orders">
        <i class="fas fa-box-open"></i>
        <h4>Tidak ada pesanan</h4>
        <p>Belum ada pesanan yang masuk ke sistem.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th><i class="fas fa-hashtag me-1"></i>No</th>
              <th><i class="fas fa-receipt me-1"></i>ID Order</th>
              <th><i class="fas fa-user me-1"></i>User</th>
              <th><i class="fas fa-book me-1"></i>Book ID</th>
              <th><i class="fas fa-image me-1"></i>Gambar</th>
              <th><i class="fas fa-hashtag me-1"></i>Jumlah</th>
              <th><i class="fas fa-clock me-1"></i>Waktu Order</th>
              <th><i class="fas fa-credit-card me-1"></i>Metode Bayar</th>
              <th><i class="fas fa-map-marker-alt me-1"></i>Alamat</th>
              <th><i class="fas fa-truck me-1"></i>Pengiriman</th>
              <th><i class="fas fa-tasks me-1"></i>Status</th>
              <th><i class="fas fa-cog me-1"></i>Aksi</th>
            </tr>
          </thead>
          <tbody id="orderTableBody">
            <?php $no = 1; foreach ($orders as $row): ?>
              <tr id="row-<?= $row['id'] ?>">
                <td><?= $no++ ?></td>
                <td><strong><?= $row['id'] ?></strong></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= $row['book_id'] ?></td>
                <td>
                  <?php if (!empty($row['image'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" alt="Buku" class="book-img" loading="lazy">
                  <?php else: ?>
                    <span class="text-muted">Tidak ada</span>
                  <?php endif; ?>
                </td>
                <td><strong><?= $row['jumlah'] ?></strong></td>
                <td><?= date('d/m/Y H:i', strtotime($row['order_date'])) ?></td>
                <td><?= ucfirst(htmlspecialchars($row['payment_method'])) ?></td>
                <td><?= htmlspecialchars($row['shipping_address'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['shipping_method'] ?? '-') ?></td>
                <td>
                  <span class="status-badge status-<?= htmlspecialchars($row['status']) ?>">
                    <?= ucwords(str_replace('_', ' ', $row['status'])) ?>
                  </span>
                  <div class="dropdown">
                    <button class="dropbtn" title="Ubah Status"><i class="fas fa-ellipsis-v"></i></button>
                    <div class="dropdown-content">
                      <button onclick="updateStatus(<?= $row['id'] ?>, 'pending')"><i class="fas fa-clock me-2"></i>Pending</button>
                      <button onclick="updateStatus(<?= $row['id'] ?>, 'confirmed')"><i class="fas fa-check-circle me-2"></i>Confirmed</button>
                      <button onclick="updateStatus(<?= $row['id'] ?>, 'dalam_perjalanan')"><i class="fas fa-shipping-fast me-2"></i>Dalam Perjalanan</button>
                      <button onclick="updateStatus(<?= $row['id'] ?>, 'sampai')"><i class="fas fa-home me-2"></i>Sampai</button>
                      <button onclick="updateStatus(<?= $row['id'] ?>, 'cancelled')"><i class="fas fa-times-circle me-2"></i>Cancelled</button>
                    </div>
                  </div>
                </td>
                <td>
                  <button class="btn btn-success btn-sm btn-struk" onclick="cetakStruk(
                    '<?= $row['id'] ?>',
                    '<?= htmlspecialchars($row['username']) ?>',
                    '<?= $row['book_id'] ?>',
                    '<?= $row['jumlah'] ?>',
                    '<?= date('d/m/Y H:i', strtotime($row['order_date'])) ?>',
                    '<?= ucwords(str_replace('_', ' ', $row['status'])) ?>',
                    '<?= ucfirst(htmlspecialchars($row['payment_method'])) ?>',
                    '<?= htmlspecialchars($row['shipping_address']) ?>',
                    '<?= htmlspecialchars($row['shipping_method']) ?>'
                  )">
                    <i class="fas fa-receipt me-1"></i>Struk
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal Struk -->
<div class="modal fade" id="modalStruk" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-receipt me-2"></i>Struk Pesanan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="strukDetail" class="struk-detail"></div>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-primary btn-print" onclick="printStruk()">
          <i class="fas fa-print me-1"></i>Print
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script>
function updateStatus(id, status) {
    if (!confirm(`Yakin ingin mengubah status menjadi "${status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())}"?`)) {
        return;
    }

    let formData = new FormData();
    formData.append('update_status', true);
    formData.append('id', id);
    formData.append('status', status);

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === "success") {
            let statusCell = document.querySelector(`#row-${id} .status-badge`);
            statusCell.className = `status-badge status-${status}`;
            statusCell.innerText = status.replace(/_/g, " ").replace(/\b\w/g, c => c.toUpperCase());
            // Show success alert
            const toast = new bootstrap.Toast(document.createElement('div'));
            toast._element.innerHTML = `
                <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                  <div class="toast-header">
                    <i class="fas fa-check-circle text-success me-2"></i>
                    <strong class="me-auto">Sukses</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                  </div>
                  <div class="toast-body">Status berhasil diubah!</div>
                </div>
            `;
            document.body.appendChild(toast._element);
            toast.show();
        } else {
            alert("Gagal mengubah status!\n" + data);
        }
    })
    .catch(err => alert("Error: " + err));
}

function cetakStruk(id, user, book, jumlah, date, status, payment, address, shipping) {
    let detail = `
        <p><strong>ID Order:</strong> ${id}</p>
        <p><strong>User:</strong> ${user}</p>
        <p><strong>Book ID:</strong> ${book}</p>
        <p><strong>Jumlah:</strong> ${jumlah}</p>
        <p><strong>Tanggal:</strong> ${date}</p>
        <p><strong>Status:</strong> ${status}</p>
        <p><strong>Metode Bayar:</strong> ${payment}</p>
        <p><strong>Alamat Pengiriman:</strong> ${address || '-'}</p>
        <p><strong>Metode Pengiriman:</strong> ${shipping || '-'}</p>
    `;
    document.getElementById("strukDetail").innerHTML = detail;
    new bootstrap.Modal(document.getElementById('modalStruk')).show();
}

function printStruk() {
    let detail = document.getElementById("strukDetail").innerHTML;
    let w = window.open("", "", "width=400,height=600");
    w.document.write(`
        <html>
        <head>
            <title>Struk Pesanan</title>
            <style>
                body { font-family: Arial, sans-serif; padding:20px; line-height:1.4; }
                h2 { text-align:center; margin-bottom:20px; }
                p { margin:5px 0; }
                .footer { margin-top:20px; text-align:center; font-size:12px; color:#666; }
            </style>
        </head>
        <body>
            <h2><i class="fas fa-receipt"></i> Struk Pesanan</h2>
            ${detail}
            <div class="footer">Terima kasih telah berbelanja 🙏</div>
        </body>
        </html>
    `);
    w.document.close();
    w.print();
}

function searchOrders() {
    let filter = document.getElementById("searchInput").value.toLowerCase();
    let rows = document.querySelectorAll("#orderTableBody tr");

    rows.forEach(row => {
        let text = '';
        row.querySelectorAll('td').forEach(cell => {
            text += cell.innerText.toLowerCase() + ' ';
        });
        row.style.display = text.includes(filter) ? "" : "none";
    });
}

// Real-time search on keyup
document.getElementById("searchInput").addEventListener("keyup", searchOrders);
</script>
</body>
</html>