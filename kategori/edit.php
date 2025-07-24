<?php
include '../koneksi.php';
$id = $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM categories WHERE id=$id"));

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    mysqli_query($conn, "UPDATE categories SET name='$name' WHERE id=$id");
    header("Location: index.php");
}
?>

<h2>Edit Kategori</h2>
<form method="post">
    Nama Kategori: <input type="text" name="name" value="<?= $data['name'] ?>" required>
    <br><br>
    <button type="submit" name="submit">Update</button>
</form>
