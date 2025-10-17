<?php
session_start();
include '../../koneksi.php';

// Cek apakah admin sudah login
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];
$user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
if ($user_id === 0) die("User ID tidak valid.");

// Tandai pesan sudah dibaca
$update = $conn->prepare("UPDATE chats SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
if (!$update) {
    die("Prepare gagal: " . $conn->error);
}
$update->bind_param("ii", $user_id, $admin_id);
$update->execute();
$update->close();

// Ambil semua pesan
$stmt = $conn->prepare("
    SELECT c.*, r.message AS reply_message
    FROM chats c
    LEFT JOIN chats r ON c.reply_to = r.id
    WHERE (c.sender_id = ? AND c.receiver_id = ?)
       OR (c.sender_id = ? AND c.receiver_id = ?)
    ORDER BY c.created_at ASC
");
$stmt->bind_param("iiii", $admin_id, $user_id, $user_id, $admin_id);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Ambil nama user
$user = $conn->query("SELECT username FROM users WHERE id = $user_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Chat dengan <?= htmlspecialchars($user['username']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .chat-box {
      height: 400px;
      overflow-y: auto;
      border: 1px solid #ccc;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 8px;
      background: #f8f9fa;
    }
    .msg {
      padding: 10px 15px;
      border-radius: 12px;
      margin-bottom: 10px;
      max-width: 70%;
      position: relative;
    }
    .from-me { background: #d1e7dd; margin-left:auto; text-align:right; }
    .from-them { background: #e7f1ff; margin-right:auto; text-align:left; }
    .reply-box {
      font-size: 0.9rem;
      padding: 5px 10px;
      margin-bottom: 5px;
      border-left: 3px solid #0d6efd;
      background: rgba(13,110,253,0.05);
      border-radius: 6px;
      color: #0d6efd;
    }
    .reply-preview {
      border-left: 3px solid #0d6efd;
      padding: 5px 10px;
      background: #eef4ff;
      margin-bottom: 8px;
      border-radius: 6px;
      font-size: 0.9rem;
    }
    .reply-cancel {
      cursor:pointer;
      color:red;
      font-size:0.85rem;
    }
  </style>
</head>
<body class="container py-4">

<h4>💬 Chat dengan <?= htmlspecialchars($user['username']) ?></h4>
<div class="chat-box" id="chatBox">
  <?php foreach ($messages as $m): ?>
    <div class="msg <?= $m['sender_id'] == $admin_id ? 'from-me' : 'from-them' ?>" data-id="<?= $m['id'] ?>">
      <?php if (!empty($m['reply_message'])): ?>
        <div class="reply-box">
          <small>Balasan untuk:</small><br>
          <?= htmlspecialchars($m['reply_message']) ?>
        </div>
      <?php endif; ?>
      <?= htmlspecialchars($m['message']) ?><br>
      <small class="text-muted"><?= $m['created_at'] ?></small>
      <br>
      <a href="#" class="text-primary small reply-btn" data-id="<?= $m['id'] ?>" data-text="<?= htmlspecialchars($m['message']) ?>">Balas</a>
    </div>
  <?php endforeach; ?>
</div>

<!-- Form kirim -->
<div id="replyPreview" class="reply-preview d-none">
  <span id="replyText"></span>
  <span class="reply-cancel ms-2">(Batal)</span>
</div>

<form method="POST" id="chatForm">
  <input type="hidden" name="receiver_id" value="<?= $user_id ?>">
  <input type="hidden" name="reply_to" id="replyTo" value="">
  <div class="input-group">
    <input type="text" name="message" id="messageInput" class="form-control" placeholder="Tulis pesan..." required>
    <button type="submit" class="btn btn-primary">Kirim</button>
  </div>
</form>

<a href="index.php" class="btn btn-secondary mt-3">← Kembali</a>

<script>
const chatBox = document.getElementById('chatBox');
chatBox.scrollTop = chatBox.scrollHeight;

const replyPreview = document.getElementById('replyPreview');
const replyText = document.getElementById('replyText');
const replyToInput = document.getElementById('replyTo');

// Klik tombol balas
document.querySelectorAll('.reply-btn').forEach(btn => {
  btn.addEventListener('click', e => {
    e.preventDefault();
    const text = btn.dataset.text;
    const id = btn.dataset.id;
    replyPreview.classList.remove('d-none');
    replyText.textContent = text;
    replyToInput.value = id;
  });
});

// Batalkan reply
document.querySelector('.reply-cancel').addEventListener('click', () => {
  replyPreview.classList.add('d-none');
  replyToInput.value = '';
});

// Kirim pesan via fetch tanpa reload
document.getElementById('chatForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  fetch('send_message.php', { method:'POST', body:formData })
    .then(res => res.text())
    .then(() => {
      const msg = document.createElement('div');
      msg.className = 'msg from-me';
      msg.innerHTML = formData.get('reply_to') 
        ? `<div class='reply-box'><small>Balasan untuk:</small><br>${replyText.textContent}</div>` 
        : '';
      msg.innerHTML += formData.get('message') + '<br><small class="text-muted">baru saja</small>';
      chatBox.appendChild(msg);
      document.getElementById('messageInput').value = '';
      replyPreview.classList.add('d-none');
      replyToInput.value = '';
      chatBox.scrollTop = chatBox.scrollHeight;
    });
});
</script>
</body>
</html>
