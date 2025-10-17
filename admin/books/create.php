<?php
session_start();
include '../../koneksi.php';

// 🚫 Cegah user non-admin mengakses halaman ini
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('❌ Anda tidak memiliki akses ke halaman ini!'); window.location='../../index.php';</script>";
    exit;
}

// Ambil kategori dari tabel categories
$kategori = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");

$error = '';
if (isset($_POST['submit'])) {
    $title       = mysqli_real_escape_string($conn, $_POST['title']);
    $author      = mysqli_real_escape_string($conn, $_POST['author']);
    $category    = (int) $_POST['category']; 
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price       = (int) $_POST['price'];
    $stock       = (int) $_POST['stock'];

    if ($price < 5000) {
        $error = "❌ Harga minimal Rp5.000.";
    } else {
        $image_name = '';
        if (!empty($_FILES['image']['name'])) {
            $target_dir = "../../uploads/";
            $image_name = time() . '-' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image_name;
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
        }

        $query = "INSERT INTO books (title, author, category_id, description, price, stock, image)
                  VALUES ('$title', '$author', $category, '$description', $price, $stock, '$image_name')";
        if (mysqli_query($conn, $query)) {
            header("Location: ../books.php?success=1");
            exit;
        } else {
            $error = "❌ Gagal menyimpan data: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Buku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white"><h4>📚 Tambah Buku Baru</h4></div>
        <div class="card-body">

            <?php if ($error): ?>
                <div class="alert alert-danger text-center"><?= $error ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label>Judul Buku</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Penulis</label>
                    <input type="text" name="author" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Kategori</label>
                    <select name="category" class="form-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php while ($row = mysqli_fetch_assoc($kategori)): ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Deskripsi</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>

                <div class="mb-3">
                    <label>Harga</label>
                    <input type="number" name="price" min="5000" class="form-control" required>
                    <small class="text-muted">Harga minimal Rp5.000</small>
                </div>

                <div class="mb-3">
                    <label>Stok</label>
                    <input type="number" name="stock" min="1" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Upload Gambar</label>
                    <input type="file" name="image" class="form-control">
                </div>

                <button type="submit" name="submit" class="btn btn-success">💾 Simpan</button>
                <a href="../books.php" class="btn btn-secondary">🔙 Kembali</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
