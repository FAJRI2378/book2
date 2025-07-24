<?php
include '../koneksi.php';
$result = mysqli_query($conn, "SELECT books.*, categories.name AS category FROM books JOIN categories ON books.category_id = categories.id");
?>

<h2>Daftar Buku</h2>
<a href="add_book.php">+ Tambah Buku</a>
<table border="1" cellpadding="10">
    <tr>
        <th>Judul</th>
        <th>Penulis</th>
        <th>Kategori</th>
        <th>Harga</th>
        <th>Stok</th>
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
                <a href="edit_book.php?id=<?= $book['id'] ?>">Edit</a> |
                <a href="delete_book.php?id=<?= $book['id'] ?>" onclick="return confirm('Yakin hapus?')">Hapus</a>
            </td>
        </tr>
    <?php endwhile ?>
</table>
