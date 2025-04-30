<table class="table table-bordered table-hover" style="margin-top: 100px; background-color: #f5f5dc;">
    <thead class="text-bg-success">
        <tr>
            <th>No</th>
            <th>Kode Pesanan</th>
            <th>Nama Pelanggan</th>
            <th>Kode Menu</th>
            <th>Qty</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 1; foreach ($menu as $m) { ?>
            <tr style="background-color: #D2B48C;"> <!-- Warna coklat muda untuk baris tabel -->
                <td><?= $i; ?></td>
                <td><?= htmlspecialchars($m["kode_pesanan"]); ?></td>
                <td><?= htmlspecialchars($m["nama_pelanggan"]); ?></td>
                <td><?= htmlspecialchars($m["kode_menu"]); ?></td>
                <td><?= htmlspecialchars($m["qty"]); ?></td>
            </tr>
        <?php $i++; } ?>
    </tbody>
</table>

<style>
    .text-bg-success {
        background-color: #8B4513; /* Warna coklat tua untuk header tabel */
        color: white; /* Warna teks putih untuk kontras */
    }
    .table-bordered th, .table-bordered td {
        border: 1px solid #8B4513; /* Border coklat untuk tabel */
    }
    .table-hover tbody tr:hover {
        background-color: #A0522D; /* Warna coklat saat hover */
        color: white; /* Warna teks putih saat hover */
    }
</style>