    <?php
    session_start();
    include '../koneksi.php';

    // Cek apakah yang login adalah admin
    if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
        header("Location: ../login.php");
        exit;
    }

    // Proses Tambah
    if (isset($_POST['tambah'])) {
        $name = $_POST['name'];
        $desc = $_POST['description'];
        mysqli_query($conn, "INSERT INTO categories (name, description) VALUES ('$name', '$desc')");
        header("Location: categories.php");
    }

    // Proses Hapus
    if (isset($_GET['hapus'])) {
        $id = $_GET['hapus'];
        mysqli_query($conn, "DELETE FROM categories WHERE id = $id");
        header("Location: categories.php");
    }

    // Proses Edit
    if (isset($_POST['update'])) {
        $id   = $_POST['id'];
        $name = $_POST['name'];
        $desc = $_POST['description'];
        mysqli_query($conn, "UPDATE categories SET name='$name', description='$desc' WHERE id=$id");
        header("Location: categories.php");
    }
    ?>

    <h2>Manajemen Kategori Buku</h2>

    <!-- Form Tambah Kategori -->
    <form method="post">
        <input type="text" name="name" placeholder="Nama Kategori" required><br>
        <textarea name="description" placeholder="Deskripsi Kategori"></textarea><br>
        <button name="tambah">Tambah</button>
    </form>

    <hr>

    <!-- List Kategori -->
    <table border ="1" cellpadding="5">
        <tr>
            <th>Nama</th>
            <th>Deskripsi</th>
            <th>Aksi</th>
        </tr>
        <?php
        $kategori = mysqli_query($conn, "SELECT * FROM categories");
        while ($row = mysqli_fetch_assoc($kategori)) :
        ?>
        <tr>
            <td><?= $row['name']; ?></td>
            <td><?= $row['description']; ?></td>
            <td>
                <!-- Tombol Edit -->
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
                    <input type="text" name="name" value="<?= $row['name']; ?>">
                    <input type="text" name="description" value="<?= $row['description']; ?>">
                    <button name="update">Update</button>
                </form>
                <!-- Tombol Hapus -->
                <a href="?hapus=<?= $row['id']; ?>" onclick="return confirm('Hapus kategori ini?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
