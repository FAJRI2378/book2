<?php
session_start();

// Fungsi untuk cek role tertentu
function checkRole($requiredRole) {
    if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
        // Belum login
        header("Location: /book2/login.php");
        exit;
    }

    if ($_SESSION['role'] !== $requiredRole) {
        // Role tidak sesuai
        if ($_SESSION['role'] === 'admin') {
            header("Location: /book2/admin/books.php");
        } else {
            header("Location: /book2/user/index.php");
        }
        exit;
    }
}
?>
