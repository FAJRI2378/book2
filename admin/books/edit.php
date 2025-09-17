<?php
include '../../koneksi.php';
$id = $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM books WHERE id = $id"));

if (isset($_POST['submit'])) {
    $title       = $_POST['title'];
    $author      = $_POST['author'];
    $description = $_POST['description'];
    $price       = $_POST['price'];
    $stock       = $_POST['stock'];

    // Cek apakah ada file gambar baru
    if (!empty($_FILES['image']['name'])) {
        $image_name = time() . '_' . basename($_FILES['image']['name']);
        $target_path = "../../uploads/" . $image_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            if (!empty($data['image']) && file_exists("../../uploads/" . $data['image'])) {
                unlink("../../uploads/" . $data['image']);
            }
            mysqli_query($conn, "UPDATE books SET 
                title='$title',
                author='$author',
                description='$description',
                price='$price',
                stock='$stock',
                image='$image_name'
                WHERE id = $id");
        } else {
            die("Gagal mengunggah gambar.");
        }
    } else {
        mysqli_query($conn, "UPDATE books SET 
            title='$title',
            author='$author',
            description='$description',
            price='$price',
            stock='$stock'
            WHERE id = $id");
    }

    header("Location: ../books.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Buku</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f7fa;
            padding: 40px;
        }
        .form-container {
            max-width: 500px;
            background: white;
            padding: 25px 30px;
            border-radius: 8px;
            margin: auto;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .form-container h2 {
            margin-bottom: 25px;
            text-align: center;
            color: #333;
        }
        label { display: block; margin-bottom: 5px; font-weight: 500; }
        input, textarea { width: 100%; padding: 8px 12px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; }
        button {
            width: 100%; padding: 10px; background-color: #0066cc; border: none; color: white;
            font-size: 16px; border-radius: 5px; cursor: pointer;
        }
        button:hover { background-color: #004da0; }
        .preview-img { display: block; max-width: 150px; margin-bottom: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Buku</h2>
        <form method="post" enctype="multipart/form-data">
            <label>Judul</label>
            <input type="text" name="title" value="<?= htmlspecialchars($data['title']) ?>" required>

            <label>Penulis</label>
            <input type="text" name="author" value="<?= htmlspecialchars($data['author']) ?>">

            <label>Deskripsi</label>
            <textarea name="description"><?= htmlspecialchars($data['description']) ?></textarea>

            <label>Harga</label>
            <input type="number" name="price" value="<?= $data['price'] ?>">

            <label>Stok</label>
            <input type="number" name="stock" value="<?= $data['stock'] ?>" required>

            <label>Foto Buku</label>
            <?php if (!empty($data['image'])): ?>
                <img src="../../uploads/<?= htmlspecialchars($data['image']) ?>" class="preview-img">
            <?php endif; ?>
            <input type="file" name="image" accept="image/*">

            <button type="submit" name="submit">Update</button>
        </form>
        <div class="back-link"><a href="../books.php">← Kembali ke Daftar Buku</a></div>
    </div>
</body>
</html>
