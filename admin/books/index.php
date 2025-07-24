<?php
session_start();
include('../../koneksi.php');

$search = isset($_GET['search']) ? $_GET['search'] : '';

if ($search) {
    $query = "SELECT books.*, categories.name AS category 
              FROM books 
              JOIN categories ON books.category_id = categories.id 
              WHERE books.title LIKE '%$search%'";
} else {
    $query = "SELECT books.*, categories.name AS category 
              FROM books 
              JOIN categories ON books.category_id = categories.id";
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Management</title>
</head>
<body>
    <h1>Welcome to the BookStore</h1>

    <h5>Manage Kategori <a href="../../admin/categories.php">Klik Sini</a></h5>

    <form method="GET">
        <input type="text" name="search" placeholder="Cari buku..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Cari</button>
    </form>

    <h2>Daftar Buku</h2>
    <table border="1" cellpadding="8">
        <tr>
            <th>Judul</th>
            <th>Penulis</th>
            <th>Harga</th>
            <th>Kategori</th>
            <th>Gambar</th>
            
        </tr>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?= $row['title'] ?></td>
                    <td><?= $row['author'] ?></td>
                    <td>Rp<?= number_format($row['price']) ?></td>
                    <td><?= $row['category'] ?></td>
                    <td>
  <?php if (!empty($row['image'])): ?>
      <img src="../../uploads/<?= $row['image'] ?>" alt="<?= $row['title'] ?>" width="80">
  <?php else: ?>
      <span>Tidak ada gambar</span>
  <?php endif; ?>
</td>

                </tr>
            <?php } ?>
        <?php else: ?>
            <tr>
                <td colspan="5">Tidak ada buku ditemukan.</td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>
