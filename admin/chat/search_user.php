<?php
session_start();
include '../../koneksi.php';

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') exit;

$admin_id = $_SESSION['user_id'];
$q = trim($_GET['q'] ?? '');

// Jika ada keyword, filter user
if ($q !== '') {
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE role='user' AND username LIKE CONCAT('%', ?, '%') ORDER BY username ASC");
    $stmt->bind_param('s', $q);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT id, username FROM users WHERE role='user' ORDER BY username ASC");
}

// Tampilkan hasil user
while ($u = $result->fetch_assoc()):
    $unread = $conn->query("
        SELECT COUNT(*) AS total 
        FROM chats 
        WHERE sender_id = {$u['id']} 
          AND receiver_id = $admin_id 
          AND is_read = 0
    ")->fetch_assoc()['total'];
?>
  <div class="user-item">
    <span class="username"><?= htmlspecialchars($u['username']) ?></span>
    <div>
      <?php if ($unread > 0): ?>
        <span class="badge bg-danger"><?= $unread ?> baru</span>
      <?php endif; ?>
      <a href="chat_room.php?user_id=<?= $u['id'] ?>" class="btn btn-sm btn-primary ms-2">Chat</a>
    </div>
  </div>
<?php endwhile; ?>

<?php if ($result->num_rows === 0): ?>
  <p class="text-muted text-center">Tidak ada user ditemukan.</p>
<?php endif; ?>
