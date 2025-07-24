<?php
include '../../koneksi.php';
$result = mysqli_query($conn, "SELECT books.*, categories.name AS category FROM books JOIN categories ON books.category_id = categories.id");
?>

<h2>Daftar Buku</h2>
<a href="create.php">+ Tambah Buku</a><br><br>
<table border="1" cellpadding="8">
    <tr>
        <th>Judul</th>
        <th>Penulis</th>
        <th>Harga</th>
        <th>Kategori</th>
        <th>Gambar</th>
        <th>Aksi</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?= $row['title'] ?></td>
            <td><?= $row['author'] ?></td>
            <td>Rp<?= number_format($row['price']) ?></td>
            <td><?= $row['category'] ?></td>
            <td>
                <?php if (!empty($row['image'])) { ?>
                    <img src="../../uploads/<?= $row['image'] ?>" alt="Gambar Buku" width="100">
                <?php } else { ?>
                    <span>Tidak ada gambar</span>
                <?php } ?>
            </td>
            <td>
                <a href="edit.php?id=<?= $row['id'] ?>">Edit</a> |
                <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus?')">Hapus</a>
            </td>
        </tr>
    <?php } ?>
</table>

<p>Total Buku: <?= mysqli_num_rows($result) ?></p>
<p><a href="../../index.php">Kembali ke Dashboard</a></p>
