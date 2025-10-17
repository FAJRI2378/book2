<?php
session_start();
include 'koneksi.php';

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'user') exit;

$user_id  = $_SESSION['user_id'];
$admin_id = 2;

$q = mysqli_query($conn, "
    SELECT * FROM chats
    WHERE (sender_id = $user_id AND receiver_id = $admin_id)
       OR (sender_id = $admin_id AND receiver_id = $user_id)
    ORDER BY created_at ASC
");

while ($r = mysqli_fetch_assoc($q)): ?>
  <div class="d-flex <?= $r['sender_id']==$user_id?'justify-content-end':'justify-content-start' ?>">
    <div class="bubble <?= $r['sender_id']==$user_id?'from-me':'from-them' ?>">
      <?php if (!empty($r['reply_to'])):
          $reply = mysqli_fetch_assoc(mysqli_query($conn, "SELECT message FROM chats WHERE id=".(int)$r['reply_to']));
          if ($reply): ?>
          <div class="reply-preview"><strong>Balasan:</strong> <?= htmlspecialchars($reply['message']) ?></div>
      <?php endif; endif; ?>
      <?= htmlspecialchars($r['message']) ?><br>
      <small class="text-muted"><?= $r['created_at'] ?></small><br>
      <?php if ($r['sender_id']==$user_id): ?>
        <small class="status"><?= $r['is_read']?'✅ Sudah dibaca':'⏳ Belum dibaca' ?></small>
      <?php endif; ?>
      <div class="reply-btn" onclick="setReply(<?= $r['id'] ?>, '<?= htmlspecialchars(addslashes($r['message'])) ?>')">Balas</div>
    </div>
  </div>
<?php endwhile; ?>
