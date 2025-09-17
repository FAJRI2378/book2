<?php
include '../../koneksi.php';

if (isset($_POST['submit'])) {
    $title       = mysqli_real_escape_string($conn, $_POST['title']);
    $author      = mysqli_real_escape_string($conn, $_POST['author']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price       = (int)$_POST['price'];
    $stock       = (int)$_POST['stock'];

    // ✅ Validasi harga minimal Rp5.000
    if ($price < 5000) {
        echo "<div class='alert alert-danger text-center'>❌ Harga produk minimal Rp5.000.</div>";
    } else {
        // Proses Upload Gambar
        $image_name = '';
        if (!empty($_FILES['image']['name'])) {
            $target_dir = "../../uploads/";
            $image_name = time() . '-' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image_name;

            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                echo "<div class='alert alert-danger'>Gagal upload gambar.</div>";
                exit;
            }
        }

        // Simpan data buku ke database
        $query = "INSERT INTO books (title, author, description, price, stock, image)
                  VALUES ('$title', '$author', '$description', $price, $stock, '$image_name')";

        if (mysqli_query($conn, $query)) {
            header("Location: ../books.php?success=1");
            exit;
        } else {
            echo "<div class='alert alert-danger'>Gagal menyimpan data buku: " . mysqli_error($conn) . "</div>";
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
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">📚 Tambah Buku Baru</h4>
        </div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Judul Buku</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Penulis</label>
                    <input type="text" name="author" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="4"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Harga (Rp)</label>
                    <input type="number" name="price" class="form-control" required min="5000">
                    <small class="text-muted">Harga minimal Rp5.000</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Stok</label>
                    <input type="number" name="stock" class="form-control" required min="1">
                </div>

                <div class="mb-3">
                    <label class="form-label">Upload Gambar</label>
                    <input type="file" name="image" accept="image/*" class="form-control">
                </div>

                <div class="text-end">
                    <button type="submit" name="submit" class="btn btn-success">💾 Simpan</button>
                    <a href="../books.php" class="btn btn-secondary">🔙 Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
