<?php
require_once 'koneksi.php'; // Koneksi ke database

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['keranjang']) || !is_array($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Fungsi untuk mendapatkan data menu berdasarkan kode_menu
function getMenuById($conn, $kode_menu) {
    $stmt = $conn->prepare("SELECT * FROM menu WHERE kode_menu = ?");
    $stmt->bind_param("s", $kode_menu);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Tambah ke Keranjang
if (isset($_POST['tambah_keranjang'])) {
    $kode_menu = $_POST['kode_menu'];
    $qty = max(1, intval($_POST['qty'])); // Pastikan minimal qty adalah 1

    $menuData = getMenuById($conn, $kode_menu);
    
    if ($menuData) {
        if (isset($_SESSION['keranjang'][$kode_menu])) {
            $_SESSION['keranjang'][$kode_menu]['quantity'] += $qty;
        } else {
            $_SESSION['keranjang'][$kode_menu] = [
                'name' => $menuData['nama'],
                'price' => $menuData['harga'],
                'quantity' => $qty,
                'image' => $menuData['gambar']
            ];
        }
    }
}

// Hapus item dari keranjang
if (isset($_GET['hapus_keranjang'])) {
    $kode_menu = $_GET['hapus_keranjang'];
    unset($_SESSION['keranjang'][$kode_menu]);
    header("Location: index.php");
    exit();
}

// Update jumlah item di keranjang
if (isset($_POST['update_keranjang'])) {
    foreach ($_POST['qty'] as $kode_menu => $qty) {
        if ($qty > 0) {
            $_SESSION['keranjang'][$kode_menu]['quantity'] = $qty;
        } else {
            unset($_SESSION['keranjang'][$kode_menu]);
        }
    }
    header("Location: index.php");
    exit();
}

// Hapus menu dari database
if (isset($_GET['hapus_menu'])) {
    $kode_menu = $_GET['hapus_menu'];
    $stmt = $conn->prepare("DELETE FROM menu WHERE kode_menu = ?");
    $stmt->bind_param("s", $kode_menu);
    $stmt->execute();
    header("Location: index.php");
    exit();
}

// Edit menu
if (isset($_POST['edit_menu'])) {
    $kode_menu = $_POST['kode_menu'];
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $kategori = $_POST['kategori'];
    $status = $_POST['status'];

    // Cek apakah ada gambar baru yang diunggah
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "src/img/";
        $target_file = $target_dir . basename($_FILES["gambar"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validasi tipe file gambar
        $valid_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $valid_extensions)) {
            // Pindahkan file gambar ke direktori yang ditentukan
            move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file);
            $gambar = basename($_FILES["gambar"]["name"]);
        } else {
            echo "<script>alert('Format gambar tidak valid!');</script>";
            exit();
        }
    } else {
        // Jika tidak ada gambar baru, gunakan gambar lama
        $gambar = $_POST['gambar_lama'];
    }

    $stmt = $conn->prepare("UPDATE menu SET nama = ?, harga = ?, kategori = ?, status = ?, gambar = ? WHERE kode_menu = ?");
    $stmt->bind_param("sdssss", $nama, $harga, $kategori, $status, $gambar, $kode_menu);
    $stmt->execute();
    header("Location: index.php");
    exit();
}
?>

<!-- Form Pemesanan -->
<form action="index.php" method="POST">
    <div class="d-flex">
        <input class="form-control mx-sm-2 my- 2 w-auto" type="text" name="pelanggan" placeholder="Nama Pelanggan" required autocomplete="off">
        <button class="btn btn-success my-2 mx-2" name="pesan">Pesan</button>
    </div>
</form>

<!-- Menu Masakan -->
<div class="row">
    <?php foreach ($menu as $m) { ?>
        <div class="col-md-3 d-flex align-items-stretch mb-4">
            <div class="card h-100 w-100" style="background-color: #d8c4a0; border: 1px solid #8b5a2b;">
                <h5 class="card-header" style="background-color: #8b5a2b; color: white;">
                    <?= htmlspecialchars($m["nama"]); ?>
                </h5>
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <p class="text-center">
                            <img class="rounded img-fluid" src="src/img/<?= htmlspecialchars($m["gambar"]); ?>" style="max-height: 150px; object-fit: cover;">
                        </p>
                        <p>Harga: Rp<?= number_format($m["harga"], 0, ',', '.'); ?></p>
                        <p>Kategori: <?= htmlspecialchars($m["kategori"]); ?></p>
                        <p>Status: <?= htmlspecialchars($m["status"]); ?></p>
                    </div>
                    
                    <div>
                        <?php if (isset($_SESSION["akun-admin"])): ?>
                            <!-- Tombol Edit & Hapus hanya untuk admin -->
                            <button class="btn btn-warning mt-2" data-toggle="modal" data-target="#editModal<?= htmlspecialchars($m["kode_menu"]); ?>">Edit Menu</button>
                            <button class="btn btn-danger mt-2" data-toggle="modal" data-target="#hapusModal<?= htmlspecialchars($m["kode_menu"]); ?>">Hapus Menu</button>
                        <?php endif; ?>

                        <!-- Form Tambah ke Keranjang (tersedia untuk semua user) -->
                        <form method="POST" action="">
                            <input type="hidden" name="kode_menu" value="<?= htmlspecialchars($m["kode_menu"]); ?>">
                            <input type="number" name="qty" min="1" value="1" class="form-control mt-2">
                            <button type="submit" name="tambah_keranjang" class="btn btn-primary mt-2 w-100">
                                <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION["akun-admin"])): ?>
            <!-- Modal Edit Menu (hanya untuk admin) -->
            <div class="modal fade" id="editModal<?= htmlspecialchars($m["kode_menu"]); ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: #8b5a2b; color: white;">
                            <h5 class="modal-title" id="editModalLabel">Edit Menu</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="modal-body">
                                <input type="hidden" name="kode_menu" value="<?= htmlspecialchars($m["kode_menu"]); ?>">
                                <input type="hidden" name="gambar_lama" value="<?= htmlspecialchars($m["gambar"]); ?>">
                                <div class="form-group">
                                    <label for="nama">Nama</label>
                                    <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($m["nama"]); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="harga">Harga</label>
                                    <input type="number" class="form-control" name="harga" value="<?= htmlspecialchars($m["harga"]); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="kategori">Kategori</label>
                                    <input type="text" class="form-control" name="kategori" value="<?= htmlspecialchars($m["kategori"]); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select name="status" class="form-control" required>
                                        <option value="Tersedia" <?= $m["status"] == "Tersedia" ? 'selected' : ''; ?>>Tersedia</option>
                                        <option value="Tidak Tersedia" <?= $m["status"] == "Tidak Tersedia" ? 'selected' : ''; ?>>Tidak Tersedia</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="gambar"> Gambar Baru</label>
                                    <input type="file" class="form-control" name="gambar" accept="image/*">
                                    <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah gambar.</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                <button type="submit" name="edit_menu" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Hapus Menu (hanya untuk admin) -->
            <div class="modal fade" id="hapusModal<?= htmlspecialchars($m["kode_menu"]); ?>" tabindex="-1" role="dialog" aria-labelledby="hapusModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: #8b5a2b; color: white;">
                            <h5 class="modal-title" id="hapusModalLabel">Hapus Menu</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            Apakah Anda yakin ingin menghapus menu <strong><?= htmlspecialchars($m["nama"]); ?></strong>?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <a href="?hapus_menu=<?= htmlspecialchars($m["kode_menu"]); ?>" class="btn btn-danger">Hapus</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php } ?>
</div>
<!-- Include jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<!-- Custom CSS -->
<style>
    body {
        background-color: #f5f5dc; /* Warna latar belakang */
        color: #5b3a29; /* Warna teks */
    }
    .btn-primary {
        background-color: #8b5a2b; /* Warna tombol primary */
        border-color: #8b5a2b; /* Border warna tombol primary */
    }
    .btn-primary:hover {
        background-color: #704d3a; /* Warna hover tombol primary */
        border-color: #704d3a; /* Border hover warna tombol primary */
    }
    .btn-warning {
        background-color: #d69a6a; /* Warna tombol warning */
        border-color: #d69a6a; /* Border warna tombol warning */
    }
    .btn-danger {
        background-color: #c74b3a; /* Warna tombol danger */
        border-color: #c74b3a; /* Border warna tombol danger */
    }
</style>