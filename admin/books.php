<?php
include '../koneksi.php';
include '../../book2/navbar.php';
$result = mysqli_query($conn, "SELECT books.*, categories.name AS category FROM books JOIN categories ON books.category_id = categories.id");
?>

<h2>Daftar Buku</h2>
<a href="books/create.php">+ Tambah Buku</a>
<table border="1" cellpadding="10">
    <tr>
        <th>Judul</th>
        <th>Penulis</th>
        <th>Kategori</th>
        <th>Harga</th>
        <th>Stok</th>
        <th>Gambar</th>
        <th>Aksi</th>
    </tr>
    <?php while ($book = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $book['title'] ?></td>
            <td><?= $book['author'] ?></td>
            <td><?= $book['category'] ?></td>
            <td><?= $book['price'] ?></td>
            <td><?= $book['stock'] ?></td>
            <td>
                <?php if (!empty($book['image'])): ?>
                    <img src="../uploads/<?= $book['image'] ?>" alt="<?= $book['title'] ?>" width="80">
                <?php else: ?>
                    <span>Tidak ada gambar</span>
                <?php endif; ?>
            <td>
 <a href="books/edit.php?id=<?= $book['id'] ?>">Edit</a>
  <a href="books/delete.php?id=<?= $book['id'] ?>" onclick="return confirm('Yakin hapus?')">Hapus</a>
</td>
        </tr>
    <?php endwhile ?>
</table>
