<?php
session_start();

// Validasi Keamanan: Mencegah bypass URL (Harus login dulu)
if (!isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit;
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Koperasi Sejahtera</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="master_anggota.php">Data Master</a></li>
                    <li class="nav-item"><a class="nav-link" href="transaksi.php">Transaksi</a></li>
                    <li class="nav-item"><a class="nav-link" href="laporan.php">Laporan</a></li>
                    <li class="nav-item">
                        <a class="nav-link text-warning" href="logout.php" onclick="return confirm('Yakin ingin keluar?');">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Konten Utama Dashboard -->
    <div class="container mt-5">
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h2>Selamat Datang, Petugas <b><?= $_SESSION['username']; ?></b>!</h2>
                <p class="text-muted">Pilih menu di bawah ini untuk mengelola operasional Koperasi.</p>
            </div>
        </div>

        <div class="row">
            <!-- Menu Data Master -->
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h4 class="card-title text-primary">Data Master</h4>
                        <p class="card-text">Kelola data pendaftaran Anggota Koperasi.</p>
                        <a href="master_anggota.php" class="btn btn-outline-primary">Buka Menu</a>
                    </div>
                </div>
            </div>
            
            <!-- Menu Transaksi -->
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h4 class="card-title text-success">Transaksi</h4>
                        <p class="card-text">Input Simpanan, Pinjaman, dan Pembayaran Angsuran.</p>
                        <a href="transaksi.php" class="btn btn-outline-success">Buka Menu</a>
                    </div>
                </div>
            </div>

            <!-- Menu Laporan -->
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h4 class="card-title text-info">Laporan</h4>
                        <p class="card-text">Lihat rekapitulasi data menggunakan Stored Procedure SQL.</p>
                        <a href="laporan.php" class="btn btn-outline-info">Buka Menu</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>