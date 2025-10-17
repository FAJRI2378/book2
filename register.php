<?php
session_start();
include 'koneksi.php';

$error = '';
$success = '';

// Buat CSRF token jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_POST['register'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Validasi token keamanan
    if (hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Validasi input dasar
        if (empty($username) || empty($email) || empty($password)) {
            $error = "Semua field harus diisi!";
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $error = "Username harus antara 3-50 karakter!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Format email tidak valid!";
        } elseif (strlen($password) < 6) {
            $error = "Password minimal 6 karakter!";
        } else {
            // Cek apakah username/email sudah terdaftar
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            if ($stmt) {
                $stmt->bind_param("ss", $email, $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $error = "Email atau username sudah terdaftar!";
                } else {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Simpan user baru ke database
                    $insert = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
                    $insert->bind_param("sss", $username, $email, $hashed_password);
                    if ($insert->execute()) {
                        $success = "Registrasi berhasil! Silakan login.";
                        $_POST = []; // bersihkan input
                    } else {
                        $error = "Gagal mendaftar: " . $insert->error;
                    }
                    $insert->close();
                }
                $stmt->close();
            } else {
                $error = "Terjadi kesalahan server.";
            }
        }
    } else {
        $error = "Token keamanan tidak valid!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - BookStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .register-header i {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 10px;
        }
        .register-header h1 {
            font-size: 1.8rem;
            color: #333;
        }
        .form-floating {
            margin-bottom: 20px;
        }
        .form-floating input {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .form-floating input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        }
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #6c757d;
            font-size: 1.2rem;
        }
        .btn-register {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.4);
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <i class="fas fa-user-plus"></i>
            <h1>Buat Akun Baru</h1>
            <p>Isi data di bawah ini untuk mendaftar</p>
        </div>

        <form method="post" id="registerForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="form-floating">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                <label for="username"><i class="fas fa-user me-1"></i>Username</label>
            </div>
            <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                <label for="email"><i class="fas fa-envelope me-1"></i>Email</label>
            </div>
            <div class="form-floating position-relative">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <label for="password"><i class="fas fa-lock me-1"></i>Password</label>
                <button type="button" class="password-toggle" onclick="togglePassword()">
                    <i class="fas fa-eye" id="toggleIcon"></i>
                </button>
            </div>
            <button type="submit" name="register" class="btn btn-register">
                <i class="fas fa-user-plus me-2"></i>Daftar
            </button>
        </form>

        <div class="login-link">
            Sudah punya akun? <a href="login.php"><i class="fas fa-sign-in-alt me-1"></i>Login Sekarang</a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById("password");
            const toggleIcon = document.getElementById("toggleIcon");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.className = "fas fa-eye-slash";
            } else {
                passwordField.type = "password";
                toggleIcon.className = "fas fa-eye";
            }
        }

        <?php if ($error): ?>
        Swal.fire({
            icon: 'error',
            title: 'Registrasi Gagal',
            text: '<?= htmlspecialchars($error) ?>',
            confirmButtonColor: '#dc3545'
        });
        <?php endif; ?>

        <?php if ($success): ?>
        Swal.fire({
            icon: 'success',
            title: 'Registrasi Berhasil',
            text: '<?= htmlspecialchars($success) ?>',
            confirmButtonColor: '#28a745'
        }).then(() => {
            window.location.href = 'login.php';
        });
        <?php endif; ?>
    </script>
</body>
</html>
