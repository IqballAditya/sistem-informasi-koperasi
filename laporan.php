<?php
session_start();

// Validasi sesi login
if (!isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit;
}

require 'config/koneksi.php';

/**
 * ====================================================================
 * NAMA MODUL    : Menu Laporan (Fitur 5)
 * DESKRIPSI     : Menampilkan rekapitulasi data menggunakan Stored Procedure SQL.
 * 
 * PENJELASAN VARIAN & INVARIAN (Pemenuhan Poin 13 SKKNI) :
 * - Varian   : Variabel $id_anggota (nilainya berubah-ubah sesuai pilihan input user).
 * - Invarian : Struktur pemanggilan query "CALL rekap_simpanan_anggota(:id)" 
 *              yang sifatnya tetap dan tidak akan berubah selama eksekusi.
 * 
 * KOMPLEKSITAS ALGORITMA (Pemenuhan Poin 17 SKKNI) :
 * - Time Complexity : O(N), di mana N adalah jumlah baris hasil eksekusi (fetch) 
 *   dari Stored Procedure di database. Karena menggunakan index, pencariannya cepat.
 * ====================================================================
 */

$hasil_laporan = [];
$pesan = '';
$nama_terpilih = '';

// Ambil daftar anggota untuk dropdown pencarian
try {
    $stmt_anggota = $pdo->query("SELECT id_anggota, nama FROM anggota ORDER BY nama ASC");
    $daftar_anggota = $stmt_anggota->fetchAll();
} catch (PDOException $e) {
    die("Error mengambil data anggota: " . $e->getMessage());
}

// Proses jika tombol Tampilkan Laporan ditekan
if (isset($_POST['btn_tampil'])) {
    $id_anggota = $_POST['id_anggota'];
    
    try {
        // Eksekusi Stored Procedure yang ada di MySQL
        $sql = "CALL rekap_simpanan_anggota(:id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id_anggota]);
        $hasil_laporan = $stmt->fetchAll();
        
        if (empty($hasil_laporan)) {
            $pesan = '<div class="alert alert-warning">Belum ada data simpanan terperinci untuk anggota ini.</div>';
        } else {
            // Mengambil nama anggota untuk judul tabel
            $nama_terpilih = $hasil_laporan[0]['nama']; 
        }
    } catch (PDOException $e) {
        $pesan = '<div class="alert alert-danger">Error mengeksekusi Stored Procedure: ' . $e->getMessage() . '</div>';
    }
}
?>



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Koperasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Navigasi -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Koperasi Sejahtera</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="master_anggota.php">Data Master</a></li>
                    <li class="nav-item"><a class="nav-link" href="transaksi.php">Transaksi</a></li>
                    <li class="nav-item"><a class="nav-link active" href="laporan.php">Laporan</a></li>
                    <li class="nav-item"><a class="nav-link text-warning" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h4>Laporan Rekapitulasi Simpanan</h4>
        <p class="text-muted">Data di bawah ini di-generate langsung menggunakan eksekusi <b>Stored Procedure</b> MySQL.</p>
        <hr>

        <div class="card shadow-sm mb-4">
            <div class="card-body bg-white">
                <form method="POST" action="" class="d-flex align-items-center">
                    <label class="me-3 fw-bold">Pilih Anggota:</label>
                    <select name="id_anggota" class="form-select w-50 me-3" required>
                        <option value="">-- Pilih Nama Anggota --</option>
                        <?php foreach ($daftar_anggota as $row): ?>
                            <option value="<?= $row['id_anggota']; ?>">
                                <?= htmlspecialchars($row['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="btn_tampil" class="btn btn-info text-white">Tampilkan Laporan</button>
                </form>
            </div>
        </div>

        <?= $pesan; ?>

        <!-- Tabel Hasil Laporan -->
        <?php if (!empty($hasil_laporan)): ?>
            <div class="card shadow-sm border-info">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <span>Hasil Rekapitulasi: <b><?= htmlspecialchars($nama_terpilih); ?></b></span>
                    
                    <!-- TOMBOL EXCEL DAN PDF DITAMBAHKAN DI SINI -->
                    <div>
                        <a href="export_excel.php?id=<?= $id_anggota; ?>" class="btn btn-sm btn-success text-white me-2">Download Excel</a>
                        <a href="cetak_laporan.php?id=<?= $id_anggota; ?>" target="_blank" class="btn btn-sm btn-danger text-white">Cetak PDF</a>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <table class="table table-bordered table-striped m-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Jenis Simpanan</th>
                                <th>Total Akumulasi (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($hasil_laporan as $baris): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td>Simpanan <?= htmlspecialchars($baris['jenis_simpanan']); ?></td>
                                <td class="text-end fw-bold">
                                    <?= number_format($baris['total'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>

