<?php
// Konfigurasi koneksi database
$host = "localhost";
$user = "root"; // Default username XAMPP
$password = ""; // Kosongkan jika tidak ada password
$database = "pwl_kasir_restoran"; // Ganti dengan nama database Anda

// Membuat koneksi
$conn = mysqli_connect($host, $user, $password, $database);

// Memeriksa koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Jika koneksi berhasil, Anda bisa melanjutkan dengan query database di sini
// Contoh: echo "Koneksi berhasil!";
?>