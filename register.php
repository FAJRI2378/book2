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
            width: 75%;
            padding: 12px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 40px; /* ruang untuk ikon mata */
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 51%;
            transform: translateY(-50%);
            cursor: pointer;
            width: 24px;
            height: 24px;
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

            <div class="password-wrapper">
                <input type="password" id="password" name="password" placeholder="Password" required>
                <!-- Ikon mata -->
                <svg id="eyeIcon" class="toggle-password" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </div>

            <button name="register">Register</button>
        </form>
        <p>Sudah punya akun? <a href="login.php">Silahkan Login</a></p>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        let passwordVisible = false;

        eyeIcon.addEventListener('click', () => {
            passwordVisible = !passwordVisible;
            passwordInput.type = passwordVisible ? 'text' : 'password';
            
            // Ganti ikon mata
            if (passwordVisible) {
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.964 9.964 0 012.708-4.419m3.388-2.074A9.961 9.961 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.973 9.973 0 01-4.132 5.411M15 12a3 3 0 11-6 0 3 3 0 016 0z" />`;
            } else {
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
            }
        });
    </script>
</body>
</html>
