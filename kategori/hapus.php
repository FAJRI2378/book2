<?php
include '../koneksi.php';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id']; // pastikan integer

    // Cek apakah kategori dipakai di tabel books
    $check = mysqli_query($conn, "SELECT COUNT(*) as total FROM books WHERE category_id=$id");
    $row   = mysqli_fetch_assoc($check);

    if ($row['total'] > 0) {
        echo "<script>
            alert('❌ Kategori tidak bisa dihapus karena masih dipakai di data buku.');
            window.location.href='index.php';
        </script>";
        exit;
    }

    // Hapus kategori
    if (mysqli_query($conn, "DELETE FROM categories WHERE id=$id")) {
        header("Location: index.php?success=1");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    header("Location: index.php");
}
?>
