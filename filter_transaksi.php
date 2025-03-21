<?php
require_once __DIR__ . '/../koneksi.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Ambil tanggal dari request
$filter_tanggal = $_POST['tanggal'] ?? date('Y-m-d');

// Query transaksi berdasarkan tanggal
$query_transaksi = "SELECT * FROM transaksi WHERE DATE(waktu) = ?";
$stmt = $conn->prepare($query_transaksi);
$stmt->bind_param("s", $filter_tanggal);
$stmt->execute();
$laporan_transaksi = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Query total pemasukan
$query_pemasukan = "SELECT SUM(total_harga) AS total_pemasukan FROM transaksi WHERE DATE(waktu) = ? AND jenis_transaksi = 'Masuk'";
$stmt = $conn->prepare($query_pemasukan);
$stmt->bind_param("s", $filter_tanggal);
$stmt->execute();
$total_pemasukan = $stmt->get_result()->fetch_assoc()['total_pemasukan'] ?? 0;
$stmt->close();

// Query total pengeluaran
$query_pengeluaran = "SELECT SUM(total_harga) AS total_pengeluaran FROM transaksi WHERE DATE(waktu) = ? AND jenis_transaksi = 'Keluar'";
$stmt = $conn->prepare($query_pengeluaran);
$stmt->bind_param("s", $filter_tanggal);
$stmt->execute();
$total_pengeluaran = $stmt->get_result()->fetch_assoc()['total_pengeluaran'] ?? 0;
$stmt->close();

$laba_rugi = $total_pemasukan - $total_pengeluaran;

$conn->close();

// Kirim data dalam format JSON
echo json_encode([
    'transaksi' => $laporan_transaksi,
    'total_pemasukan' => $total_pemasukan,
    'total_pengeluaran' => $total_pengeluaran,
    'laba_rugi' => $laba_rugi,
    'filter_tanggal' => date("d M Y", strtotime($filter_tanggal))
]);
?>
