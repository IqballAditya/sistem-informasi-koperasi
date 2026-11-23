<?php
/**
 * Modul Dashboard Utama Koperasi (Fitur 2)
 * 
 * Menampilkan ringkasan data metrik operasional koperasi secara real-time:
 * - Total Anggota Terdaftar
 * - Total Saldo Simpanan Terkumpul
 * - Total Pinjaman Berjalan & Sisa Tagihan
 * Dilengkapi menu navigasi cepat ke seluruh modul sistem.
 * 
 * @package    KoperasiSejahtera
 * @subpackage Dashboard
 * @author     Iqbal Aditya (Peserta Ujikom)
 * @version    1.2.0
 */

session_start();

// Validasi Keamanan: Mencegah bypass URL
if (!isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit;
}

require 'config/koneksi.php';

// Mengambil Metrik Ringkasan dari Basis Data
try {
    // 1. Total Anggota
    $total_anggota = (int)$pdo->query("SELECT COUNT(*) FROM anggota")->fetchColumn();

    // 2. Total Kas Simpanan Terkumpul
    $total_simpanan = (int)$pdo->query("SELECT COALESCE(SUM(total_simpanan), 0) FROM anggota")->fetchColumn();

    // 3. Pinjaman Aktif & Sisa Tagihan
    $stmt_pinjam = $pdo->query("SELECT COUNT(*) AS total_aktif, COALESCE(SUM(sisa_tagihan), 0) AS total_tagihan FROM pinjaman WHERE status = 'Berjalan'");
    $info_pinjaman = $stmt_pinjam->fetch();
    $pinjaman_aktif = (int)$info_pinjaman['total_aktif'];
    $sisa_tagihan_global = (int)$info_pinjaman['total_tagihan'];

} catch (PDOException $e) {
    die("Gagal memuat data metrik: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Koperasi Sejahtera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Navigasi / Menu Aplikasi -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">Koperasi Sejahtera</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="master_anggota.php">Data Master</a></li>
                    <li class="nav-item"><a class="nav-link" href="transaksi.php">Transaksi</a></li>
                    <li class="nav-item"><a class="nav-link" href="simpanan.php">Setor Simpanan</a></li>
                    <li class="nav-item"><a class="nav-link" href="laporan.php">Laporan</a></li>
                    <li class="nav-item">
                        <a class="nav-link text-warning" href="logout.php" onclick="return confirm('Yakin ingin keluar?');">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Konten Utama Dashboard -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="p-4 bg-white rounded shadow-sm border-start border-primary border-4">
                    <h3 class="mb-1">Selamat Datang, <b><?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></b>!</h3>
                    <p class="text-muted mb-0">Sistem Informasi Pengelolaan Koperasi Sejahtera Mandiri - Portofolio Ujikom Analis Pemrograman.</p>
                </div>
            </div>
        </div>

        <!-- Kartu Metrik Ringkasan Real-Time -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 bg-primary text-white h-100">
                    <div class="card-body">
                        <h6 class="text-uppercase fw-semibold text-white-50">Total Anggota</h6>
                        <h2 class="fw-bold mb-0"><?= number_format($total_anggota, 0, ',', '.'); ?></h2>
                        <small>Orang terdaftar</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 bg-success text-white h-100">
                    <div class="card-body">
                        <h6 class="text-uppercase fw-semibold text-white-50">Total Simpanan</h6>
                        <h2 class="fw-bold mb-0">Rp <?= number_format($total_simpanan, 0, ',', '.'); ?></h2>
                        <small>Akumulasi saldo kas</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 bg-info text-white h-100">
                    <div class="card-body">
                        <h6 class="text-uppercase fw-semibold text-white-50">Pinjaman Berjalan</h6>
                        <h2 class="fw-bold mb-0"><?= number_format($pinjaman_aktif, 0, ',', '.'); ?> <span class="fs-6 fw-normal">Akun</span></h2>
                        <small>Sisa tagihan: Rp <?= number_format($sisa_tagihan_global, 0, ',', '.'); ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Operasional Koperasi -->
        <h5 class="fw-bold mb-3">Modul Operasional</h5>
        <div class="row">
            <!-- Menu Data Master -->
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-primary fw-bold">Data Master</h5>
                        <p class="card-text text-muted small">Kelola pendaftaran anggota (Full CRUD).</p>
                        <a href="master_anggota.php" class="btn btn-outline-primary w-100">Buka Modul</a>
                    </div>
                </div>
            </div>
            
            <!-- Menu Transaksi Terpadu -->
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-success fw-bold">Transaksi</h5>
                        <p class="card-text text-muted small">Simpanan, Pinjaman &amp; Bayar Angsuran (ACID).</p>
                        <a href="transaksi.php" class="btn btn-outline-success w-100">Buka Modul</a>
                    </div>
                </div>
            </div>

            <!-- Menu Simpanan Khusus -->
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-warning fw-bold">Setor Simpanan</h5>
                        <p class="card-text text-muted small">Input cepat setoran simpanan anggota.</p>
                        <a href="simpanan.php" class="btn btn-outline-warning text-dark w-100">Buka Modul</a>
                    </div>
                </div>
            </div>

            <!-- Menu Laporan -->
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-info fw-bold">Laporan</h5>
                        <p class="card-text text-muted small">Rekapitulasi SQL Stored Procedure &amp; Cetak.</p>
                        <a href="laporan.php" class="btn btn-outline-info w-100">Buka Modul</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JavaScript Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
