<?php
require_once __DIR__ . '/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST["kode_pesanan"]) || !isset($_POST["status_pembayaran"]) || !isset($_POST["metode_pembayaran"])) {
        echo "error: Data tidak lengkap";
        exit;
    }

    $kode_pesanan = $_POST['kode_pesanan'];
    $status_pembayaran = $_POST['status_pembayaran'];
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $total_bayar = isset($_POST["total_bayar"]) ? intval($_POST["total_bayar"]) : 0;
    $kembalian = isset($_POST["kembalian"]) ? intval($_POST["kembalian"]) : 0;

    // Get total price
    $query_total_harga = "SELECT SUM(pesanan.qty * menu.harga) AS total_harga 
                          FROM pesanan 
                          JOIN menu ON pesanan.kode_menu = menu.kode_menu 
                          WHERE pesanan.kode_pesanan = ?";
    $stmt = $conn->prepare($query_total_harga);
    $stmt->bind_param("s", $kode_pesanan);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $total_harga = intval($data["total_harga"]);

    // For QRIS, automatically set as paid
    if ($metode_pembayaran === 'QRIS') {
        $status_pembayaran = 'Lunas';
        $kembalian = 0;
        $total_bayar = $total_harga;
    }

    // Update transaction
    $query_update = "UPDATE transaksi SET 
                        status_pembayaran = ?, 
                        metode_pembayaran = ?, 
                        total_harga = ?, 
                        total_bayar = ?, 
                        kembalian = ? 
                     WHERE kode_pesanan = ?";
    $stmt = $conn->prepare($query_update);
    $stmt->bind_param("ssiiss", $status_pembayaran, $metode_pembayaran, $total_harga, $total_bayar, $kembalian, $kode_pesanan);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "error: Metode request tidak valid";
}
?>