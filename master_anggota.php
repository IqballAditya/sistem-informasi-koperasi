<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit;
}

require 'config/koneksi.php';

/**
 * ====================================================================
 * NAMA MODUL    : Master Data Anggota (Fitur 3) - Full CRUD
 * DESKRIPSI     : Mengelola data keanggotaan Koperasi (Create, Read, Delete).
 * ALGORITMA     : 
 *   1. Jika form disubmit, eksekusi INSERT ke tabel 'anggota'.
 *   2. Jika parameter '?hapus=id' diterima, eksekusi DELETE.
 *   3. Jalankan SELECT untuk menampilkan seluruh daftar anggota.
 * ====================================================================
 */

$pesan = '';

// 1. Logika untuk Menambah Data (Create)
if (isset($_POST['btn_simpan'])) {
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    try {
        $sql_insert = "INSERT INTO anggota (nama, alamat, total_simpanan) VALUES (:nama, :alamat, 0)";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([':nama' => $nama, ':alamat' => $alamat]);
        $pesan = '<div class="alert alert-success">Anggota baru berhasil didaftarkan!</div>';
    } catch (PDOException $e) {
        $pesan = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}

// 2. Logika untuk Menghapus Data (Delete)
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    try {
        $sql_delete = "DELETE FROM anggota WHERE id_anggota = :id";
        $stmt_delete = $pdo->prepare($sql_delete);
        $stmt_delete->execute([':id' => $id_hapus]);
        
        $pesan = '<div class="alert alert-warning">Data anggota berhasil dihapus beserta histori transaksinya (Cascade).</div>';
    } catch (PDOException $e) {
        $pesan = '<div class="alert alert-danger">Gagal menghapus data: ' . $e->getMessage() . '</div>';
    }
}

// 3. Logika untuk Menampilkan Data (Read)
try {
    $sql_select = "SELECT * FROM anggota ORDER BY id_anggota DESC";
    $stmt_select = $pdo->query($sql_select);
    $daftar_anggota = $stmt_select->fetchAll();
} catch (PDOException $e) {
    die("Gagal mengambil data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Master Anggota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Koperasi Sejahtera</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="master_anggota.php">Data Master</a></li>
                    <li class="nav-item"><a class="nav-link" href="transaksi.php">Transaksi</a></li>
                    <li class="nav-item"><a class="nav-link" href="laporan.php">Laporan</a></li>
                    <li class="nav-item"><a class="nav-link text-warning" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h4>Kelola Data Master Anggota</h4>
        <hr>
        
        <?= $pesan; ?>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">Tambah Anggota Baru</div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Alamat Lengkap</label>
                                <textarea name="alamat" class="form-control" rows="3" required></textarea>
                            </div>
                            <button type="submit" name="btn_simpan" class="btn btn-primary w-100">Simpan Data</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">Daftar Anggota Koperasi</div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-hover m-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Anggota</th>
                                    <th>Alamat</th>
                                    <th>Total Simpanan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($daftar_anggota as $row): ?>
                                <tr>
                                    <td><?= $row['id_anggota']; ?></td>
                                    <td><?= htmlspecialchars($row['nama']); ?></td>
                                    <td><?= htmlspecialchars($row['alamat']); ?></td>
                                    <td>Rp <?= number_format($row['total_simpanan'], 0, ',', '.'); ?></td>
                                    <td>
                                        <!-- Tombol Hapus dengan Konfirmasi -->
                                        <a href="?hapus=<?= $row['id_anggota']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Yakin ingin menghapus anggota ini? Data transaksi simpanan/pinjamannya juga akan hilang otomatis.');">
                                           Hapus
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>