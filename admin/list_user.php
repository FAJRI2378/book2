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
            width: 80%;
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
    </style>
</head>
<body>
    <h2 align="center">List User Terdaftar</h2>
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
