<?php
/**
 * Modul Master Data Anggota (Fitur 3 & Poin 10-11, 13 SKKNI)
 * 
 * Mengelola data anggota koperasi secara penuh (Full CRUD: Create, Read, Update, Delete).
 * Dilengkapi relasi ON DELETE CASCADE di level database untuk menjaga integritas data.
 * 
 * PENJELASAN VARIAN & INVARIAN (Pemenuhan Poin 13 SKKNI):
 * - Varian   : Nilai input $_POST['nama'], $_POST['alamat'], dan parameter $_GET['hapus']
 *              yang berubah secara dinamis sesuai interaksi pengguna.
 * - Invarian : Struktur query Prepared Statement (:nama, :alamat, :id) yang tetap,
 *              serta kondisi bahwa id_anggota bersifat unik (Primary Key) dan total_simpanan
 *              hanya dimanipulasi melalui transaksi simpanan.
 * 
 * @package    KoperasiSejahtera
 * @subpackage MasterData
 * @author     Iqbal Aditya (Peserta Ujikom)
 * @version    1.2.0
 */

session_start();

// Validasi Keamanan: Cegah bypass URL
if (!isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit;
}

require 'config/koneksi.php';

$pesan = '';

// 1. Logika untuk Menambah Data Anggota Baru (Create - Poin 10-11)
if (isset($_POST['btn_simpan'])) {
    $nama = trim($_POST['nama'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if (!empty($nama) && !empty($alamat)) {
        try {
            $sql_insert = "INSERT INTO anggota (nama, alamat, total_simpanan) VALUES (:nama, :alamat, 0)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([
                ':nama'   => $nama,
                ':alamat' => $alamat
            ]);
            $pesan = '<div class="alert alert-success alert-dismissible fade show">Anggota baru berhasil didaftarkan!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        } catch (PDOException $e) {
            $pesan = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
        }
    } else {
        $pesan = '<div class="alert alert-warning">Nama dan Alamat wajib diisi!</div>';
    }
}

// 2. Logika untuk Mengubah/Memperbarui Data Anggota (Update - Melengkapi Full CRUD)
if (isset($_POST['btn_update'])) {
    $id_anggota = filter_input(INPUT_POST, 'id_anggota', FILTER_VALIDATE_INT);
    $nama = trim($_POST['nama'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if ($id_anggota && !empty($nama) && !empty($alamat)) {
        try {
            $sql_update = "UPDATE anggota SET nama = :nama, alamat = :alamat WHERE id_anggota = :id";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([
                ':nama'   => $nama,
                ':alamat' => $alamat,
                ':id'     => $id_anggota
            ]);
            $pesan = '<div class="alert alert-success alert-dismissible fade show">Data anggota berhasil diperbarui!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        } catch (PDOException $e) {
            $pesan = '<div class="alert alert-danger">Gagal memperbarui data: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
        }
    } else {
        $pesan = '<div class="alert alert-warning">Data pembaharuan tidak valid!</div>';
    }
}

// 3. Logika untuk Menghapus Data Anggota (Delete - Poin 10-11)
if (isset($_GET['hapus'])) {
    $id_hapus = filter_input(INPUT_GET, 'hapus', FILTER_VALIDATE_INT);
    if ($id_hapus) {
        try {
            $sql_delete = "DELETE FROM anggota WHERE id_anggota = :id";
            $stmt_delete = $pdo->prepare($sql_delete);
            $stmt_delete->execute([':id' => $id_hapus]);
            
            $pesan = '<div class="alert alert-warning alert-dismissible fade show">Data anggota berhasil dihapus beserta histori transaksinya (Cascade).<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        } catch (PDOException $e) {
            $pesan = '<div class="alert alert-danger">Gagal menghapus data: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
        }
    }
}

// 4. Logika untuk Menampilkan Seluruh Data Anggota (Read - Poin 10-11)
try {
    $sql_select = "SELECT id_anggota, nama, alamat, total_simpanan FROM anggota ORDER BY id_anggota DESC";
    $stmt_select = $pdo->query($sql_select);
    $daftar_anggota = $stmt_select->fetchAll();
} catch (PDOException $e) {
    die("Gagal mengambil data: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Master Anggota - Koperasi Sejahtera</title>
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
                    <li class="nav-item"><a class="nav-link active" href="master_anggota.php">Data Master</a></li>
                    <li class="nav-item"><a class="nav-link" href="transaksi.php">Transaksi</a></li>
                    <li class="nav-item"><a class="nav-link" href="laporan.php">Laporan</a></li>
                    <li class="nav-item"><a class="nav-link text-warning" href="logout.php" onclick="return confirm('Yakin ingin keluar?');">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">Kelola Data Master Anggota</h4>
                <small class="text-muted">Manajemen Keanggotaan Penuh (Create, Read, Update, Delete)</small>
            </div>
            <span class="badge bg-primary fs-6">Total: <?= count($daftar_anggota); ?> Anggota</span>
        </div>
        <hr>
        
        <?= $pesan; ?>

        <div class="row">
            <!-- Form Tambah Anggota (Create) -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-secondary text-white fw-bold">
                        Tambah Anggota Baru
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control" required placeholder="Contoh: Ahmad Fauzi">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Alamat Lengkap</label>
                                <textarea name="alamat" class="form-control" rows="3" required placeholder="Contoh: Jl. Merdeka No. 45"></textarea>
                            </div>
                            <button type="submit" name="btn_simpan" class="btn btn-primary w-100">
                                Simpan Anggota Baru
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabel Daftar Anggota (Read, Update, Delete) -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-secondary text-white fw-bold d-flex justify-content-between align-items-center">
                        <span>Daftar Anggota Koperasi</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover m-0 align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 60px;">ID</th>
                                        <th>Nama Anggota</th>
                                        <th>Alamat</th>
                                        <th>Total Simpanan</th>
                                        <th style="width: 160px;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($daftar_anggota)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data anggota.</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($daftar_anggota as $row): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary">#<?= $row['id_anggota']; ?></span></td>
                                            <td class="fw-semibold"><?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($row['alamat'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-success fw-bold">Rp <?= number_format($row['total_simpanan'], 0, ',', '.'); ?></td>
                                            <td class="text-center">
                                                <!-- Tombol Edit (Memicu Modal Bootstrap) -->
                                                <button type="button" 
                                                        class="btn btn-sm btn-warning text-dark me-1" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalEdit<?= $row['id_anggota']; ?>">
                                                    Edit
                                                </button>

                                                <!-- Tombol Hapus dengan Konfirmasi -->
                                                <a href="?hapus=<?= $row['id_anggota']; ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Yakin ingin menghapus anggota ini? Data transaksi simpanan/pinjamannya juga akan hilang otomatis (Cascade).');">
                                                   Hapus
                                                </a>
                                            </td>
                                        </tr>

                                        <!-- Modal Edit Anggota (Update - Pemenuhan Full CRUD) -->
                                        <div class="modal fade" id="modalEdit<?= $row['id_anggota']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="">
                                                        <div class="modal-header bg-warning">
                                                            <h5 class="modal-title text-dark">Edit Data Anggota #<?= $row['id_anggota']; ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body text-start">
                                                            <input type="hidden" name="id_anggota" value="<?= $row['id_anggota']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Nama Lengkap</label>
                                                                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Alamat Lengkap</label>
                                                                <textarea name="alamat" class="form-control" rows="3" required><?= htmlspecialchars($row['alamat'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" name="btn_update" class="btn btn-warning text-dark">Simpan Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Akhir Modal Edit -->

                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JavaScript Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
