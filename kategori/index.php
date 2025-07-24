<?php
include '../koneksi.php';

$categories = mysqli_query($conn, "SELECT * FROM categories");
?>

<h2>Data Kategori</h2>
<h5>MELIHAT SEMUA BUKU <a href="../admin/books/index.php">KILIK SINI</a></h5>
<a href="tambah.php">+ Tambah Kategori</a><br><br>
<table border="1" cellpadding="10">
    <tr>
        <th>No</th>
        <th>Nama Kategori</th>
        <th>Aksi</th>
    </tr>
    <?php $no = 1; while ($row = mysqli_fetch_assoc($categories)) { ?>
    <tr>
        <td><?= $no++ ?></td>
        <td><?= $row['name'] ?></td>
        <td>
            <a href="edit.php?id=<?= $row['id'] ?>">Edit</a> |
            <a href="hapus.php?id=<?= $row['id'] ?>" onclick="return confirm('Hapus kategori ini?')">Hapus</a>
        </td>
    </tr>
    <?php } ?>
</table>
