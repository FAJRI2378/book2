<?php
include '../../../koneksi.php';

$id = $_GET['id'];

// Ambil nama file gambar terlebih dahulu
$result = mysqli_query($conn, "SELECT image FROM books WHERE id = $id");
$row = mysqli_fetch_assoc($result);
$image = $row['image'];

// Hapus file gambar dari folder uploads
if ($image && file_exists("../../../assets/uploads/" . $image)) {
    unlink("../../../assets/uploads/" . $image);
}

// Hapus data dari database
mysqli_query($conn, "DELETE FROM books WHERE id = $id");

// Redirect kembali
header("Location: index.php");
exit;
