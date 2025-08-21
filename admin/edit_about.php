<?php
session_start();
include '../koneksi.php';

// cek apakah admin sudah login (opsional)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $about = mysqli_real_escape_string($conn, $_POST['about']);
    mysqli_query($conn, "UPDATE settings SET value='$about' WHERE name='about'");
    header("Location: books.php?success=1");
    exit;
}

$res = mysqli_query($conn, "SELECT value FROM settings WHERE name='about'");
$data = mysqli_fetch_assoc($res);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit About Us</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
  <h2>✏️ Edit About Us</h2>

  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Berhasil disimpan!</div>
  <?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Isi About Us</label>
      <textarea name="about" class="form-control" rows="6"><?= htmlspecialchars($data['value']) ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="index.php" class="btn btn-secondary">Kembali</a>
  </form>
</body>
</html>
