<?php
include '../../koneksi.php';

// Ambil data kategori dari database
$categories = mysqli_query($conn, "SELECT * FROM categories");

if (isset($_POST['submit'])) {
    $title       = mysqli_real_escape_string($conn, $_POST['title']);
    $author      = mysqli_real_escape_string($conn, $_POST['author']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price       = (int)$_POST['price'];
    $stock       = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];

    // Proses Upload Gambar
    $image_name = '';
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../../uploads/";
        $image_name = time() . '-' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Gambar berhasil diupload
        } else {
            echo "Gagal upload gambar.";
            exit;
        }
    }

    // Simpan data buku ke database
    $query = "INSERT INTO books (title, author, description, price, stock, category_id, image)
              VALUES ('$title', '$author', '$description', $price, $stock, $category_id, '$image_name')";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit;
    } else {
        echo "Gagal menyimpan data buku: " . mysqli_error($conn);
    }
}
?>

<h2>Tambah Buku</h2>
<form method="post" enctype="multipart/form-data">
    Judul: <input type="text" name="title" required><br><br>
    Penulis: <input type="text" name="author"><br><br>
    Deskripsi:<br>
    <textarea name="description"></textarea><br><br>
    Harga: <input type="number" name="price" required><br><br>
    Stok: <input type="number" name="stock" required><br><br>
    Kategori:
    <select name="category_id" required>
        <?php while ($cat = mysqli_fetch_assoc($categories)) { ?>
            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php } ?>
    </select><br><br>
    Gambar: <input type="file" name="image" accept="image/*"><br><br>
    <button type="submit" name="submit">Simpan</button>
</form>
