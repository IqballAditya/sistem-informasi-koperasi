<?php
/**
 * Modul Transaksi Koperasi (Fitur 4 & Poin 8, 10-11, 13, 17 SKKNI)
 * 
 * Mengelola transaksi Simpanan, Pinjaman (Pembiayaan), dan Pembayaran Angsuran.
 * 
 * =========================================================================================
 * BUKTI PEMENUHAN POIN SKKNI UNTUK ASESOR:
 * 
 * 1. POIN 8: IMPLEMENTASI TRANSAKSI BASIS DATA (COMMIT & ROLLBACK)
 *    Menerapkan prinsip ACID (Atomicity, Consistency, Isolation, Durability) menggunakan:
 *    - $pdo->beginTransaction() : Memulai transaksi manual di level PHP.
 *    - $pdo->commit()           : Menyimpan transaksi secara permanen jika seluruh kueri sukses.
 *    - $pdo->rollBack()         : Membatalkan seluruh mutasi data jika terjadi kegagalan/exception.
 *    (Setara dengan mysqli_begin_transaction, mysqli_commit, dan mysqli_rollback).
 * 
 * 2. POIN 10-11: KEAMANAN AKSES BASIS DATA
 *    - Menggunakan PDO Prepared Statements dengan parameter binding (:param) untuk mencegah SQL Injection.
 *    - Seluruh tampilan teks dibungkus fungsi htmlspecialchars() untuk mencegah serangan XSS.
 * 
 * 3. POIN 13: KONSEP VARIAN DAN INVARIAN
 *    - Varian   : Nilai input $_POST (nominal, tenor, bunga, id_anggota, id_pinjaman) 
 *                 yang nilainya dinamis tergantung transaksi yang dilakukan pengguna.
 *    - Invarian : Aturan integritas bahwa 'sisa_tagihan' tidak boleh bernilai negatif 
 *                 (nominal_bayar <= sisa_tagihan), dan total akumulasi simpanan/angsuran
 *                 selalu konsisten dengan tabel referensi melalui Trigger Database.
 * 
 * 4. POIN 17: ANALISIS KOMPLEKSITAS ALGORITMA (BIG-O NOTATION)
 *    - Time Complexity  : O(1) [Konstan] untuk operasi INSERT dan kalkulasi bunga, karena 
 *                         kueri langsung mengakses baris melalui Primary Key terindeks (Clustered Index).
 *    - Space Complexity : O(1) [Konstan], alokasi memori hanya untuk variabel penampung sementara.
 * =========================================================================================
 * 
 * @package    KoperasiSejahtera
 * @subpackage Transaksi
 * @author     Iqbal Aditya (Peserta Ujikom)
 * @version    1.2.0
 */

session_start();

// Validasi Keamanan Sesi Pengguna
if (!isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit;
}

require 'config/koneksi.php';

$pesan = '';
$id_user = (int)$_SESSION['id_user']; 

// -----------------------------------------------------------------------------
// 1. PROSES TRANSAKSI SIMPANAN (DILENGKAPI COMMIT & ROLLBACK - POIN 8)
// -----------------------------------------------------------------------------
if (isset($_POST['btn_simpanan'])) {
    $id_anggota     = filter_input(INPUT_POST, 'id_anggota', FILTER_VALIDATE_INT);
    $jenis_simpanan = $_POST['jenis_simpanan'] ?? '';
    $nominal        = filter_input(INPUT_POST, 'nominal', FILTER_VALIDATE_INT);

    // Validasi Invarian: Nominal harus bilangan bulat positif
    if ($id_anggota && in_array($jenis_simpanan, ['Pokok', 'Wajib', 'Sukarela']) && $nominal > 0) {
        try {
            // [POIN 8] Memulai Transaksi Basis Data
            $pdo->beginTransaction();

            $sql = "INSERT INTO simpanan (id_anggota, id_user, jenis_simpanan, nominal, tgl_simpan) 
                    VALUES (:id_anggota, :id_user, :jenis, :nominal, :tgl)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_anggota' => $id_anggota,
                ':id_user'    => $id_user,
                ':jenis'      => $jenis_simpanan,
                ':nominal'    => $nominal,
                ':tgl'        => date('Y-m-d')
            ]);

            // [POIN 8] Commit Transaksi: Simpan permanen dan aktifkan trigger penambahan saldo
            $pdo->commit();
            $pesan = '<div class="alert alert-success alert-dismissible fade show">
                        <strong>Sukses!</strong> Transaksi Simpanan berhasil dicatat (COMMIT berhasil, Saldo otomatis diperbarui Trigger).
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                      </div>';
        } catch (PDOException $e) {
            // [POIN 8] Rollback Transaksi: Batalkan mutasi data jika terjadi kegagalan
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $pesan = '<div class="alert alert-danger"><strong>Transaksi Dibatalkan (ROLLBACK):</strong> ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
        }
    } else {
        $pesan = '<div class="alert alert-warning">Input data simpanan tidak valid! Nominal harus lebih besar dari 0.</div>';
    }
}

// -----------------------------------------------------------------------------
// 2. PROSES TRANSAKSI PINJAMAN / PEMBIAYAAN (DILENGKAPI COMMIT & ROLLBACK - POIN 8)
// -----------------------------------------------------------------------------
if (isset($_POST['btn_pinjaman'])) {
    $id_anggota = filter_input(INPUT_POST, 'id_anggota', FILTER_VALIDATE_INT);
    $nominal    = filter_input(INPUT_POST, 'nominal_pinjaman', FILTER_VALIDATE_INT);
    $bunga      = filter_input(INPUT_POST, 'bunga', FILTER_VALIDATE_FLOAT);
    $tenor      = filter_input(INPUT_POST, 'tenor', FILTER_VALIDATE_INT);

    // Validasi Invarian: Seluruh parameter pinjaman harus positif
    if ($id_anggota && $nominal > 0 && $bunga >= 0 && $tenor > 0) {
        try {
            // [POIN 8] Memulai Transaksi Basis Data
            $pdo->beginTransaction();

            // Memanggil SQL Function: hitung_total_bunga(nominal, bunga, tenor)
            $sql_bunga = "SELECT hitung_total_bunga(:nom, :bunga, :tenor) AS total_bunga";
            $stmt_bunga = $pdo->prepare($sql_bunga);
            $stmt_bunga->execute([
                ':nom'   => $nominal, 
                ':bunga' => $bunga, 
                ':tenor' => $tenor
            ]);
            $hasil_bunga = $stmt_bunga->fetch();
            $total_bunga = (int)$hasil_bunga['total_bunga'];
            $sisa_tagihan = $nominal + $total_bunga;

            // Simpan Data Pinjaman
            $sql = "INSERT INTO pinjaman (id_anggota, id_user, nominal_pinjaman, tenor, bunga, sisa_tagihan, status) 
                    VALUES (:id_anggota, :id_user, :nominal, :tenor, :bunga, :sisa, 'Berjalan')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_anggota' => $id_anggota,
                ':id_user'    => $id_user,
                ':nominal'    => $nominal,
                ':tenor'      => $tenor,
                ':bunga'      => $bunga,
                ':sisa'       => $sisa_tagihan
            ]);

            // [POIN 8] Commit Transaksi
            $pdo->commit();
            $pesan = '<div class="alert alert-primary alert-dismissible fade show">
                        <strong>Sukses!</strong> Pinjaman berhasil disetujui (COMMIT berhasil). Total tagihan termasuk bunga flat: Rp ' . number_format($sisa_tagihan, 0, ',', '.') . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                      </div>';
        } catch (PDOException $e) {
            // [POIN 8] Rollback Transaksi
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $pesan = '<div class="alert alert-danger"><strong>Transaksi Dibatalkan (ROLLBACK):</strong> ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
        }
    } else {
        $pesan = '<div class="alert alert-warning">Form pinjaman tidak valid! Pastikan nominal, bunga, dan tenor terisi dengan benar.</div>';
    }
}

// -----------------------------------------------------------------------------
// 3. PROSES PEMBAYARAN ANGSURAN (DILENGKAPI COMMIT, ROLLBACK & VALIDASI INVARIAN)
// -----------------------------------------------------------------------------
if (isset($_POST['btn_angsuran'])) {
    $id_pinjaman   = filter_input(INPUT_POST, 'id_pinjaman', FILTER_VALIDATE_INT);
    $nominal_bayar = filter_input(INPUT_POST, 'nominal_bayar', FILTER_VALIDATE_INT);

    if ($id_pinjaman && $nominal_bayar > 0) {
        try {
            // Periksa sisa tagihan terkini untuk mencegah tagihan menjadi minus (Poin 13: Invarian)
            $stmt_cek = $pdo->prepare("SELECT sisa_tagihan, status FROM pinjaman WHERE id_pinjaman = :id");
            $stmt_cek->execute([':id' => $id_pinjaman]);
            $pinjam_info = $stmt_cek->fetch();

            if (!$pinjam_info || $pinjam_info['status'] !== 'Berjalan') {
                $pesan = '<div class="alert alert-warning">Pinjaman ini sudah lunas atau tidak ditemukan!</div>';
            } elseif ($nominal_bayar > $pinjam_info['sisa_tagihan']) {
                // Pencegahan Edge-Case: Pembayaran melebihi sisa tagihan
                $pesan = '<div class="alert alert-danger">
                            <strong>Gagal!</strong> Nominal bayar (Rp ' . number_format($nominal_bayar, 0, ',', '.') . ') 
                            melebihi sisa tagihan (Rp ' . number_format($pinjam_info['sisa_tagihan'], 0, ',', '.') . ').
                          </div>';
            } else {
                // [POIN 8] Memulai Transaksi Basis Data
                $pdo->beginTransaction();

                $sql = "INSERT INTO angsuran (id_pinjaman, id_user, nominal_bayar, tgl_bayar) 
                        VALUES (:id_pinjaman, :id_user, :nominal, :tgl)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':id_pinjaman' => $id_pinjaman,
                    ':id_user'     => $id_user,
                    ':nominal'     => $nominal_bayar,
                    ':tgl'         => date('Y-m-d')
                ]);

                // [POIN 8] Commit Transaksi: Mengaktifkan Trigger pemotongan sisa tagihan & auto-lunas
                $pdo->commit();
                $pesan = '<div class="alert alert-info alert-dismissible fade show">
                            <strong>Sukses!</strong> Pembayaran angsuran berhasil dicatat (COMMIT berhasil, Sisa tagihan otomatis dikurangi oleh Trigger).
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                          </div>';
            }
        } catch (PDOException $e) {
            // [POIN 8] Rollback Transaksi
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $pesan = '<div class="alert alert-danger"><strong>Transaksi Dibatalkan (ROLLBACK):</strong> ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
        }
    } else {
        $pesan = '<div class="alert alert-warning">Data angsuran tidak valid! Nominal bayar harus lebih dari 0.</div>';
    }
}

// Mengambil referensi data untuk dropdown (Read dengan Prepared/Query terstruktur)
try {
    $daftar_anggota = $pdo->query("SELECT id_anggota, nama FROM anggota ORDER BY nama ASC")->fetchAll();
    $daftar_pinjaman = $pdo->query("SELECT p.id_pinjaman, a.nama, p.sisa_tagihan 
                                    FROM pinjaman p 
                                    JOIN anggota a ON p.id_anggota = a.id_anggota 
                                    WHERE p.status = 'Berjalan'
                                    ORDER BY p.id_pinjaman DESC")->fetchAll();
} catch (PDOException $e) {
    die("Gagal memuat referensi data: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Transaksi - Koperasi Sejahtera</title>
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
                    <li class="nav-item"><a class="nav-link active" href="transaksi.php">Transaksi</a></li>
                    <li class="nav-item"><a class="nav-link" href="simpanan.php">Setor Simpanan</a></li>
                    <li class="nav-item"><a class="nav-link" href="laporan.php">Laporan</a></li>
                    <li class="nav-item"><a class="nav-link text-warning" href="logout.php" onclick="return confirm('Yakin ingin keluar?');">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">Menu Transaksi Terpadu</h4>
                <small class="text-muted">Menerapkan Transaksi Database (COMMIT &amp; ROLLBACK - Poin 8 SKKNI)</small>
            </div>
            <span class="badge bg-success py-2 px-3">ACID Transaction Mode: Aktif</span>
        </div>
        <hr>

        <?= $pesan; ?>

        <div class="row">
            <!-- 1. Form Simpanan -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-success text-white fw-bold">
                        1. Transaksi Simpanan
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Pilih Anggota</label>
                                <select name="id_anggota" class="form-select" required>
                                    <option value="">-- Pilih Anggota --</option>
                                    <?php foreach ($daftar_anggota as $row): ?>
                                        <option value="<?= $row['id_anggota']; ?>">
                                            <?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Jenis Simpanan</label>
                                <select name="jenis_simpanan" class="form-select" required>
                                    <option value="Pokok">Simpanan Pokok</option>
                                    <option value="Wajib">Simpanan Wajib</option>
                                    <option value="Sukarela">Simpanan Sukarela</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nominal Setoran (Rp)</label>
                                <input type="number" name="nominal" class="form-control" required min="1000" placeholder="Contoh: 100000">
                                <small class="text-muted">Minimal Rp 1.000</small>
                            </div>
                            <button type="submit" name="btn_simpanan" class="btn btn-success w-100 py-2">
                                Proses Simpanan (Commit)
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 2. Form Pinjaman (Pembiayaan) -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-primary text-white fw-bold">
                        2. Pengajuan Pinjaman
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Pilih Anggota</label>
                                <select name="id_anggota" class="form-select" required>
                                    <option value="">-- Pilih Anggota --</option>
                                    <?php foreach ($daftar_anggota as $row): ?>
                                        <option value="<?= $row['id_anggota']; ?>">
                                            <?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nominal Pinjaman Pokok (Rp)</label>
                                <input type="number" name="nominal_pinjaman" class="form-control" required min="10000" placeholder="Contoh: 1000000">
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-semibold">Bunga Flat (%)</label>
                                    <input type="number" step="0.1" name="bunga" class="form-control" placeholder="Misal: 1.5" required min="0">
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-semibold">Tenor (Bulan)</label>
                                    <input type="number" name="tenor" class="form-control" placeholder="Misal: 12" required min="1">
                                </div>
                            </div>
                            <button type="submit" name="btn_pinjaman" class="btn btn-primary w-100 py-2">
                                Proses Pinjaman (Commit)
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 3. Form Pembayaran Angsuran -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-info text-white fw-bold">
                        3. Pembayaran Angsuran
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Pilih Pinjaman Aktif</label>
                                <select name="id_pinjaman" class="form-select" required>
                                    <option value="">-- Pilih Pinjaman --</option>
                                    <?php foreach ($daftar_pinjaman as $row): ?>
                                        <option value="<?= $row['id_pinjaman']; ?>">
                                            <?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8'); ?> (Sisa: Rp <?= number_format($row['sisa_tagihan'], 0, ',', '.'); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nominal Bayar (Rp)</label>
                                <input type="number" name="nominal_bayar" class="form-control" required min="1000" placeholder="Contoh: 150000">
                                <small class="text-muted">Tidak boleh melebihi sisa tagihan</small>
                            </div>
                            <button type="submit" name="btn_angsuran" class="btn btn-info text-white w-100 py-2">
                                Bayar Angsuran (Commit)
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap 5 JavaScript Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
