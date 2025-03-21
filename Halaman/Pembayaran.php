<?php
session_start();
require_once 'koneksi.php';


$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Proses pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bayar'])) {
    $kode_pesanan = $conn->real_escape_string($_POST['kode_pesanan']);
    $jumlah_uang = (int) $_POST['jumlah_uang'];

    // Cek apakah kode pesanan valid dan belum dibayar
    $cek_pesanan = "SELECT * FROM transaksi WHERE kode_pesanan = '$kode_pesanan' AND status_pembayaran = 'Belum Dibayar'";
    $result_cek = $conn->query($cek_pesanan);

    if ($result_cek->num_rows > 0) {
        $data = $result_cek->fetch_assoc();

        // Hitung total pembelian
        $query_total = "SELECT SUM(menu.harga * pesanan.qty) AS total 
                        FROM pesanan 
                        JOIN menu ON pesanan.kode_menu = menu.kode_menu 
                        WHERE pesanan.kode_pesanan = '$kode_pesanan'";
        $result_total = $conn->query($query_total);
        $row_total = $result_total->fetch_assoc();
        $total_beli = (int) $row_total['total'];

        if ($jumlah_uang >= $total_beli) {
            $kembalian = $jumlah_uang - $total_beli;

            // Update transaksi dengan total bayar dan kembalian
            $update = "UPDATE transaksi SET 
            total_bayar = $jumlah_uang, 
            kembalian = $kembalian, 
            status_pembayaran = 'Lunas' 
            WHERE kode_pesanan = '$kode_pesanan'";

            if ($conn->query($update)) {
                echo "<script>alert('Pembayaran berhasil! Kembalian: Rp " . number_format($kembalian, 0, ',', '.') . "'); window.location.reload();</script>";
            } else {
                echo "<script>alert('Gagal menyimpan pembayaran: " . $conn->error . "');</script>"; 
            }
        } else {
            echo "<script>alert('Uang tidak cukup untuk pembayaran!');</script>";
        }
    } else {
        echo "<script>alert('Pesanan tidak ditemukan atau sudah dibayar!');</script>";
    }
}

// Ambil daftar transaksi yang belum dibayar
$query = "SELECT * FROM transaksi WHERE status_pembayaran = 'Belum Dibayar'";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Pembayaran</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>
    <h2>Daftar Pembayaran</h2>
    <table>
        <tr>
            <th>Kode Pesanan</th>
            <th>Nama Pelanggan</th>
            <th>Waktu</th>
            <th>Total Bayar</th>
            <th>Kembalian</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['kode_pesanan']); ?></td>
                <td><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                <td><?php echo htmlspecialchars($row['waktu']); ?></td>
                <td>Rp <?php echo number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                <td>Rp <?php echo number_format($row['kembalian'], 0, ',', '.'); ?></td>
                <td><?php echo htmlspecialchars($row['status_pembayaran']); ?></td>
                <td>
                    <form method="POST" action="">
                        <input type="hidden" name="kode_pesanan" value="<?php echo htmlspecialchars($row['kode_pesanan']); ?>">
                        <label for="jumlah_uang">Jumlah Uang:</label>
                        <input type="number" name="jumlah_uang" required>
                        <button type="submit" name="bayar">Bayar</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
<?php $conn->close(); ?>
