<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit;
}

require 'config/koneksi.php';

/**
 * ====================================================================
 * NAMA MODUL    : Menu Transaksi (Fitur 4) - Full Transaksi
 * DESKRIPSI     : Mengelola input Simpanan, Pinjaman, dan Angsuran.
 *                 Telah terintegrasi dengan Trigger MySQL.
 * ====================================================================
 */

$pesan = '';
$id_user = $_SESSION['id_user']; 

// 1. Proses Eksekusi Simpanan
if (isset($_POST['btn_simpanan'])) {
    try {
        $sql = "INSERT INTO simpanan (id_anggota, id_user, jenis_simpanan, nominal, tgl_simpan) 
                VALUES (:id_anggota, :id_user, :jenis, :nominal, :tgl)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_anggota' => $_POST['id_anggota'],
            ':id_user' => $id_user,
            ':jenis' => $_POST['jenis_simpanan'],
            ':nominal' => $_POST['nominal'],
            ':tgl' => date('Y-m-d')
        ]);
        $pesan = '<div class="alert alert-success">Transaksi Simpanan dicatat! Trigger bekerja.</div>';
    } catch (PDOException $e) {
        $pesan = '<div class="alert alert-danger">Error Simpanan: ' . $e->getMessage() . '</div>';
    }
}

// 2. Proses Eksekusi Pinjaman (Pembiayaan)
if (isset($_POST['btn_pinjaman'])) {
    try {
        $nominal = $_POST['nominal_pinjaman'];
        $bunga = $_POST['bunga'];
        $tenor = $_POST['tenor'];
        
        // Memanggil Function MySQL (hitung_total_bunga) yang sudah kita buat sebelumnya
        // Total Hutang = Pinjaman Pokok + (Nominal * Bunga% * Tenor)
        $sql_bunga = "SELECT hitung_total_bunga(:nom, :bunga, :tenor) AS total_bunga";
        $stmt_bunga = $pdo->prepare($sql_bunga);
        $stmt_bunga->execute([':nom' => $nominal, ':bunga' => $bunga, ':tenor' => $tenor]);
        $hasil_bunga = $stmt_bunga->fetch();
        
        $sisa_tagihan = $nominal + $hasil_bunga['total_bunga'];

        $sql = "INSERT INTO pinjaman (id_anggota, id_user, nominal_pinjaman, tenor, bunga, sisa_tagihan, status) 
                VALUES (:id_anggota, :id_user, :nominal, :tenor, :bunga, :sisa, 'Berjalan')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_anggota' => $_POST['id_anggota'],
            ':id_user' => $id_user,
            ':nominal' => $nominal,
            ':tenor' => $tenor,
            ':bunga' => $bunga,
            ':sisa' => $sisa_tagihan
        ]);
        $pesan = '<div class="alert alert-primary">Pinjaman disetujui! Tagihan total (termasuk bunga tetap) otomatis dihitung.</div>';
    } catch (PDOException $e) {
        $pesan = '<div class="alert alert-danger">Error Pinjaman: ' . $e->getMessage() . '</div>';
    }
}

// 3. Proses Eksekusi Pembayaran Angsuran
if (isset($_POST['btn_angsuran'])) {
    try {
        $sql = "INSERT INTO angsuran (id_pinjaman, id_user, nominal_bayar, tgl_bayar) 
                VALUES (:id_pinjaman, :id_user, :nominal, :tgl)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_pinjaman' => $_POST['id_pinjaman'],
            ':id_user' => $id_user,
            ':nominal' => $_POST['nominal_bayar'],
            ':tgl' => date('Y-m-d')
        ]);
        $pesan = '<div class="alert alert-info">Angsuran dibayar! Trigger otomatis memotong sisa tagihan.</div>';
    } catch (PDOException $e) {
        $pesan = '<div class="alert alert-danger">Error Angsuran: ' . $e->getMessage() . '</div>';
    }
}

// Mengambil referensi data untuk dropdown
$daftar_anggota = $pdo->query("SELECT id_anggota, nama FROM anggota")->fetchAll();
$daftar_pinjaman = $pdo->query("SELECT p.id_pinjaman, a.nama, p.sisa_tagihan FROM pinjaman p JOIN anggota a ON p.id_anggota = a.id_anggota WHERE p.status = 'Berjalan'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Menu Transaksi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Koperasi Sejahtera</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="master_anggota.php">Data Master</a></li>
                    <li class="nav-item"><a class="nav-link active" href="transaksi.php">Transaksi</a></li>
                    <li class="nav-item"><a class="nav-link" href="laporan.php">Laporan</a></li>
                    <li class="nav-item"><a class="nav-link text-warning" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h4>Menu Transaksi Koperasi</h4>
        <hr>
        <?= $pesan; ?>

        <div class="row">
            <!-- 1. Form Simpanan -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-success h-100">
                    <div class="card-header bg-success text-white">1. Transaksi Simpanan</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label>Anggota</label>
                                <select name="id_anggota" class="form-select" required>
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($daftar_anggota as $row): ?>
                                        <option value="<?= $row['id_anggota']; ?>"><?= htmlspecialchars($row['nama']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Jenis</label>
                                <select name="jenis_simpanan" class="form-select" required>
                                    <option value="Pokok">Pokok</option>
                                    <option value="Wajib">Wajib</option>
                                    <option value="Sukarela">Sukarela</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Nominal (Rp)</label>
                                <input type="number" name="nominal" class="form-control" required>
                            </div>
                            <button type="submit" name="btn_simpanan" class="btn btn-success w-100">Proses Simpanan</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 2. Form Pinjaman (Pembiayaan) -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-primary h-100">
                    <div class="card-header bg-primary text-white">2. Pengajuan Pinjaman</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label>Anggota</label>
                                <select name="id_anggota" class="form-select" required>
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($daftar_anggota as $row): ?>
                                        <option value="<?= $row['id_anggota']; ?>"><?= htmlspecialchars($row['nama']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Nominal Pinjaman Pokok</label>
                                <input type="number" name="nominal_pinjaman" class="form-control" required>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label>Bunga Tetap (%)</label>
                                    <input type="number" step="0.1" name="bunga" class="form-control" placeholder="Misal: 1.5" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label>Tenor (Bulan)</label>
                                    <input type="number" name="tenor" class="form-control" placeholder="Misal: 12" required>
                                </div>
                            </div>
                            <button type="submit" name="btn_pinjaman" class="btn btn-primary w-100">Proses Pinjaman</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 3. Form Angsuran -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-info h-100">
                    <div class="card-header bg-info text-white">3. Bayar Angsuran</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label>Pilih Data Pinjaman</label>
                                <select name="id_pinjaman" class="form-select" required>
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($daftar_pinjaman as $row): ?>
                                        <option value="<?= $row['id_pinjaman']; ?>">
                                            <?= htmlspecialchars($row['nama']); ?> (Sisa: Rp <?= number_format($row['sisa_tagihan'],0,',','.'); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Nominal Bayar (Rp)</label>
                                <input type="number" name="nominal_bayar" class="form-control" required>
                            </div>
                            <button type="submit" name="btn_angsuran" class="btn btn-info w-100 text-white">Proses Angsuran</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>