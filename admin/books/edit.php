<?php
include '../../koneksi.php';

$id = (int) $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM books WHERE id = $id"));

// Ambil kategori dari DB
$kategori = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");

if (isset($_POST['submit'])) {
    $title       = $_POST['title'];
    $author      = $_POST['author'];
    $category    = (int) $_POST['category'];
    $description = $_POST['description'];
    $price       = $_POST['price'];
    $stock       = $_POST['stock'];

    if (!empty($_FILES['image']['name'])) {
        $image_name = time() . '_' . basename($_FILES['image']['name']);
        $target_path = "../../uploads/" . $image_name;
        move_uploaded_file($_FILES['image']['tmp_name'], $target_path);

        if (!empty($data['image']) && file_exists("../../uploads/" . $data['image'])) {
            unlink("../../uploads/" . $data['image']);
        }

        mysqli_query($conn, "UPDATE books SET 
            title='$title', author='$author', category_id=$category, description='$description',
            price=$price, stock=$stock, image='$image_name' WHERE id=$id");
    } else {
        mysqli_query($conn, "UPDATE books SET 
            title='$title', author='$author', category_id=$category, description='$description',
            price=$price, stock=$stock WHERE id=$id");
    }

    header("Location: ../books.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Buku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-warning"><h4>✏️ Edit Buku</h4></div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label>Judul</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($data['title']) ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Penulis</label>
                    <input type="text" name="author" value="<?= htmlspecialchars($data['author']) ?>" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Kategori</label>
                    <select name="category" class="form-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php while ($row = mysqli_fetch_assoc($kategori)): ?>
                            <option value="<?= $row['id'] ?>" <?= $data['category_id'] == $row['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Deskripsi</label>
                    <textarea name="description" class="form-control"><?= htmlspecialchars($data['description']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label>Harga</label>
                    <input type="number" name="price" value="<?= $data['price'] ?>" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Stok</label>
                    <input type="number" name="stock" value="<?= $data['stock'] ?>" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Foto Buku</label><br>
                    <?php if (!empty($data['image'])): ?>
                        <img src="../../uploads/<?= htmlspecialchars($data['image']) ?>" width="120" class="mb-2">
                    <?php endif; ?>
                    <input type="file" name="image" class="form-control">
                </div>

                <button type="submit" name="submit" class="btn btn-success">💾 Update</button>
                <a href="../books.php" class="btn btn-secondary">🔙 Kembali</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
