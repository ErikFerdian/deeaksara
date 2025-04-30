<?php
ob_start(); // Tambahkan baris ini sebagai baris pertama!
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
    <style>
        :root {
            --dark-brown: #5D4037;
            --medium-brown: #8D6E63;
            --light-brown: #BCAAA4;
            --cream: #EFEBE9;
            --accent-brown: #D7CCC8;
            --text-brown: #3E2723;
        }
        
        body {
            background-color: var(--cream);
            color: var(--text-brown);
        }
        
        .bg-dark {
            background-color: var(--dark-brown) !important;
        }
        
        .text-white {
            color: white !important;
        }
        
        .btn-outline-light {
            border-color: var(--light-brown);
            color: var(--light-brown);
        }
        
        .btn-outline-light:hover {
            background-color: var(--light-brown);
            color: white;
        }
        
        .btn-danger {
            background-color: #A44A3F;
            border-color: #A44A3F;
        }
        
        .btn-danger:hover {
            background-color: #8B3A2F;
        }
        
        #dropdown-menu {
            background-color: var(--dark-brown);
        }
        
        #dropdown-menu a {
            color: white;
        }
        
        #dropdown-menu a:hover {
            background-color: var(--medium-brown);
        }
        
        /* Laporan Specific Styles */
        .laporan-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(93, 64, 55, 0.1);
            padding: 25px;
            margin-top: 20px;
            border: 1px solid var(--accent-brown);
        }
        
        .laporan-header {
            color: var(--dark-brown);
            border-bottom: 2px solid var(--light-brown);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .table-laporan {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table-laporan thead {
            background-color: var(--dark-brown);
            color: white;
        }
        
        .table-laporan th {
            padding: 12px 15px;
            text-align: left;
        }
        
        .table-laporan tbody tr {
            border-bottom: 1px solid var(--accent-brown);
        }
        
        .table-laporan tbody tr:nth-child(even) {
            background-color: var(--cream);
        }
        
        .table-laporan tbody tr:hover {
            background-color: var(--accent-brown);
        }
        
        .table-laporan td {
            padding: 12px 15px;
        }
        
        .laporan-summary {
            background-color: var(--accent-brown);
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .btn-laporan {
            background-color: var(--medium-brown);
            color: white;
            border: none;
        }
        
        .btn-laporan:hover {
            background-color: var(--dark-brown);
            color: white;
        }
        
        .filter-section {
            background-color: var(--accent-brown);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .form-control:focus {
            border-color: var(--medium-brown);
            box-shadow: 0 0 0 0.25rem rgba(141, 110, 99, 0.25);
        }
    </style>
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
                <li><a class="text-decoration-none p-2 h5 text-light" href="index.php?tambah">TAMBAH</a></li><br>
                <li><a class="text-decoration-none p-2 h5 text-light" href="index.php?laporan">LAPORAN</a></li>
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
} elseif (isset($_GET["laporan"])) {
    echo '<div class="laporan-container">';
    include "halaman/laporan.php"; 
    echo '</div>';
} elseif (isset($_GET["tambah"])) {
    include "halaman/tambah.php";
} else {
    include "halaman/beranda.php";
}
?>
    </div>

    <script src="./src/css/bootstrap-5.2.0/js/bootstrap.min.js"></script>
    <script src="src/js/beranda.js"></script>

</body>
</html>