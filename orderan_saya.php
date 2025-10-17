<?php
session_start();
include 'koneksi.php';

// pastikan user login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ambil pesanan milik user + join ke buku
$result = mysqli_query($conn, "SELECT o.*, b.title 
                               FROM orders o 
                               JOIN books b ON o.book_id = b.id 
                               WHERE o.user_id = '$user_id' 
                               ORDER BY o.order_date DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pesanan Saya</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { 
        font-family: 'Segoe UI', Tahoma, sans-serif; 
        background: #f8f9fa; 
    }
    h2 { 
        margin: 20px 0; 
        font-weight: bold; 
        color: #343a40; 
    }
    .badge { padding:6px 12px; font-size: 0.85rem; }
    .pending { background:#fd7e14; }
    .confirmed { background:#198754; }
    .cancelled { background:#dc3545; }
    .dalam_perjalanan { background:#0d6efd; }
    .sampai { background:#20c997; }

    /* Modal modern */
    .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; 
             background:rgba(0,0,0,0.6); justify-content:center; align-items:center; z-index:1050; }
    .modal-content { background:white; padding:25px; width:380px; border-radius:12px; box-shadow:0 5px 25px rgba(0,0,0,0.3);}
    .modal-content h3 { margin-bottom:20px; font-size:20px; color:#0d6efd; }
    .close { position:absolute; right:20px; top:15px; cursor:pointer; font-size:22px; color:#888; }
    .close:hover { color:#000; }
    #strukDetail p { margin:6px 0; font-size:15px; text-align:left; }

    /* Scroll tabel */
    .table-responsive { max-height: 450px; overflow-y:auto; margin-top:20px; }
  </style>
</head>
<body>

<div class="container">
    <h2>📦 Pesanan Saya</h2>
    <a href="index.php" class="btn btn-primary mb-3">← Kembali Belanja</a>

    <div class="table-responsive shadow-sm rounded bg-white p-2">
    <table class="table table-bordered table-hover align-middle text-center mb-0">
      <thead class="table-dark">
        <tr>
          <th>Judul Buku</th>
          <th>Jumlah</th>
          <th>Tanggal</th>
          <th>Pembayaran</th>
          <th>Alamat</th>
          <th>Pengiriman</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
      <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while($row = mysqli_fetch_assoc($result)): 
            $status_rapi = ucwords(str_replace("_"," ",$row['status']));
        ?>
          <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= $row['jumlah'] ?></td>
            <td><?= date('d-m-Y H:i', strtotime($row['order_date'])) ?></td>
            <td><?= htmlspecialchars($row['payment_method']) ?></td>
            <td><?= htmlspecialchars($row['shipping_address']) ?></td>
            <td><?= htmlspecialchars($row['shipping_method']) ?></td>
            <td><span class="badge <?= $row['status'] ?>"><?= $status_rapi ?></span></td>
            <td>
            
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
          <tr><td colspan="8" class="text-muted">Belum ada pesanan.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    </div>
</div>


<!-- Modal Struk -->
<!-- <div class="modal" id="modalStruk">
  <div class="modal-content position-relative">
    <span class="close" onclick="document.getElementById('modalStruk').style.display='none'">&times;</span>
    <h3>Struk Pesanan</h3>
    <div id="strukDetail"></div>
    <div class="mt-3 text-center">
        <button class="btn btn-success" onclick="printStruk()">🖨 Cetak</button>
    </div>
  </div>
</div>

<script>
function cetakStruk(id,title,jumlah,date,status,payment_method,shipping_address,shipping_method){
    let detail = `
        <p><b>ID Order:</b> ${id}</p>
        <p><b>Judul Buku:</b> ${title}</p>
        <p><b>Jumlah:</b> ${jumlah}</p>
        <p><b>Tanggal:</b> ${date}</p>
        <p><b>Pembayaran:</b> ${payment_method}</p>
        <p><b>Alamat:</b> ${shipping_address}</p>
        <p><b>Pengiriman:</b> ${shipping_method}</p>
        <p><b>Status:</b> ${status}</p>
    `;
    document.getElementById("strukDetail").innerHTML = detail;
    document.getElementById("modalStruk").style.display = "flex";
}

function printStruk(){
    let detail = document.getElementById("strukDetail").innerHTML;
    let w = window.open("", "", "width=400,height=600");
    w.document.write(`
        <html>
        <head>
            <title>Struk Pesanan</title>
            <style>
                body { font-family: Arial, sans-serif; padding:20px; }
                h2 { text-align:center; margin-bottom:20px; }
                p { margin:6px 0; }
                .footer { margin-top:20px; text-align:center; font-size:12px; }
            </style>
        </head>
        <body>
            <h2>📄 Struk Pesanan</h2>
            ${detail}
            <div class="footer">Terima kasih telah berbelanja 🙏</div>
        </body>
        </html>
    `);
    w.document.close();
    w.print();
} -->
</script>

</body>
</html>
