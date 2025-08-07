<?php
include '../koneksi.php';

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    mysqli_query($conn, "INSERT INTO categories (name) VALUES ('$name')");
    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Kategori</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 500px;
            background: #fff;
            padding: 30px;
            margin: auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        input[type="text"], button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #28a745;
            color: white;
            font-weight: bold;
            cursor: pointer;
            border: none;
        }
        button:hover {
            background-color: #218838;
        }
        h2 {
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Tambah Kategori</h2>
    <form method="post">
        <label>Nama Kategori:</label>
        <input type="text" name="name" required>
        <button type="submit" name="submit">Simpan</button>
    </form>
</div>

<div class="container" style="text-align: center; margin-top: 20px;">
        <a href="index.php" class="btn btn-primary">← Kembali </a>
    </div>

</body>
</html>
