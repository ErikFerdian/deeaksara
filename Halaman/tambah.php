<?php
require_once "function.php";

// Proses simpan data
if (isset($_POST["tambah"])) {
    $tambah = tambah_data_menu();

    echo $tambah > 0
        ? "<script>
            alert('Data berhasil ditambah!');
            location.href = 'index.php';
        </script>"
        : "<script>
            alert('Data gagal ditambah!');
            location.href = 'index.php?tambah';
        </script>";
}
?>

<div class="container mt-5">
    <h1 class="my-4 text-center" style="background-color: #8B4513; color: white; padding: 10px;">Tambah Menu Makanan</h1>
    <a class="btn btn-success fw-bold mb-3" href="index.php">Kembali</a>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="table-responsive-md">
            <table class="table table-bordered" style="background-color: #D2B48C;">
                <tr>
                    <td><label for="nama">Nama Menu</label></td>
                    <td><input type="text" name="nama" id="nama" class="form-control" required></td>
                </tr>
                <tr>
                    <td><label for="harga">Harga</label></td>
                    <td><input type="number" name="harga" id="harga" class="form-control" min="0" required></td>
                </tr>
                <tr>
                    <td><label for="gambar">Gambar</label></td>
                    <td><input type="file" name="gambar" id="gambar" class="form-control" accept="image/*" required></td>
                </tr>
                <tr>
                    <td><label for="kategori">Kategori</label></td>
                    <td>
                        <select name="kategori" id="kategori" class="form-select" required>
                            <option value="Makanan" selected>Makanan</option>
                            <option value="Fast Food">Fast Food</option>
                            <option value="Snack">Snack</option>
                            <option value="Dessert">Dessert</option>
                            <option value="Minuman">Minuman</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label>Status</label></td>
                    <td>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="tersedia" value="tersedia" checked>
                            <label class="form-check-label" for="tersedia">Tersedia</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="tidak-tersedia" value="tidak tersedia">
                            <label class="form-check-label" for="tidak-tersedia">Tidak Tersedia</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <button class="btn btn-primary w-100" name="tambah" type="submit" style="background-color: #A0522D; border-color: #A0522D;">Tambah Menu</button>
                    </td>
                </tr>
            </table>
        </div>
    </form>
</div>

<style>
    body {
        background-color: #f5f5dc; /* Warna latar belakang krem */
        color: #5b3a29; /* Warna teks coklat gelap */
    }
    .btn-success {
        background-color: #6B8E23; /* Warna hijau untuk tombol kembali */
        border-color: #6B8E23;
    }
</style>