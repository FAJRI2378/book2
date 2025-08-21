<?php
include '../koneksi.php';

// proses AJAX update status
if (isset($_POST['update_status'])) {
    $id     = mysqli_real_escape_string($conn, $_POST['id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $query = "UPDATE orders SET status = '$status' WHERE id = '$id'";
    if (mysqli_query($conn, $query)) {
        echo "success";
    } else {
        echo "error: " . mysqli_error($conn);
    }
    exit;
}

// ambil data orders + user info
$result = mysqli_query($conn, "
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.order_date DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pesanan</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { border-collapse: collapse; width: 95%; margin: 20px auto; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #f2f2f2; }
        button { padding: 6px 10px; border: none; border-radius: 4px; cursor: pointer; }
        .badge { padding: 4px 8px; border-radius: 4px; color: white; }
        .pending { background: orange; }
        .confirmed { background: green; }
        .cancelled { background: red; }

        .dropdown { position: relative; display: inline-block; }
        .dropbtn { background: none; border: none; font-size: 18px; cursor: pointer; }
        .dropdown-content { display: none; position: absolute; background: #fff; min-width: 120px; box-shadow: 0px 4px 6px rgba(0,0,0,0.2); z-index: 1; }
        .dropdown-content button { width: 100%; padding: 8px; text-align: left; border: none; background: white; cursor: pointer; }
        .dropdown-content button:hover { background: #f1f1f1; }
        .dropdown:hover .dropdown-content { display: block; }

        .back-btn { display: block; width: fit-content; margin: 20px auto; padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 6px; }
        .back-btn:hover { background: #0056b3; }

        /* Modal struk */
        .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); justify-content:center; align-items:center; }
        .modal-content { background:white; padding:20px; width:300px; border-radius:8px; text-align:center; }
        .modal-content h3 { margin-bottom:15px; }
        .close { float:right; cursor:pointer; font-size:20px; }
    </style>
</head>
<body>

<h2 style="text-align:center;">📦 Kelola Pesanan</h2>

<table>
    <tr>
        <th>No</th>
        <th>ID Order</th>
        <th>User</th>
        <th>Book ID</th>
        <th>Jumlah</th>
        <th>Waktu Order</th>
        <th>Metode Bayar</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>
    <?php
     $no = 1;
    while($row = mysqli_fetch_assoc($result)) : ?>
        <tr id="row-<?php echo $row['id']; ?>">
            <td><?php echo $no++; ?></td>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['username']); ?></td>
            <td><?php echo $row['book_id']; ?></td>
            <td><?php echo $row['jumlah']; ?></td>
            <td><?php echo $row['order_date']; ?></td>
            <td><?php echo $row['payment_method']; ?></td>
            <td class="status">
                <span class="badge <?php echo $row['status']; ?>">
                    <?php echo $row['status']; ?>
                </span>
                <div class="dropdown">
                    <button class="dropbtn">⋮</button>
                    <div class="dropdown-content">
                        <button onclick="updateStatus(<?php echo $row['id']; ?>, 'pending')">Pending</button>
                        <button onclick="updateStatus(<?php echo $row['id']; ?>, 'confirmed')">Confirmed</button>
                        <button onclick="updateStatus(<?php echo $row['id']; ?>, 'cancelled')">Cancelled</button>
                    </div>
                </div>
            </td>
            <td>
                <button onclick="cetakStruk(
                    '<?php echo $row['id']; ?>',
                    '<?php echo $row['username']; ?>',
                    '<?php echo $row['book_id']; ?>',
                    '<?php echo $row['jumlah']; ?>',
                    '<?php echo $row['order_date']; ?>',
                    '<?php echo $row['status']; ?>',
                    '<?php echo $row['payment_method']; ?>'
                )">🧾 Struk</button>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<a href="books.php" class="back-btn">← Kembali ke Daftar Buku</a>

<!-- Modal Struk -->
<div class="modal" id="modalStruk">
    <div class="modal-content" id="strukContent">
        <span class="close" onclick="document.getElementById('modalStruk').style.display='none'">&times;</span>
        <h3>Struk Pesanan</h3>
        <div id="strukDetail"></div>
        <br>
        <button onclick="printStruk()">🖨 Print</button>
    </div>
</div>

<script>
function updateStatus(id, status) {
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
            let statusCell = document.querySelector(`#row-${id} .status`);
            statusCell.querySelector("span").className = "badge " + status;
            statusCell.querySelector("span").innerText = status;
            alert("Status berhasil diubah menjadi " + status);
        } else {
            alert("Gagal mengubah status!\n" + data);
        }
    })
    .catch(err => alert("Error: " + err));
}

function cetakStruk(id,user,book,jumlah,date,status,payment){
    let detail = `
        <p><b>ID Order:</b> ${id}</p>
        <p><b>User:</b> ${user}</p>
        <p><b>Book ID:</b> ${book}</p>
        <p><b>Jumlah:</b> ${jumlah}</p>
        <p><b>Tanggal:</b> ${date}</p>
        <p><b>Metode Bayar:</b> ${payment}</p>
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
