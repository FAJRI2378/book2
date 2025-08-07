<?php
include '../koneksi.php';
$id = $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM categories WHERE id=$id"));

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    mysqli_query($conn, "UPDATE categories SET name='$name' WHERE id=$id");
    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Kategori</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background-color: #f0f0f0;
        }
        .container {
            max-width: 500px;
            background: #fff;
            padding: 30px;
            margin: auto;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        input[type="text"], button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0069d9;
        }
        h2 {
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Kategori</h2>
    <form method="post">
        <label>Nama Kategori:</label>
        <input type="text" name="name" value="<?= $data['name'] ?>" required>
        <button type="submit" name="submit">Update</button>
    </form>
</div>

<div class="container" style="text-align: center; margin-top: 20px;">
        <a href="index.php" class="btn btn-primary">← Kembali </a>
    </div>

    
</body>
</html>
