<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white text-center">
            <h4>Data Transaksi</h4>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover text-center">
                <thead class="table-success">
                    <tr>
                        <th>No</th>
                        <th>Kode Pesanan</th>
                        <th>Nama Pelanggan</th>
                        <th>Waktu</th>
                        <th>Total Harga</th>
                        <th>Status Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php $i = 1; foreach ($menu as $m): 
                    $kode_pesanan = $m["kode_pesanan"];
                    $total_pembayaran = ambil_data("SELECT pesanan.qty, menu.harga FROM pesanan JOIN transaksi ON pesanan.kode_pesanan = transaksi.kode_pesanan JOIN menu ON pesanan.kode_menu = menu.kode_menu WHERE transaksi.kode_pesanan = '$kode_pesanan'");?>
                    <tr> <!-- ✅ BENAR! PHP sudah ditutup sebelum <tr> -->
                        <td><?= $i; ?></td>
                        <td><?= $m["kode_pesanan"]; ?></td>
                        <td><?= $m["nama_pelanggan"]; ?></td>
                        <td><?= $m["waktu"]; ?></td>
                        <td>
                            <?php
                            $total = 0;
                            foreach ($total_pembayaran as $tp) {
                                $total += $tp["qty"] * $tp["harga"];
                            }
                            echo "Rp. " . number_format($total, 0, ',', '.');
                            ?>
                        </td>
                        <td id="status-<?= $m["kode_pesanan"]; ?>">
                            <?php if ($m["status_pembayaran"] == "Lunas"): ?>
                                <span class="badge bg-success">Lunas</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Belum Dibayar</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-success btn-sm" onclick="konfirmasiPembayaran(<?= $total; ?>, '<?= $m["kode_pesanan"]; ?>')">Bayar</button>
                            <a class="btn btn-danger btn-sm" href="hapus.php?kode_pesanan=<?= $m["kode_pesanan"]; ?>" onclick="return confirm('Hapus Data Transaksi?')">Hapus</a>
                            <button class="btn btn-warning btn-sm" onclick="editStatus('<?= $m["kode_pesanan"]; ?>', '<?= $m["status_pembayaran"]; ?>')">Edit</button>
                            <form action="cetak/cetak.php" target="_blank" method="GET" class="d-inline">
                                <input type="hidden" name="kode_pesanan" value="<?= $m["kode_pesanan"]; ?>">
                                <button class="btn btn-primary btn-sm">Cetak</button>
                            </form>
                        </td>
                    </tr>
                <?php $i++; endforeach; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit Status Pembayaran -->
<div class="modal fade" id="modalEditStatus" tabindex="-1" aria-labelledby="modalEditStatusLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditStatusLabel">Edit Status Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditStatus">
                    <input type="hidden" id="kodePesananEdit" name="kode_pesanan">
                    <div class="mb-3">
                        <label for="statusPembayaran" class="form-label">Status Pembayaran</label>
                        <select id="statusPembayaran" class="form-select" name="status_pembayaran">
                            <option value="Belum Dibayar">Belum Dibayar</option>
                            <option value="Lunas">Lunas</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success" onclick="updateStatus()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Pembayaran -->
<div class="modal fade" id="modalPembayaran" tabindex="-1" aria-labelledby="modalPembayaranLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPembayaranLabel">Konfirmasi Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="totalHarga" class="form-label">Total Harga</label>
                        <input type="text" id="totalHarga" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="metodePembayaran" class="form-label">Metode Pembayaran</label>
                        <select id="metodePembayaran" class="form-select" onchange="toggleInputJumlah()">
                            <option value="cash">Cash</option>
                            <option value="online">QRIS</option>
                        </select>
                    </div>
                    <div id="inputUang" class="mb-3">
                        <label for="jumlahUang" class="form-label">Jumlah Uang</label>
                        <input type="number" id="jumlahUang" class="form-control" oninput="hitungKembalian()">
                    </div>
                    <div class="mb-3">
                        <label for="totalKembali" class="form-label">Total Kembali</label>
                        <input type="text" id="totalKembali" class="form-control" readonly>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"ws>Tutup</button>
                <button type="button" class="btn btn-primary" id="btnCetak" onclick="cetakStruk()">Cetak</button>
                <button type="button" class="btn btn-success" id="btnBayar" onclick="prosesPembayaran()">Bayar</button>
            </div>
        </div>
    </div>
</div>


<script>
let currentKodePesanan = '';

function konfirmasiPembayaran(total, kodePesanan) {
    document.getElementById('totalHarga').value = 'Rp. ' + total.toLocaleString('id-ID');
    document.getElementById('jumlahUang').value = '';
    document.getElementById('totalKembali').value = '';
    document.getElementById('metodePembayaran').value = 'cash'; // Default ke Cash
    toggleInputJumlah();
    currentKodePesanan = kodePesanan;
    
    var modal = new bootstrap.Modal(document.getElementById('modalPembayaran'));
    modal.show();
}
function toggleInputJumlah() {
    let metode = document.getElementById('metodePembayaran').value;

    let inputUang = document.getElementById('inputUang');
    
    if (metode === 'online') {  // Pastikan 'QRIS' ditampilkan dengan benar
        metode = 'QRIS';
        inputUang.style.display = 'none';
        document.getElementById('jumlahUang').value = '';
        document.getElementById('totalKembali').value = '';
    } else {
        inputUang.style.display = 'block';
    }
}

function hitungKembalian() {
    let metode = document.getElementById('metodePembayaran').value;
    let totalHarga = parseInt(document.getElementById('totalHarga').value.replace('Rp. ', '').replaceAll('.', '')) || 0;
    let jumlahUang = parseInt(document.getElementById('jumlahUang').value) || 0;

    let kembalian = metode === 'cash' ? jumlahUang - totalHarga : 0; // QRIS tidak ada kembalian

    document.getElementById('totalKembali').value = 'Rp. ' + kembalian.toLocaleString('id-ID');
}



function prosesPembayaran() {
    let metode = document.getElementById('metodePembayaran').value;
    let jumlahUang = parseInt(document.getElementById('jumlahUang').value) || 0;
    let totalHarga = parseInt(document.getElementById('totalHarga').value.replace('Rp. ', '').replaceAll('.', ''));
    let kembalian = jumlahUang - totalHarga;

    let statusPembayaran = (metode === 'online' || metode === 'qris') ? 'Lunas' : 'Belum Dibayar';

    // Validasi jika metode Cash, uang harus cukup
    if (metode === 'cash' && jumlahUang < totalHarga) {
        alert('Jumlah uang kurang!');
        return;
    }

    let data = new URLSearchParams();
    data.append('kode_pesanan', currentKodePesanan);
    data.append('status_pembayaran', statusPembayaran);
    data.append('metode_pembayaran', metode);
    data.append('total_bayar', metode === 'online' ? totalHarga : jumlahUang);
    data.append('kembalian', metode === 'online' ? 0 : kembalian);

    console.log("Mengirim data pembayaran:", Object.fromEntries(data));

    fetch('update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: data.toString()
    })
    .then(response => response.text())
    .then(data => {
        console.log("Respons dari server:", data);
        if (data.trim() === 'success') {
            // ✅ Perbarui status pembayaran tanpa reload
            document.getElementById('status-' + currentKodePesanan).innerHTML = 
                (statusPembayaran === "Lunas") 
                ? '<span class="badge bg-success">Lunas</span>' 
                : '<span class="badge bg-warning text-dark">Belum Dibayar</span>';

            alert('Pembayaran berhasil!');

            // ✅ Tutup modal setelah pembayaran berhasil
            var modal = bootstrap.Modal.getInstance(document.getElementById('modalPembayaran'));
            modal.hide();
        } else {
            alert('Terjadi kesalahan: ' + data);
        }
    })
    .catch(error => {
        alert('Gagal terhubung ke server: ' + error);
    });
}




// Fungsi untuk membuka modal Edit Status Pembayaran
function editStatus(kodePesanan, statusPembayaran) {
    document.getElementById('kodePesananEdit').value = kodePesanan;
    document.getElementById('statusPembayaran').value = statusPembayaran;
    currentKodePesanan = kodePesanan;

    var modal = new bootstrap.Modal(document.getElementById('modalEditStatus'));
    modal.show();
}

// Fungsi untuk menyimpan perubahan status pembayaran
function updateStatus() {
    let kodePesanan = document.getElementById('kodePesananEdit').value;
    let statusPembayaran = document.getElementById('statusPembayaran').value;
    
    let metodePembayaran = document.getElementById('metodePembayaran') ? document.getElementById('metodePembayaran').value : 'cash';
    let totalBayar = document.getElementById('totalHarga') ? parseInt(document.getElementById('totalHarga').value.replace('Rp. ', '').replaceAll('.', '')) : 0;
    let jumlahUang = document.getElementById('jumlahUang') ? parseInt(document.getElementById('jumlahUang').value) || 0 : 0;
    let kembalian = jumlahUang - totalBayar;

    let data = new URLSearchParams();
    data.append('kode_pesanan', kodePesanan);
    data.append('status_pembayaran', statusPembayaran);
    data.append('metode_pembayaran', metodePembayaran);
    data.append('total_bayar', metodePembayaran === 'qris' ? totalBayar : jumlahUang);
    data.append('kembalian', metodePembayaran === 'qris' ? 0 : kembalian);

    fetch('update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: data.toString()
    })
    .then(response => response.text())
    .then(data => {
        if (data === 'success') {
            document.getElementById('status-' + kodePesanan).innerHTML = 
                statusPembayaran === "Lunas" 
                ? '<span class="badge bg-success">Lunas</span>' 
                : '<span class="badge bg-warning text-dark">Belum Dibayar</span>';

            alert('Status pembayaran berhasil diperbarui!');

            var modal = bootstrap.Modal.getInstance(document.getElementById('modalEditStatus'));
            modal.hide();
        } else {
            alert('Terjadi kesalahan saat memperbarui status pembayaran: ' + data);
        }
    })
    .catch(error => {
        alert('Gagal terhubung ke server: ' + error);
    });
}


function cetakStruk() {
    window.open('cetak/cetak.php?kode_pesanan=' + currentKodePesanan, '_blank');
}

</script>