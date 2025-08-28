<?php
include '../koneksi.php';

$query = mysqli_query($conn, "SELECT * FROM users");
?>

<!DOCTYPE html>
<html>
<head>
    <title>List User Terdaftar</title>
    <style>
        table {
            border-collapse: collapse;
            width: 90%;
            margin: 20px auto;
        }
        th, td {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
        }
        a.btn {
            padding: 6px 10px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 14px;
        }
        .btn-struk {
            background-color: #28a745;
        }
        .btn-konfirmasi {
            background-color: #007bff;
        }
        .btn-konfirmasi.disabled {
            background-color: gray;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <h2 align="center">List User Terdaftar</h2>
     <a href="books.php" class="btn btn-primary" 
   style="background:#007bff; padding:8px 14px; border-radius:4px; text-decoration:none; color:white;">
   ← Kembali ke Daftar Buku
</a>
    <table>
        <tr>
            <th>No</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
        </tr>
        <?php
        $no = 1;
        while ($user = mysqli_fetch_assoc($query)) {
            echo "<tr>
                    <td>{$no}</td>
                    <td>{$user['username']}</td>
                    <td>{$user['email']}</td>
                    <td>{$user['role']}</td>
                  </tr>";
            $no++;
        }
        ?>
    </table>

   
</body>
</html>
