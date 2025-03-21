<?php
require_once "../function.php";

if (!isset($_GET["kode_pesanan"]) || empty($_GET["kode_pesanan"])) {
    die("Kode pesanan tidak ditemukan.");
}

$kode = $_GET["kode_pesanan"];

// Ambil data transaksi termasuk total bayar, kembalian, dan metode pembayaran
$transaksi = ambil_data("SELECT total_bayar, kembalian, metode_pembayaran FROM transaksi WHERE kode_pesanan = '$kode'");
if (!$transaksi || count($transaksi) == 0) {
    die("Data transaksi tidak ditemukan.");
}

$total_bayar = $transaksi[0]["total_bayar"];
$kembalian = $transaksi[0]["kembalian"];
$metode_pembayaran = $transaksi[0]["metode_pembayaran"]; // Tambahkan metode pembayaran
// Ambil data pesanan
$menu = ambil_data("SELECT DISTINCT pesanan.*, menu.nama, menu.harga FROM pesanan 
                    JOIN menu ON pesanan.kode_menu = menu.kode_menu 
                    WHERE pesanan.kode_pesanan = '$kode'");

if (!$menu || count($menu) == 0) {
    die("Data pesanan tidak ditemukan.");
}

// Hitung total harga dari semua item
$total_semuanya = 0;
foreach ($menu as $m) {
    $total_semuanya += $m["harga"] * $m["qty"];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            width: 300px;
            margin: auto;
            text-align: center;
        }
        .header {
            font-size: 18px;
            font-weight: bold;
        }
        .sub-header {
            font-size: 14px;
        }
        .line {
            border-top: 1px dashed black;
            margin: 10px 10px;
        }
        .item {
            text-align: left;
            font-size: 14px;
        }
        .total {
            font-size: 16px;
            font-weight: bold;
        }
        .footer {
            font-size: 12px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">Dee Aksara</div>
    <div class="sub-header">JL.Ketintang Sel.No 47,Karah.Kec Jambangan Surabaya JawaTimur-Kode Pos:60232</div>
    <div class="sub-header">Telp: 08123456789</div>
    <div class="line"></div>

    <div class="sub-header">Kode Pesanan: <?= htmlspecialchars($kode) ?></div>
    <div class="sub-header">Tanggal: <?= date("Y-m-d H:i:s") ?></div>
    <div class="line"></div>

    <?php foreach ($menu as $m) { ?>
        <div class="item">
            <b><?= htmlspecialchars($m["nama"]) ?></b><br>
            <?= (int)$m["qty"] ?> x Rp.<?= number_format($m["harga"], 0, ',', '.') ?><br>
            <b class="total">Rp.<?= number_format($m["harga"] * $m["qty"], 0, ',', '.') ?></b>
            <div class="line"></div>
        </div>
    <?php } ?>

    <div class="total">Total: Rp.<?= number_format($total_semuanya, 0, ',', '.') ?></div>
    <div class="sub-header">Bayar: Rp.<?= number_format($total_bayar, 0, ',', '.') ?></div>
    <div class="sub-header">Kembali: Rp.<?= number_format($kembalian, 0, ',', '.') ?></div>
    <div class="sub-header">Metode Pembayaran: <?= htmlspecialchars($metode_pembayaran) ?></div>
    <div class="line"></div>

    <div class="footer">Terima Kasih Telah Berbelanja</div>
</body>
</html>
