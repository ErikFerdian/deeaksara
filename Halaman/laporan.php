<?php
require_once __DIR__ . '/../koneksi.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "pwl_kasir_restoran");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Inisialisasi filter default
$filter_tanggal = $_GET['tanggal'] ?? '';

$where_clause = "";
$params = [];

// Jika user memilih tanggal tertentu
if (!empty($filter_tanggal)) {
    $where_clause = "WHERE DATE(waktu) = ?";
    $params[] = $filter_tanggal;
}

// Query transaksi berdasarkan filter
$query_transaksi = "SELECT * FROM transaksi $where_clause";
$stmt = $conn->prepare($query_transaksi);

if (!empty($params)) {
    $stmt->bind_param(str_repeat("s", count($params)), ...$params);
}

$stmt->execute();
$laporan_transaksi = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Hitung keuntungan dan kerugian
$total_pendapatan = 0;
$total_pengeluaran = 0;

foreach ($laporan_transaksi as $data) {
    if ($data["jenis_transaksi"] === 'Masuk') {
        $total_pendapatan += $data["total_harga"];
    } elseif ($data["jenis_transaksi"] === 'Keluar') {
        $total_pengeluaran += $data["total_harga"];
    }
}

$keuntungan = $total_pendapatan - $total_pengeluaran;

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function updateLaporan() {
            let urlParams = new URLSearchParams(window.location.search);
            let tanggal = document.getElementById("tanggal").value;

            if (tanggal) {
                urlParams.set("tanggal", tanggal);
            } else {
                urlParams.delete("tanggal");
            }

            window.location.search = urlParams.toString();
        }
    </script>
</head>
<body>
<div class="container mt-4">
    <h2 class="text-center">📅 Laporan Keuangan</h2>

    <form class="row g-3" method="GET" action="laporan.php">
        <div class="col-md-4">
            <label for="tanggal" class="form-label">Cari Berdasarkan Hari</label>
            <input type="date" class="form-control" id="tanggal" name="tanggal" 
                value="<?= htmlspecialchars($filter_tanggal); ?>" 
                onchange="updateLaporan()">
        </div>
    </form>

    <h4 class="mt-4">📅 Transaksi pada 
        <?= !empty($filter_tanggal) ? date("d M Y", strtotime($filter_tanggal)) : "Periode Tidak Diketahui"; ?>
    </h4>

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
        <thead class="table-success">
                <tr>
                    <th>No</th>
                    <th>Kode Pesanan</th>
                    <th>Nama Pelanggan</th>
                    <th>Waktu</th>
                    <th>Total Harga</th>
                    <th>Jenis</th>
                    <th>Status Pembayaran</th>
                    <th>Metode Pembayaran</th> <!-- Kolom baru -->
                </tr>
            </thead>
            <tbody>
                <?php if (empty($laporan_transaksi)): ?>
                    <tr><td colspan="8" class="text-center">Tidak ada transaksi pada periode ini.</td></tr>
                <?php else: ?>
                    <?php $i = 1; foreach ($laporan_transaksi as $data): ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <td><?= htmlspecialchars($data["kode_pesanan"]); ?></td>
                            <td><?= htmlspecialchars($data["nama_pelanggan"]); ?></td>
                            <td><?= date("d M Y, H:i", strtotime($data["waktu"])); ?></td>
                            <td>Rp. <?= number_format($data["total_harga"], 0, ',', '.'); ?></td>
                            <td><span class="badge bg-<?= $data["jenis_transaksi"] === 'Masuk' ? 'primary' : 'danger'; ?>"><?= htmlspecialchars($data["jenis_transaksi"]); ?></span></td>
                            <td><span class="badge bg-<?= $data["status_pembayaran"] === 'Lunas' ? 'success' : 'warning text-dark'; ?>"><?= htmlspecialchars($data["status_pembayaran"]); ?></span></td>
                            <td><?= htmlspecialchars($data["metode_pembayaran"]); ?></td> <!-- Tampilkan metode pembayaran -->
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>

        </table>
    </div>

    <div class="mt-4">
        <h4>📊 Ringkasan Keuangan</h4>
        <ul class="list-group">
            <li class="list-group-item">Total Pendapatan: <strong>Rp. <?= number_format($total_pendapatan, 0, ',', '.'); ?></strong></li>
            <li class="list-group-item">Total Pengeluaran: <strong>Rp. <?= number_format($total_pengeluaran, 0, ',', '.'); ?></strong></li>
            <li class="list-group-item <?= $keuntungan >= 0 ? 'list-group-item-success' : 'list-group-item-danger'; ?>">
                <?= $keuntungan >= 0 ? "Keuntungan" : "Kerugian"; ?>: 
                <strong>Rp. <?= number_format(abs($keuntungan), 0, ',', '.'); ?></strong>
            </li>
        </ul>
    </div>
</div>
</body>
</html>
