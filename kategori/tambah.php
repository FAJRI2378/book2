<?php
include '../koneksi.php';

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    mysqli_query($conn, "INSERT INTO categories (name) VALUES ('$name')");
    header("Location: index.php");
}
?>

<h2>Tambah Kategori</h2>
<form method="post">
    Nama Kategori: <input type="text" name="name" required>
    <br><br>
    <button type="submit" name="submit">Simpan</button>
</form>
