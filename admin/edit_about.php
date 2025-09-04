<?php
session_start();
include '../koneksi.php';

// ambil data about us dari settings
$res = mysqli_query($conn, "SELECT * FROM settings WHERE name='about'");
$data = mysqli_fetch_assoc($res);

// kalau belum ada record, buat dulu
if (!$data) {
    mysqli_query($conn, "INSERT INTO settings (name, value) VALUES ('about', '')");
    $res = mysqli_query($conn, "SELECT * FROM settings WHERE name='about'");
    $data = mysqli_fetch_assoc($res);
}

// proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $about = mysqli_real_escape_string($conn, $_POST['about']);

    // upload foto jika ada
    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . basename($_FILES['image']['name']);
        $target = "../uploads/" . $imageName;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);

        mysqli_query($conn, "UPDATE settings SET value='$about', image='$imageName' WHERE name='about'");
    } else {
        mysqli_query($conn, "UPDATE settings SET value='$about' WHERE name='about'");
    }

    header("Location: books.php?success=1");
    exit;
}
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

  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">Isi About Us</label>
      <textarea name="about" class="form-control" rows="6"><?= htmlspecialchars($data['value'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Foto About Us</label><br>
      <?php if (!empty($data['image'])): ?>
        <img src="../uploads/<?= htmlspecialchars($data['image']) ?>" alt="About" style="max-width:200px;border-radius:8px;margin-bottom:10px;"><br>
      <?php endif; ?>
      <input type="file" name="image" class="form-control">
      <small class="text-muted">Kosongkan jika tidak ingin mengganti foto.</small>
    </div>

    <button type="submit" class="btn btn-primary">💾 Simpan</button>
    <a href="books.php" class="btn btn-secondary">⬅️ Kembali</a>
  </form>
</body>
</html>
