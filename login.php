<?php
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $email    = $_POST['email'];
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    $user   = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role']    = $user['role'];

        if ($user['role'] == 'admin') {
            header("Location: admin/categories.php");
        } else {
            header("Location: index.php");
        }
    } else {
        echo "Login gagal!";
    }
}
?>

<h1>LOGIN</h1>
<form method="post">
    <input type="email" name="email" placeholder="Email"><br><br>
    <input type="password" name="password" placeholder="Password"><br><br>
    <button name="login">Login</button>
</form>
<p>Belum punya akun? <a href="register.php">Daftar Sekarang</a></p>

