<?php
session_start();
include '../koneksi.php';
include '../../book2/navbar.php';

// Cek role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Proses Tambah
if (isset($_POST['tambah'])) {
    $name = $_POST['name'];
    mysqli_query($conn, "INSERT INTO categories (name) VALUES ('$name')");
    header("Location: categories.php");
    exit;
}

// Proses Hapus
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM categories WHERE id = $id");
    header("Location: categories.php");
    exit;
}

// Proses Edit
if (isset($_POST['update'])) {
    $id   = $_POST['id'];
    $name = $_POST['name'];
    mysqli_query($conn, "UPDATE categories SET name='$name' WHERE id=$id");
    header("Location: categories.php");
    exit;
}

// Proses Search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT * FROM categories";
if ($search) {
    $query .= " WHERE name LIKE '%$search%'";
}
$kategori = mysqli_query($conn, $query);
?>

<h2>Manajemen Kategori Buku</h2>

<h5>ingin melihat daftar buku? <a href="../admin/books.php">klik sini</a></h5>

<!-- Form Tambah Kategori -->
<form method="post" style="margin-bottom:20px;">
    <input type="text" name="name" placeholder="Nama Kategori" required>
    <button name="tambah">Tambah</button>
</form>

<!-- Form Pencarian -->
<form method="get" style="margin-bottom:20px;">
    <input type="text" name="search" placeholder="Cari kategori..." value="<?= htmlspecialchars($search); ?>">
    <button type="submit">Cari</button>
    <?php if ($search): ?>
        <a href="categories.php">Reset</a>
    <?php endif; ?>
</form>

<!-- List Kategori -->
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>No</th>
        <th>Nama Kategori</th>
        <th>Aksi</th>
    </tr>
    <?php $no = 1; while ($row = mysqli_fetch_assoc($kategori)) : ?>
    <tr>
        <td><?= $no++; ?></td>
        <td><?= $row['name']; ?></td>
        <td>
            <!-- Edit Form -->
            <form method="post" style="display:inline;">
                <input type="hidden" name="id" value="<?= $row['id']; ?>">
                <input type="text" name="name" value="<?= htmlspecialchars($row['name']); ?>" required>
                <button name="update">Update</button>
            </form>
            <!-- Tombol Hapus -->
            <a href="?hapus=<?= $row['id']; ?>" onclick="return confirm('Hapus kategori ini?')">Hapus</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
