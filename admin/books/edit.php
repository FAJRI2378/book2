<?php
include '../../koneksi.php';
$id = $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM books WHERE id = $id"));
$categories = mysqli_query($conn, "SELECT * FROM categories");

if (isset($_POST['submit'])) {
    $title       = $_POST['title'];
    $author      = $_POST['author'];
    $description = $_POST['description'];
    $price       = $_POST['price'];
    $stock       = $_POST['stock'];
    $category_id = $_POST['category_id'];

    mysqli_query($conn, "UPDATE books SET title='$title', author='$author', description='$description', price='$price', stock='$stock', category_id='$category_id' WHERE id = $id");

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
        label {
            display: block;
            margin-bottom: 5px;
            color: #444;
            font-weight: 500;
        }
        input[type="text"], input[type="number"], textarea, select {
            width: 100%;
            padding: 8px 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: vertical;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #0066cc;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #004da0;
        }
        .back-link {
            text-align: center;
            margin-top: 15px;
        }
        .back-link a {
            text-decoration: none;
            color: #0066cc;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Buku</h2>
        <form method="post">
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

            <label>Kategori</label>
            <select name="category_id">
                <?php while ($cat = mysqli_fetch_assoc($categories)) { ?>
                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $data['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php } ?>
            </select>

            <button type="submit" name="submit">Update</button>
        </form>

        <div class="back-link">
            <a href="../books.php">← Kembali ke Daftar Buku</a>
        </div>
    </div>
</body>
</html>
