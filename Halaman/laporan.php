<?php
require_once __DIR__ . '/../koneksi.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Inisialisasi filter
$filter_tanggal = $_GET['tanggal'] ?? '';
$filter_bulan = $_GET['bulan'] ?? '';
$filter_tahun = $_GET['tahun'] ?? '';

$where_clause = "";
$params = [];
$label_periode = "Semua Data";

// Filter berdasarkan tanggal
if (!empty($filter_tanggal)) {
    $where_clause = "WHERE DATE(waktu) = ?";
    $params[] = $filter_tanggal;
    $label_periode = "Tanggal " . date("d M Y", strtotime($filter_tanggal));
}
// Filter berdasarkan bulan
elseif (!empty($filter_bulan)) {
    $where_clause = "WHERE DATE_FORMAT(waktu, '%Y-%m') = ?";
    $params[] = $filter_bulan;
    $label_periode = "Bulan " . date("F Y", strtotime($filter_bulan . "-01"));
}
// Filter berdasarkan tahun
elseif (!empty($filter_tahun)) {
    $where_clause = "WHERE DATE_FORMAT(waktu, '%Y') = ?";
    $params[] = $filter_tahun;
    $label_periode = "Tahun " . htmlspecialchars($filter_tahun);
}

// Query transaksi
$query_transaksi = "SELECT * FROM transaksi $where_clause ORDER BY waktu DESC";
$stmt = $conn->prepare($query_transaksi);

if (!empty($params)) {
    $stmt->bind_param(str_repeat("s", count($params)), ...$params);
}

$stmt->execute();
$laporan_transaksi = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Hitung total pendapatan
$total_pendapatan = 0;
foreach ($laporan_transaksi as $data) {
    if ($data["jenis_transaksi"] === 'Masuk') {
        $total_pendapatan += $data["total_harga"];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .header {
            background-color: var(--dark-brown);
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(93, 64, 55, 0.1);
        }

        .filter-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(93, 64, 55, 0.1);
            border: 1px solid var(--accent-brown);
        }

        .form-control, .form-select {
            border-color: var(--light-brown);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--medium-brown);
            box-shadow: 0 0 0 0.25rem rgba(141, 110, 99, 0.25);
        }

        .table-container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(93, 64, 55, 0.1);
            margin-bottom: 20px;
            border: 1px solid var(--accent-brown);
        }

        .table {
            color: var(--text-brown);
        }

        .table thead {
            background-color: var(--dark-brown);
            color: white;
        }

        .table th, .table td {
            padding: 12px;
            vertical-align: middle;
        }

        .table-striped tbody tr:nth-child(odd) {
            background-color: var(--cream);
        }

        .table-hover tbody tr:hover {
            background-color: var(--accent-brown);
        }

        .badge-primary {
            background-color: #6B8E23;
        }

        .badge-danger {
            background-color: #A44A3F;
        }

        .badge-success {
            background-color: #5D8E63;
        }

        .badge-warning {
            background-color: #D2B48C;
            color: var(--text-brown);
        }

        .summary-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(93, 64, 55, 0.1);
            border: 1px solid var(--accent-brown);
        }

        .list-group-item {
            background-color: var(--cream);
            border-color: var(--light-brown);
        }

        .period-label {
            color: var(--medium-brown);
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-brown);
        }

        .no-data {
            color: var(--medium-brown);
            font-style: italic;
            text-align: center;
            padding: 20px;
        }
    </style>
    <script>
        function updateLaporan() {
            const tanggal = document.getElementById("tanggal").value;
            const bulan = document.getElementById("bulan").value;
            const tahun = document.getElementById("tahun").value;
            const urlParams = new URLSearchParams(window.location.search);

            if (tanggal) {
                urlParams.set("tanggal", tanggal);
                urlParams.delete("bulan");
                urlParams.delete("tahun");
            } else if (bulan) {
                urlParams.set("bulan", bulan);
                urlParams.delete("tanggal");
                urlParams.delete("tahun");
            } else if (tahun) {
                urlParams.set("tahun", tahun);
                urlParams.delete("tanggal");
                urlParams.delete("bulan");
            } else {
                urlParams.delete("tanggal");
                urlParams.delete("bulan");
                urlParams.delete("tahun");
            }

            window.location.search = urlParams.toString();
        }
    </script>
</head>
<body>
<div class="container mt-4">
    <h2 class="text-center header">📅 Laporan Keuangan</h2>

    <div class="filter-card">
        <form class="row g-3" method="GET" action="<?= basename($_SERVER['PHP_SELF']); ?>">
            <div class="col-md-4">
                <label for="tanggal" class="form-label">Cari Berdasarkan Hari</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal"
                       value="<?= htmlspecialchars($filter_tanggal); ?>"
                       onchange="updateLaporan()">
            </div>
            <div class="col-md-4">
                <label for="bulan" class="form-label">Cari Berdasarkan Bulan</label>
                <input type="month" class="form-control" id="bulan" name="bulan"
                       value="<?= htmlspecialchars($filter_bulan); ?>"
                       onchange="updateLaporan()">
            </div>
            <div class="col-md-4">
                <label for="tahun" class="form-label">Cari Berdasarkan Tahun</label>
                <input type="number" class="form-control" id="tahun" name="tahun"
                       value="<?= htmlspecialchars($filter_tahun); ?>"
                       onchange="updateLaporan()">
            </div>
        </form>
    </div>

    <h4 class="period-label">📅 Transaksi pada <?= $label_periode; ?></h4>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Pesanan</th>
                    <th>Nama Pelanggan</th>
                    <th>Waktu</th>
                    <th>Total Harga</th>
                    <th>Jenis</th>
                    <th>Status Pembayaran</th>
                    <th>Metode Pembayaran</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($laporan_transaksi)): ?>
                    <tr><td colspan="8" class="no-data">Tidak ada transaksi pada periode ini.</td></tr>
                <?php else: ?>
                    <?php $i = 1; foreach ($laporan_transaksi as $data): ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <td><?= htmlspecialchars($data["kode_pesanan"]); ?></td>
                            <td><?= htmlspecialchars($data["nama_pelanggan"]); ?></td>
                            <td><?= date("d M Y, H:i", strtotime($data["waktu"])); ?></td>
                            <td>Rp <?= number_format($data["total_harga"], 0, ',', '.'); ?></td>
                            <td>
                                <span class="badge bg-<?= $data["jenis_transaksi"] === 'Masuk' ? 'primary' : 'danger'; ?>">
                                    <?= htmlspecialchars($data["jenis_transaksi"]); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $data["status_pembayaran"] === 'Lunas' ? 'success' : 'warning'; ?>">
                                    <?= htmlspecialchars($data["status_pembayaran"]); ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($data["metode_pembayaran"]); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="summary-card">
        <h4>📊 Ringkasan Keuangan</h4>
        <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between align-items-center">
                Total Pendapatan
                <span class="badge bg-primary rounded-pill">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></span>
            </li>
        </ul>
    </div>
</div>
</body>
</html>
