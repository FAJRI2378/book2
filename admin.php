<?php
// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "bookstore");

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Data admin
$username = "admin";
$email = "admin@example.com";
$password = password_hash("admin123", PASSWORD_DEFAULT); // Enkripsi password
$role = "admin";

// Masukkan ke database
$sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $username, $email, $password, $role);

if ($stmt->execute()) {
    echo "Akun admin berhasil dibuat.";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
