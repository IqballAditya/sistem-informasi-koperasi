<?php
/**
 * Modul Laporan Rekapitulasi Koperasi (Fitur 5 & Poin 13, 17, 22-24 SKKNI)
 * 
 * Menampilkan laporan akumulasi simpanan menggunakan MySQL Stored Procedure:
 * CALL rekap_simpanan_anggota(:id_anggota)
 * Dilengkapi fitur ekspor ke Microsoft Excel (.xls) dan Cetak PDF print-ready.
 * 
 * PENJELASAN VARIAN & INVARIAN (Pemenuhan Poin 13 SKKNI):
 * - Varian   : Parameter $id_anggota yang dikirimkan melalui form POST; nilainya berubah-ubah
 *              secara dinamis sesuai pilihan anggota yang ingin dilihat laporannya.
 * - Invarian : Struktur kueri Stored Procedure "CALL rekap_simpanan_anggota(:id)" 
 *              yang sifatnya konstan/tetap serta rumus kalkulasi agregasi SUM(nominal) di database.
 * 
 * PENJELASAN KOMPLEKSITAS ALGORITMA (Pemenuhan Poin 17 SKKNI):
 * - Time Complexity  : O(N), di mana N adalah jumlah baris catatan transaksi simpanan milik anggota.
 *                      Pencarian dioptimalkan dengan Foreign Key Index (id_anggota) di InnoDB engine.
 * - Space Complexity : O(K), di mana K adalah jumlah kategori jenis simpanan (maksimal 3 baris: Pokok, Wajib, Sukarela).
 * 
 * @package    KoperasiSejahtera
 * @subpackage Laporan
 * @author     Iqbal Aditya (Peserta Ujikom)
 * @version    1.2.0
 */

session_start();

// Validasi Sesi Pengguna
if (!isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit;
}

require 'config/koneksi.php';

$hasil_laporan = [];
$pesan = '';
$nama_terpilih = '';
$id_terpilih = 0;

// Ambil daftar anggota untuk dropdown pencarian
try {
    $stmt_anggota = $pdo->query("SELECT id_anggota, nama FROM anggota ORDER BY nama ASC");
    $daftar_anggota = $stmt_anggota->fetchAll();
} catch (PDOException $e) {
    die("Error mengambil data anggota: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

// Proses jika tombol Tampilkan Laporan ditekan
if (isset($_POST['btn_tampil'])) {
    $id_anggota = filter_input(INPUT_POST, 'id_anggota', FILTER_VALIDATE_INT);
    
    if ($id_anggota) {
        try {
            // Eksekusi Stored Procedure yang ada di MySQL
            $sql = "CALL rekap_simpanan_anggota(:id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id_anggota]);
            $hasil_laporan = $stmt->fetchAll();
            $id_terpilih = $id_anggota;
            
            if (empty($hasil_laporan)) {
                $pesan = '<div class="alert alert-warning">Belum ada catatan mutasi simpanan untuk anggota ini.</div>';
            } else {
                $nama_terpilih = $hasil_laporan[0]['nama']; 
            }
        } catch (PDOException $e) {
            $pesan = '<div class="alert alert-danger">Error mengeksekusi Stored Procedure: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
        }
    } else {
        $pesan = '<div class="alert alert-warning">Silakan pilih nama anggota terlebih dahulu!</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Koperasi - Koperasi Sejahtera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Navigasi -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">Koperasi Sejahtera</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="master_anggota.php">Data Master</a></li>
                    <li class="nav-item"><a class="nav-link" href="transaksi.php">Transaksi</a></li>
                    <li class="nav-item"><a class="nav-link" href="simpanan.php">Setor Simpanan</a></li>
                    <li class="nav-item"><a class="nav-link active" href="laporan.php">Laporan</a></li>
                    <li class="nav-item"><a class="nav-link text-warning" href="logout.php" onclick="return confirm('Yakin ingin keluar?');">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">Laporan Rekapitulasi Simpanan</h4>
                <small class="text-muted">Diolah terpusat menggunakan <b>MySQL Stored Procedure</b> (CALL rekap_simpanan_anggota)</small>
            </div>
        </div>
        <hr>

        <!-- Form Filter Anggota -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body bg-white">
                <form method="POST" action="" class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label class="fw-bold">Pilih Anggota:</label>
                    </div>
                    <div class="col-md-6">
                        <select name="id_anggota" class="form-select" required>
                            <option value="">-- Pilih Nama Anggota --</option>
                            <?php foreach ($daftar_anggota as $row): ?>
                                <option value="<?= $row['id_anggota']; ?>" <?= ($id_terpilih == $row['id_anggota']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" name="btn_tampil" class="btn btn-info text-white">
                            Tampilkan Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?= $pesan; ?>

        <!-- Tabel Hasil Laporan -->
        <?php if (!empty($hasil_laporan)): ?>
            <div class="card shadow-sm border-info mb-4">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center py-3">
                    <span class="fs-5">Hasil Rekapitulasi: <b><?= htmlspecialchars($nama_terpilih, ENT_QUOTES, 'UTF-8'); ?></b></span>
                    
                    <div>
                        <a href="export_excel.php?id=<?= $id_terpilih; ?>" class="btn btn-sm btn-success text-white me-2">
                            Download Excel (.xls)
                        </a>
                        <a href="cetak_laporan.php?id=<?= $id_terpilih; ?>" target="_blank" class="btn btn-sm btn-danger text-white">
                            Cetak PDF
                        </a>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <table class="table table-bordered table-striped m-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">No</th>
                                <th>Jenis Simpanan</th>
                                <th class="text-end" style="width: 250px;">Total Akumulasi (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1; 
                            $grand_total = 0;
                            foreach ($hasil_laporan as $baris): 
                                $grand_total += (int)$baris['total'];
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td>Simpanan <?= htmlspecialchars($baris['jenis_simpanan'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-end fw-bold">
                                    Rp <?= number_format($baris['total'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="2" class="text-end">Total Simpanan Keseluruhan :</th>
                                <th class="text-end text-success fs-5">Rp <?= number_format($grand_total, 0, ',', '.'); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap 5 JavaScript Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
