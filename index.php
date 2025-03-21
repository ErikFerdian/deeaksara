<?php
session_start();
require_once "function.php";

if (!isset($_SESSION["akun-admin"]) && !isset($_SESSION["akun-user"])) {
    header("Location: login.php");
    exit;
}

// Ambil data berdasarkan parameter URL
if (isset($_GET["transaksi"])) {
    $menu = ambil_data("SELECT * FROM transaksi");
} elseif (isset($_GET["pesanan"])) {
    $menu = ambil_data("SELECT p.kode_pesanan, tk.nama_pelanggan, p.kode_menu, p.qty
                        FROM pesanan AS p
                        JOIN transaksi AS tk ON (tk.kode_pesanan = p.kode_pesanan)");
} else {
    if (!isset($_GET["search"])) {
        $menu = ambil_data("SELECT * FROM menu ORDER BY kode_menu DESC");
    } else {
        $key_search = $_GET["key-search"];
        $menu = ambil_data("SELECT * FROM menu WHERE nama LIKE '%$key_search%' 
                                                   OR harga LIKE '%$key_search%' 
                                                   OR kategori LIKE '%$key_search%' 
                                                   OR `status` LIKE '%$key_search%' 
                                                   ORDER BY kode_menu DESC");
    }
}

// Proses pemesanan
if (isset($_POST["pesan"])) {
    $pesanan = tambah_data_pesanan();
    echo $pesanan > 0
        ? "<script>alert('Pesanan Berhasil Dikirim!');</script>"
        : "<script>alert('Pesanan Gagal Dikirim!');</script>";
}

// Hitung total item dalam keranjang
$total_item = array_sum(array_column($_SESSION['keranjang'] ?? [], 'quantity'));
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./src/css/bootstrap-5.2.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="./src/css/bootstrap-icons-1.8.3/bootstrap-icons.css">
    <title>Beranda</title>
</head>

<body class="bg-light">

    <!-- Header -->
    <div class="container-fluid position-fixed top-0 bg-dark p-2 d-flex justify-content-between" style="z-index: 2;">
        <div class="text-white h3 d-flex">
            <span id="menu-list" role="button"><i class="bi bi-list"></i></span>
            <span class="mx-3">Dee Aksara</span>
        </div>
        
        <!-- Ikon Keranjang -->
        <div>
            <a href="keranjang.php" class="btn btn-outline-light position-relative">
                <i class="bi bi-cart"></i> Keranjang
                <?php if ($total_item > 0) : ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= $total_item; ?>
                    </span>
                <?php endif; ?>
            </a>
            
            <!-- Tombol Logout -->
            <a class="btn btn-danger fw-bold" href="logout.php" onclick="return confirm('Ingin Logout?')">Logout</a>
        </div>
    </div>

    <!-- List Menu -->
    <div id="dropdown-menu" class="container-fluid position-fixed float-start bg-dark text-white w-auto vh-100" style="display: none; z-index: 1; top: 50px;">
        <ul>
            <br>
            <li><a class="text-decoration-none p-2 h5 text-light" href="index.php">MENU</a></li><br>
            <?php if (isset($_SESSION["akun-admin"])) { ?>
                <li><a class="text-decoration-none p-2 h5 text-light" href="index.php?pesanan">PESANAN</a></li><br>
                <li><a class="text-decoration-none p-2 h5 text-light" href="index.php?transaksi">TRANSAKSI</a></li><br>
                <li><a class="text-decoration-none p-2 h5 text-light" href="index.php?laporan">LAPORAN</a></li> <!-- Pastikan ini benar -->
            <?php } ?>
        </ul>
    </div>

    <!-- Content -->
    <div class="container" style="z-index: -1; margin-top: 60px;">
        <?php
        if (isset($_GET["pesanan"])) {
            include "halaman/pesanan.php";
        } elseif (isset($_GET["transaksi"])) {
            include "halaman/transaksi.php";
        } elseif (isset($_GET["laporan"])) { // Tambahkan cek untuk laporan
            include "halaman/laporan.php"; 
        } else {
            include "halaman/beranda.php";
        }
        ?>
    </div>

    <script src="./src/css/bootstrap-5.2.0/js/bootstrap.min.js"></script>
    <script src="src/js/beranda.js"></script>

</body>
</html>
