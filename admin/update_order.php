<?php
include '../koneksi.php';

if (isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status   = $_POST['status'];

    if (in_array($status, ['pending', 'confirmed', 'cancelled'])) {
        $sql = "UPDATE orders SET status='$status' WHERE id=$order_id";
        if (mysqli_query($conn, $sql)) {
            echo "success";
            exit;
        }
    }
}
echo "error";
