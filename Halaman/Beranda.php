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
?>

<!-- Form Pemesanan -->
<form action="index.php" method="POST">
    <div class="d-flex">
        <input class="form-control mx-sm-2 my-2 w-auto" type="text" name="pelanggan" placeholder="Nama Pelanggan" required autocomplete="off">
        <button class="btn btn-success my-2 mx-2" name="pesan">Pesan</button>
    </div>
</form>

<!-- Menu Masakan -->
<div class="row">
    <?php foreach ($menu as $m) { ?>
        <div class="col-sm-4 mx-auto m-2">
            <div class="card">
                <h5 class="card-header bg-info"><?= htmlspecialchars($m["nama"]); ?></h5>
                <div class="card-body">
                    <p><img class="rounded" src="src/img/<?= htmlspecialchars($m["gambar"]); ?>" width="150"></p>
                    <p>Harga: Rp<?= number_format($m["harga"], 0, ',', '.'); ?></p>
                    <p>Kategori: <?= htmlspecialchars($m["kategori"]); ?></p>
                    <p>Status: <?= htmlspecialchars($m["status"]); ?></p>
                    <form method="POST" action="">
                        <input type="hidden" name="kode_menu" value="<?= htmlspecialchars($m["kode_menu"]); ?>">
                        <input type="number" name="qty" min="1" value="1" class="form-control">
                        <button type="submit" name="tambah_keranjang" class="btn btn-primary mt-2">
                            <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<!-- Keranjang Belanja dalam Satu Halaman -->
<h3>
    <i class="bi bi-cart"></i> Keranjang Belanja
</h3>
<?php if (!empty($_SESSION['keranjang'])) { ?>
    <form method="POST" action="">
        <table class="table">
            <tr><th>Gambar</th><th>Nama</th><th>Harga</th><th>Qty</th><th>Subtotal</th><th>Action</th></tr>
            <?php $total = 0; ?>
            <?php foreach ($_SESSION['keranjang'] as $kode_menu => $item) { ?>
                <?php $subtotal = $item["price"] * $item["quantity"]; ?>
                <?php $total += $subtotal; ?>
                <tr>
                    <td><img src="src/img/<?= htmlspecialchars($item["image"]); ?>" width="50"></td>
                    <td><?= htmlspecialchars($item["name"]); ?></td>
                    <td>Rp<?= number_format($item["price"], 0, ',', '.'); ?></td>
                    <td><input type="number" name="qty[<?= $kode_menu; ?>]" value="<?= $item['quantity']; ?>" min="1" class="form-control w-50"></td>
                    <td>Rp<?= number_format($subtotal, 0, ',', '.'); ?></td>
                    <td>
                        <a href="?hapus_keranjang=<?= $kode_menu; ?>" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Hapus
                        </a>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <td colspan="4" class="text-right"><strong>Total:</strong></td>
                <td><strong>Rp<?= number_format($total, 0, ',', '.'); ?></strong></td>
                <td></td>
            </tr>
        </table>
        <button type="submit" name="update_keranjang" class="btn btn-success">
            <i class="bi bi-arrow-repeat"></i> Update Keranjang
        </button>
    </form>
<?php } else { echo "Keranjang kosong!"; } ?>
