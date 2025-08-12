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
            header("Location: admin/books.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $error = "Email atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            margin: 0;
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;               
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .login-container {
            background: white;
            padding: 40px 30px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            width: 350px;
        }
        .login-container h1 {
            text-align: center;
            margin-bottom: 24px;
            font-size: 24px;
            color: #1f2937;
        }
        .input-group {
            position: relative;
            margin-bottom: 16px;
        }
        .login-container input {
            width: 85%;
            padding: 12px;
            padding-right: 40px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s ease;
        }
        .login-container input:focus {
            border-color: #2563eb;
        }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            color: #2563eb;
            padding: 0;
        }
        .login-container button[name="login"] {
            width: 100%;
            padding: 12px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .login-container button[name="login"]:hover {
            background: #1d4ed8;
        }
        .login-container p {
            text-align: center;
            margin-top: 16px;
            font-size: 14px;
        }
        .login-container a {
            color: #2563eb;
            text-decoration: none;
        }
        .login-container a:hover {
            text-decoration: underline;
        }
        .error {
            background: rgba(255, 0, 0, 0.08);
            color: #dc2626;
            padding: 8px;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 16px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="email" name="email" placeholder="Email" required>
            <div class="input-group">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <button type="button" class="toggle-password" onclick="togglePassword()">👁</button>
            </div>
            <button name="login">Masuk</button>
        </form>
        <p>Belum punya akun? <a href="register.php">Daftar Sekarang</a></p>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById("password");
            if (passwordField.type === "password") {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        }
    </script>
</body>
</html>
