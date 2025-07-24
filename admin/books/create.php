<?php
include '../../koneksi.php';

$categories = mysqli_query($conn, "SELECT * FROM categories");

if (isset($_POST['submit'])) {
    $title       = $_POST['title'];
    $author      = $_POST['author'];
    $description = $_POST['description'];
    $price       = $_POST['price'];
    $category_id = $_POST['category_id'];

    // Proses Upload Gambar
    $image_name = '';
    if ($_FILES['image']['name'] != '') {
        $target_dir = "../../uploads/";
        $image_name = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    }

    // Simpan data buku termasuk nama file gambar
    mysqli_query($conn, "INSERT INTO books (title, author, description, price, category_id, image) VALUES ('$title', '$author', '$description', '$price', '$category_id', '$image_name')");
    
    header("Location: index.php");
}
?>

<h2>Tambah Buku</h2>
<form method="post" enctype="multipart/form-data">
    Judul: <input type="text" name="title" required><br><br>
    Penulis: <input type="text" name="author"><br><br>
    Deskripsi:<br>
    <textarea name="description"></textarea><br><br>
    Harga: <input type="number" name="price"><br><br>
    Kategori:
    <select name="category_id">
        <?php while ($cat = mysqli_fetch_assoc($categories)) { ?>
            <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
        <?php } ?>
    </select><br><br>
    Gambar: <input type="file" name="image"><br><br>
    <button type="submit" name="submit">Simpan</button>
</form>
