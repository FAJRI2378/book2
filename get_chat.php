<?php
include 'koneksi.php';
$result = mysqli_query($conn, "SELECT * FROM chats ORDER BY created_at ASC");

while ($row = mysqli_fetch_assoc($result)) {
    echo "<div class='chat-msg'><span>{$row['sender']}:</span> {$row['message']} <small class='text-muted'>({$row['created_at']})</small></div>";
}
?>
