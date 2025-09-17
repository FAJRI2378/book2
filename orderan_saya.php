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
    body { font-family: Arial, sans-serif; padding:20px; }
    table { width:100%; border-collapse: collapse; margin-top:20px; }
    th, td { padding:10px; border:1px solid #ddd; text-align:center; }
    th { background:#f8f9fa; }
    .badge { padding:5px 10px; border-radius:5px; color:white; text-transform:capitalize; }
    .pending { background:orange; }
    .confirmed { background:green; }
    .cancelled { background:red; }
    .dalam_perjalanan { background:blue; }
    .sampai { background:teal; }

    .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); justify-content:center; align-items:center; }
    .modal-content { background:white; padding:20px; width:320px; border-radius:8px; text-align:center; }
    .modal-content h3 { margin-bottom:15px; }
    .close { float:right; cursor:pointer; font-size:20px; }
  </style>
</head>
<body>

<h2>📦 Pesanan Saya</h2>
<a href="index.php" class="btn btn-primary">← Kembali Belanja</a>

<!-- Bungkus tabel dalam div scroll -->
<div class="table-responsive" style="max-height: 450px; overflow-y: auto; margin-top:20px;">
<table class="table table-bordered table-striped align-middle text-center">
  <thead class="table-light">
    <tr>
      <th>Judul Buku</th>
      <th>Jumlah</th>
      <th>Tanggal</th>
      <th>Metode Pembayaran</th>
      <th>Alamat Pengiriman</th>
      <th>Metode Pengiriman</th>
      <th>Status</th>
      <th>Aksi</th>
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
        <td><?= $row['order_date'] ?></td>
        <td><?= htmlspecialchars($row['payment_method']) ?></td>
        <td><?= htmlspecialchars($row['shipping_address']) ?></td>
        <td><?= htmlspecialchars($row['shipping_method']) ?></td>
        <td><span class="badge <?= $row['status'] ?>"><?= $status_rapi ?></span></td>
        <td>
          <button class="btn btn-sm btn-primary"
                  onclick="cetakStruk(
                    '<?= $row['id'] ?>',
                    '<?= htmlspecialchars($row['title']) ?>',
                    '<?= $row['jumlah'] ?>',
                    '<?= $row['order_date'] ?>',
                    '<?= $status_rapi ?>',
                    '<?= htmlspecialchars($row['payment_method']) ?>',
                    '<?= htmlspecialchars($row['shipping_address']) ?>',
                    '<?= htmlspecialchars($row['shipping_method']) ?>'
                  )">
            🧾 Struk
          </button>
        </td>
      </tr>
    <?php endwhile; ?>
  <?php else: ?>
      <tr><td colspan="8">Belum ada pesanan.</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>


<!-- Modal Struk -->
<div class="modal" id="modalStruk">
  <div class="modal-content">
    <span class="close" onclick="document.getElementById('modalStruk').style.display='none'">&times;</span>
    <h3>Struk Pesanan</h3>
    <div id="strukDetail"></div>
    <br>
    <button class="btn btn-success" onclick="printStruk()">🖨 Cetak</button>
  </div>
</div>

<script>
function cetakStruk(id,title,jumlah,date,status,payment_method,shipping_address,shipping_method){
    let detail = `
        <p><b>ID Order:</b> ${id}</p>
        <p><b>Judul Buku:</b> ${title}</p>
        <p><b>Jumlah:</b> ${jumlah}</p>
        <p><b>Tanggal:</b> ${date}</p>
        <p><b>Metode Pembayaran:</b> ${payment_method}</p>
        <p><b>Alamat Pengiriman:</b> ${shipping_address}</p>
        <p><b>Metode Pengiriman:</b> ${shipping_method}</p>
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
                h2 { text-align:center; }
                p { margin:5px 0; }
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
}
</script>

</body>
</html>
