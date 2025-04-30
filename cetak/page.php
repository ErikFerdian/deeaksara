<?php
require_once "../function.php";

if (!isset($_GET["kode_pesanan"]) || empty($_GET["kode_pesanan"])) {
    die("Kode pesanan tidak ditemukan.");
}

$kode = $_GET["kode_pesanan"];
$menu = ambil_data("SELECT DISTINCT * FROM pesanan 
                    JOIN transaksi ON (pesanan.kode_pesanan = transaksi.kode_pesanan) 
                    JOIN menu ON (menu.kode_menu = pesanan.kode_menu) 
                    WHERE transaksi.kode_pesanan = '$kode'");

if (!$menu || count($menu) == 0) {
    die("Data pesanan tidak ditemukan.");
}

$total_semuanya = 0;
foreach ($menu as $m) {
    $total_semuanya += $m["harga"] * $m["qty"];
}

$pembayaran = isset($_GET["pembayaran"]) ? (int)$_GET["pembayaran"] : 0;
$kembalian = max(0, $pembayaran - $total_semuanya);
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
        .logo img {
            width: 80px;
            height: auto;
            margin-bottom: 10px;
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
            margin: 10px 0;
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
        .link-box {
            border: 1px solid black;
            padding: 5px;
            display: inline-block;
            margin-top: 5px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="logo">
        <img src="logo.png" alt="Logo Toko">
    </div>
    <div class="header">Ucoffe</div>
    <div class="sub-header">Jl. Contoh No.123, Kota, Indonesia</div>
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
    <div class="sub-header">Bayar: Rp.<?= number_format($pembayaran, 0, ',', '.') ?></div>
    <div class="sub-header">Kembali: Rp.<?= number_format($kembalian, 0, ',', '.') ?></div>
    <div class="line"></div>

    <div class="footer">Terima Kasih Telah Berbelanja</div>
</body>
</html>