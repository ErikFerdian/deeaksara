<?php
session_start();
require_once 'koneksi.php';

// Periksa koneksi database
if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['keranjang']) || !is_array($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Fungsi mendapatkan data menu
function getItemDetails($conn, $id_menu) {
    $stmt = $conn->prepare("SELECT nama, harga FROM menu WHERE id_menu = ?");
    if (!$stmt) {
        die("Query Error: " . $conn->error);
    }
    $stmt->bind_param("s", $id_menu);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Menambahkan item ke keranjang
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_cart'])) {
    $item_id = $_POST['item_id'];
    $quantity = max(1, intval($_POST['quantity']));

    if (!empty($item_id)) {
        $menu = getItemDetails($conn, $item_id);
        if ($menu) {
            if (!isset($_SESSION['keranjang'][$item_id])) {
                $_SESSION['keranjang'][$item_id] = [
                    'name' => $menu['nama'],
                    'price' => intval($menu['harga']),
                    'quantity' => $quantity
                ];
            } else {
                $_SESSION['keranjang'][$item_id]['quantity'] += $quantity;
            }
        }
    }
    header("Location: keranjang.php");
    exit();
}

// Mengupdate jumlah item di keranjang
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_cart'])) {
    if (!empty($_POST['quantity'])) {
        foreach ($_POST['quantity'] as $item_id => $qty) {
            if (isset($_SESSION['keranjang'][$item_id])) {
                $_SESSION['keranjang'][$item_id]['quantity'] = max(1, intval($qty));
            }
        }
    }
    header("Location: keranjang.php");
    exit();
}

// Menghapus item dari keranjang
if (isset($_GET['remove'])) {
    $item_id = $_GET['remove'];
    unset($_SESSION['keranjang'][$item_id]);
    header("Location: keranjang.php");
    exit();
}

// Proses transaksi saat tombol "Pesan" ditekan
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_payment'])) {
    $nama_pelanggan = htmlspecialchars($_POST['nama_pelanggan']);
    $kode_pesanan = uniqid("ORD"); // Generate kode pesanan unik
    $total_harga = 0;

    // Hitung total harga
    foreach ($_SESSION['keranjang'] as $item) {
        $total_harga += $item['price'] * $item['quantity'];
    }

    // Simpan data ke tabel transaksi
    $stmt = $conn->prepare("INSERT INTO transaksi (kode_pesanan, nama_pelanggan, total_harga, waktu) VALUES (?, ?, ?, NOW())");
    if (!$stmt) {
        die("Query Error: " . $conn->error);
    }
    $stmt->bind_param("ssd", $kode_pesanan, $nama_pelanggan, $total_harga);
    $stmt->execute();

    // Simpan detail pesanan ke tabel pesanan
    foreach ($_SESSION['keranjang'] as $item_id => $item) {
        $stmt = $conn->prepare("INSERT INTO pesanan (kode_pesanan, kode_menu, qty) VALUES (?, ?, ?)");
        if (!$stmt) {
            die("Query Error: " . $conn->error);
        }
        $stmt->bind_param("ssi", $kode_pesanan, $item_id, $item['quantity']);
        $stmt->execute();
    }

    // Bersihkan keranjang setelah transaksi sukses
    $_SESSION['keranjang'] = [];
    
    echo "<script>alert('Pesanan berhasil dibuat! Kode Pesanan: $kode_pesanan'); window.location.href='keranjang.php';</script>";
    exit();
}

// Menghitung total harga
$total_harga = 0;
foreach ($_SESSION['keranjang'] as $item) {
    $total_harga += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="mb-4 text-center">🛒 Keranjang</h2>

    <div class="card p-3">
        <form method="POST" action="keranjang.php">
            <table class="table table-bordered text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Harga Satuan</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($_SESSION['keranjang'])): ?>
                        <?php foreach ($_SESSION['keranjang'] as $item_id => $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']); ?></td>
                                <td>
                                    <input type="number" name="quantity[<?= $item_id; ?>]" value="<?= $item['quantity']; ?>" min="1" class="form-control w-50">
                                </td>
                                <td>Rp <?= number_format($item['price'], 0, ',', '.'); ?></td>
                                <td>Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                                <td>
                                    <a href="keranjang.php?remove=<?= $item_id; ?>" class="btn btn-sm btn-danger">🗑 Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-secondary">
                            <td colspan="3"><b>Total Harga</b></td>
                            <td colspan="2"><b>Rp <?= number_format($total_harga, 0, ',', '.'); ?></b></td>
                        </tr>
                        <tr>
                            <td colspan="5">
                                <button type="submit" name="update_cart" class="btn btn-success">🔄 Update Keranjang</button>
                            </td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Keranjang kosong.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
    </div>

    <!-- Form Konfirmasi Pembayaran -->
    <?php if (!empty($_SESSION['keranjang'])): ?>
        <div class="text-center mt-3">
            <form method="POST">
                <input type="text" name="nama_pelanggan" class="form-control w-50 me-2 d-inline" placeholder="Nama Pelanggan" required>
                <button type="submit" name="confirm_payment" class="btn btn-lg btn-success">✅ Pesan</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="text-center mt-3">
        <a href="index.php" class="btn btn-primary">🏠 Kembali ke Beranda</a>
    </div>
</div>

</body>
</html>
