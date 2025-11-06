<?php
session_start();
include 'koneksi.php';

// Validasi session
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Gunakan prepared statement untuk keamanan
$stmt = $conn->prepare("
    SELECT b.*, w.created_at
    FROM wishlist w 
    JOIN books b ON w.book_id = b.id 
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card-img-top {
            height: 250px;
            object-fit: cover;
        }
        .remove-wishlist {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
        }
        .card {
            position: relative;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-state i {
            font-size: 80px;
            color: #ddd;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="bi bi-heart-fill text-danger"></i> Daftar Wishlist</h3>
            <span class="badge bg-primary"><?= $result->num_rows ?> Item</span>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="row">
            <?php while ($b = $result->fetch_assoc()): ?>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card h-100">
                        <!-- Tombol hapus wishlist -->
                        <button class="btn btn-danger btn-sm remove-wishlist" 
                                onclick="removeFromWishlist(<?= $b['id'] ?>)"
                                title="Hapus dari wishlist">
                            <i class="bi bi-x-lg"></i>
                        </button>

                        <img src="uploads/<?= htmlspecialchars($b['image']) ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($b['title']) ?>"
                             onerror="this.src='uploads/default-book.jpg'">
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($b['title']) ?></h5>
                            
                            <?php if (!empty($b['author'])): ?>
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-person"></i> <?= htmlspecialchars($b['author']) ?>
                                </p>
                            <?php endif; ?>

                            <p class="text-primary fw-bold mb-3">
                                Rp <?= number_format($b['price'], 0, ',', '.') ?>
                            </p>

                            <div class="mt-auto">
                                <a href="book_detail.php?id=<?= $b['id'] ?>" 
                                   class="btn btn-outline-primary btn-sm w-100 mb-2">
                                    <i class="bi bi-eye"></i> Lihat Detail
                                </a>
                                <button class="btn btn-primary btn-sm w-100" 
                                        onclick="addToCart(<?= $b['id'] ?>)">
                                    <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-heart"></i>
                <h4 class="mt-3">Wishlist Kosong</h4>
                <p class="text-muted">Anda belum menambahkan buku ke wishlist</p>
                <a href="index.php" class="btn btn-primary mt-3">
                    <i class="bi bi-book"></i> Jelajahi Buku
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function removeFromWishlist(bookId) {
            if (!confirm('Hapus buku dari wishlist?')) return;

            fetch('remove_wishlist.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `book_id=${bookId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Gagal menghapus dari wishlist');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            });
        }

        function addToCart(bookId) {
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `book_id=${bookId}&quantity=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Berhasil ditambahkan ke keranjang!');
                } else {
                    alert(data.message || 'Gagal menambahkan ke keranjang');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            });
        }
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>