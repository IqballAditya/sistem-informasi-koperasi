<?php
/**
 * Modul Transaksi Simpanan Mandiri (Fitur Khusus & Poin 8, 10-11 SKKNI)
 * 
 * Modul terdedikasi untuk mencatat transaksi setoran simpanan anggota.
 * Telah disesuaikan dengan standar asesmen:
 * - Poin 8: Menerapkan transaksi basis data manual ($pdo->beginTransaction, commit, rollBack).
 * - Poin 10-11: Menggunakan PDO Prepared Statement dan proteksi XSS dengan htmlspecialchars().
 * - Keamanan Sesi: Memeriksa autentikasi session kasir/petugas.
 * 
 * @package    KoperasiSejahtera
 * @subpackage Simpanan
 * @author     Iqbal Aditya (Peserta Ujikom)
 * @version    1.2.0
 */

session_start();

// Validasi Keamanan: Harus login terlebih dahulu
if (!isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit;
}

require 'config/koneksi.php';

$pesan = "";
$id_user = (int)$_SESSION['id_user'];

// Proses saat tombol submit ditekan
if (isset($_POST['submit_simpanan'])) {
    $id_anggota     = filter_input(INPUT_POST, 'id_anggota', FILTER_VALIDATE_INT);
    $jenis_simpanan = $_POST['jenis_simpanan'] ?? '';
    $nominal        = filter_input(INPUT_POST, 'nominal', FILTER_VALIDATE_INT);
    $tgl_simpan     = date('Y-m-d');

    if ($id_anggota && in_array($jenis_simpanan, ['Pokok', 'Wajib', 'Sukarela']) && $nominal > 0) {
        try {
            // [POIN 8 SKKNI] Memulai Transaksi Basis Data Manual (Setara mysqli_begin_transaction)
            $pdo->beginTransaction();

            $sql = "INSERT INTO simpanan (id_anggota, id_user, jenis_simpanan, nominal, tgl_simpan) 
                    VALUES (:id_anggota, :id_user, :jenis, :nominal, :tgl)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_anggota' => $id_anggota,
                ':id_user'    => $id_user,
                ':jenis'      => $jenis_simpanan,
                ':nominal'    => $nominal,
                ':tgl'        => $tgl_simpan
            ]);

            // [POIN 8 SKKNI] Commit: Permanenkan transaksi dan Trigger otomatis mengupdate saldo anggota
            $pdo->commit();

            $pesan = '<div class="alert alert-success alert-dismissible fade show">
                        <strong>Berhasil!</strong> Setoran simpanan sebesar Rp ' . number_format($nominal, 0, ',', '.') . ' telah dicatat.
                        (Status: COMMIT Berhasil, Saldo anggota otomatis diperbarui oleh Trigger).
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                      </div>';
        } catch (PDOException $e) {
            // [POIN 8 SKKNI] Rollback: Batalkan perubahan jika ada kegagalan query
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $pesan = '<div class="alert alert-danger">
                        <strong>Transaksi Dibatalkan (ROLLBACK):</strong> ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '
                      </div>';
        }
    } else {
        $pesan = '<div class="alert alert-warning">Data tidak valid! Pastikan nominal bernilai positif.</div>';
    }
}

// Ambil daftar anggota untuk dropdown
try {
    $stmt_anggota = $pdo->query("SELECT id_anggota, nama, total_simpanan FROM anggota ORDER BY nama ASC");
    $daftar_anggota = $stmt_anggota->fetchAll();
} catch (PDOException $e) {
    die("Gagal memuat data anggota: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setor Simpanan - Koperasi Sejahtera</title>
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
                    <li class="nav-item"><a class="nav-link active" href="simpanan.php">Setor Simpanan</a></li>
                    <li class="nav-item"><a class="nav-link" href="laporan.php">Laporan</a></li>
                    <li class="nav-item"><a class="nav-link text-warning" href="logout.php" onclick="return confirm('Yakin ingin keluar?');">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-success text-white py-3">
                        <h5 class="mb-0">Form Setoran Simpanan Anggota</h5>
                        <small>Implementasi Transaksi Basis Data (Poin 8 SKKNI)</small>
                    </div>
                    <div class="card-body p-4">

                        <?= $pesan; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Pilih Anggota</label>
                                <select name="id_anggota" class="form-select" required>
                                    <option value="">-- Pilih Anggota --</option>
                                    <?php foreach ($daftar_anggota as $row): ?>
                                        <option value="<?= $row['id_anggota']; ?>">
                                            <?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8'); ?> 
                                            (Saldo: Rp <?= number_format($row['total_simpanan'], 0, ',', '.'); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Jenis Simpanan</label>
                                <select name="jenis_simpanan" class="form-select" required>
                                    <option value="Pokok">Simpanan Pokok</option>
                                    <option value="Wajib">Simpanan Wajib</option>
                                    <option value="Sukarela">Simpanan Sukarela</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Nominal Setoran (Rp)</label>
                                <input type="number" name="nominal" class="form-control" required min="1000" placeholder="Contoh: 100000">
                                <small class="text-muted">Minimal nominal Rp 1.000</small>
                            </div>

                            <button type="submit" name="submit_simpanan" class="btn btn-success w-100 py-2">
                                Simpan Transaksi (Eksekusi COMMIT)
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
