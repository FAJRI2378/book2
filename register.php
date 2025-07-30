<?php
include 'koneksi.php';

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, email, password, role) 
            VALUES ('$username', '$email', '$password', 'user')";
    mysqli_query($conn, $sql);

    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun</title>
    <style>
        body {
            background-color: #f3f4f6;
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .register-container {
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
            width: 380px;
        }

        .register-container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 24px;
        }

        .register-container input {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }

        .register-container button {
            width: 100%;
            padding: 12px;
            background-color: #184eccff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .register-container button:hover {
            background-color: #16a34a;
        }

        .register-container p {
            text-align: center;
            margin-top: 16px;
        }

        .register-container a {
            color: #2563eb;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h1>Daftar Akun</h1>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button name="register">Register</button>
        </form>
        <p>Sudah punya akun? <a href="login.php">Silahkan Login</a></p>
    </div>
</body>
</html>
