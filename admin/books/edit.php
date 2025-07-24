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
    $stock       = $_POST['stock']; // ⬅️ Tambahkan ini
    $category_id = $_POST['category_id'];

    mysqli_query($conn, "UPDATE books SET title='$title', author='$author', description='$description', price='$price', stock='$stock', category_id='$category_id' WHERE id = $id");

    header("Location: /book2/books.php");
exit;

}
?>

<h2>Edit Buku</h2>
<form method="post">
    Judul: <input type="text" name="title" value="<?= $data['title'] ?>" required><br><br>
    Penulis: <input type="text" name="author" value="<?= $data['author'] ?>"><br><br>
    Deskripsi:<br>
    <textarea name="description"><?= $data['description'] ?></textarea><br><br>
    Harga: <input type="number" name="price" value="<?= $data['price'] ?>"><br><br>
    
    <!-- ✅ Tambahkan input stok -->
    Stok: <input type="number" name="stock" value="<?= $data['stock'] ?>" required><br><br>

    Kategori:
    <select name="category_id">
        <?php while ($cat = mysqli_fetch_assoc($categories)) { ?>
            <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $data['category_id'] ? 'selected' : '' ?>>
                <?= $cat['name'] ?>
            </option>
        <?php } ?>
    </select><br><br>

    <button type="submit" name="submit" >Update</button>
</form>
<p><a href="index.php">Kembali ke Daftar Buku</a></p>
