<?php
include '../koneksi.php';

$categories = mysqli_query($conn, "SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Kategori</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f4f4f4;
        }

        h2 {
            color: #333;
        }

        a {
            text-decoration: none;
            color: #007BFF;
            margin-right: 10px;
        }

        a:hover {
            text-decoration: underline;
        }

        .top-links {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .action-buttons a {
            color: #007BFF;
            margin-right: 10px;
        }

        .action-buttons a:hover {
            color: red;
        }
    </style>
</head>
<body>

    <h2>📚 Data Kategori Buku</h2>
    <div class="top-links">
        <a href="../admin/books.php">🔍 Lihat Semua Buku</a>
        <a href="tambah.php">➕ Tambah Kategori</a>
    </div>

    <table>
        <tr>
            <th>No</th>
            <th>Nama Kategori</th>
            <th>Aksi</th>
        </tr>
        <?php $no = 1; while ($row = mysqli_fetch_assoc($categories)) { ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td class="action-buttons">
                <a href="edit.php?id=<?= $row['id'] ?>">✏️ Edit</a>
                <a href="hapus.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus kategori ini?')">🗑️ Hapus</a>
            </td>
        </tr>
        <?php } ?>
    </table>
<div class="container" style="text-align: center; margin-top: 20px;">
        <a href="../admin/books.php" class="btn btn-primary">← Kembali </a>
    </div>

</body>
</html>
