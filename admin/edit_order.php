<?php
include '../koneksi.php';

$id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM orders WHERE id=$id");
$order = mysqli_fetch_assoc($result);

if (!$order) {
    die("Order tidak ditemukan!");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Status Order</title>
</head>
<body>
    <h2>Edit Status Order #<?= $order['id']; ?></h2>
    <form method="POST" action="update_order.php">
        <input type="hidden" name="order_id" value="<?= $order['id']; ?>">

        <label>Status:</label>
        <select name="status">
            <option value="pending"   <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="confirmed" <?= $order['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
            <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>

        <button type="submit">Simpan</button>
    </form>
</body>
</html>
