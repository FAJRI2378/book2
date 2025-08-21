<?php
include '../../koneksi.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // cek stok dulu
    $cek = mysqli_query($conn, "SELECT stock, image FROM books WHERE id = $id");
    $data = mysqli_fetch_assoc($cek);

    if ($data) {
        if ($data['stock'] > 0) {
            // kalau stok masih ada, jangan hapus
            echo "<script>
                alert('❌ Buku tidak bisa dihapus karena stok masih ada ($data[stock]). Silakan habiskan stok dulu.');
                window.location.href='../books.php';
            </script>";
            exit;
        } else {
            // hapus file gambar kalau ada
            if (!empty($data['image']) && file_exists("../../../assets/uploads/" . $data['image'])) {
                unlink("../../../assets/uploads/" . $data['image']);
            }

            // hapus data buku dari database
            $query = mysqli_query($conn, "DELETE FROM books WHERE id = $id");

            if ($query) {
                echo "<script>
                    alert('✅ Buku berhasil dihapus.');
                    window.location.href='../books.php';
                </script>";
            } else {
                echo "<script>
                    alert('❌ Gagal menghapus buku.');
                    window.location.href='../books.php';
                </script>";
            }
        }
    } else {
        echo "<script>
            alert('❌ Buku tidak ditemukan.');
            window.location.href='../books.php';
        </script>";
    }
} else {
    header("Location: ../books.php");
    exit;
}
