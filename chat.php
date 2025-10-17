<?php
session_start();
include 'koneksi.php';

// ✅ Cek login user
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$user_id  = $_SESSION['user_id'];
$admin_id = 2;

// ✅ Tandai semua pesan admin → user sebagai sudah dibaca
mysqli_query($conn, "UPDATE chats 
                     SET is_read = 1 
                     WHERE receiver_id = $user_id AND sender_id = $admin_id");

// ✅ Ambil semua pesan
$messages = mysqli_query($conn, "
    SELECT * FROM chats
    WHERE (sender_id = $user_id AND receiver_id = $admin_id)
       OR (sender_id = $admin_id AND receiver_id = $user_id)
    ORDER BY created_at ASC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Chat Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#f8f9fa; padding:20px; font-family:sans-serif; }
    .chat-box { height:400px; overflow-y:auto; border:1px solid #ccc; border-radius:8px; padding:15px; background:white; scroll-behavior: smooth; }
    .bubble { padding:10px 15px; border-radius:15px; margin-bottom:10px; max-width:70%; position: relative; }
    .from-me { background:#d1e7dd; margin-left:auto; text-align:right; }
    .from-them { background:#e7f1ff; margin-right:auto; text-align:left; }
    .status { font-size:0.8rem; color:#666; }
    .reply-preview { background:#f1f1f1; border-left:3px solid #007bff; padding:6px 10px; margin-bottom:5px; font-size:0.9rem; border-radius:6px; color:#333; }
    .reply-btn { cursor:pointer; font-size:0.8rem; color:#007bff; text-decoration:underline; }
  </style>
</head>
<body class="container">
  <h4 class="mb-3">💬 Chat dengan Admin</h4>

  <div class="chat-box" id="chat-box">
    <?php while ($r = mysqli_fetch_assoc($messages)): ?>
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
  </div>

  <form id="chat-form" class="mt-3">
    <input type="hidden" name="reply_to" id="reply_to">
    <div id="reply-box" class="reply-preview d-none"></div>

    <div class="input-group">
      <input type="text" name="message" id="message" class="form-control" placeholder="Tulis pesan..." required>
      <button type="submit" class="btn btn-success">Kirim</button>
    </div>
  </form>

  <a href="index.php" class="btn btn-secondary mt-3">← Kembali</a>

  <script>
    const chatBox = document.getElementById('chat-box');
    const replyBox = document.getElementById('reply-box');
    const replyInput = document.getElementById('reply_to');
    const form = document.getElementById('chat-form');

    // Scroll otomatis ke bawah setiap 2 detik
    function scrollToBottom() {
      chatBox.scrollTop = chatBox.scrollHeight;
    }
    scrollToBottom();

    // Menampilkan pesan balasan
    function setReply(id, msg) {
      replyInput.value = id;
      replyBox.classList.remove('d-none');
      replyBox.innerHTML = '🔁 Balas: ' + msg + 
          ' <span class="text-danger ms-2" style="cursor:pointer;" onclick="cancelReply()">❌</span>';
      document.getElementById('message').focus();
    }

    // Batalkan balasan
    function cancelReply() {
      replyInput.value = '';
      replyBox.classList.add('d-none');
      replyBox.innerHTML = '';
    }

    // Kirim pesan via AJAX
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(form);
      fetch('send_message.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.text())
      .then(() => {
        document.getElementById('message').value = '';
        cancelReply();
        loadMessages();
      });
    });

    // Refresh chat tiap 2 detik
    function loadMessages() {
      fetch('load_messages.php')
        .then(res => res.text())
        .then(html => {
          chatBox.innerHTML = html;
          scrollToBottom();
        });
    }
    setInterval(loadMessages, 2000);
  </script>
</body>
</html>
