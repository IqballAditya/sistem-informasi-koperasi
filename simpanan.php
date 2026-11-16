<?php
/**
 * ====================================================================
 * NAMA MODUL    : Modul Transaksi Simpanan
 * DESKRIPSI     : Menerima input form simpanan dan menyimpannya ke tabel 'simpanan'.
 *                 Trigger MySQL akan otomatis menambah 'total_simpanan' di tabel 'anggota'.
 * PARAMETER     : $_POST['id_anggota'], $_POST['jenis_simpanan'], $_POST['nominal']
 * ALGORITMA     : 1. Terima data dari form.
 *                 2. Siapkan query INSERT menggunakan PDO Prepare Statement.
 *                 3. Eksekusi query. Tampilkan pesan sukses.
 * ====================================================================
 */

// Memanggil file koneksi database
require 'config/koneksi.php';

$pesan = "";

// Cek apakah tombol submit ditekan
if (isset($_POST['submit_simpanan'])) {
    // Menangkap data dari form
    $id_anggota = $_POST['id_anggota'];
    $id_user = 1; // Diasumsikan Kasir 1 yang sedang login
    $jenis_simpanan = $_POST['jenis_simpanan'];
    $nominal = $_POST['nominal'];
    $tgl_simpan = date('Y-m-d'); // Tanggal hari ini

    try {
        // Query DML (Data Manipulation Language)
        $sql = "INSERT INTO simpanan (id_anggota, id_user, jenis_simpanan, nominal, tgl_simpan) 
                VALUES (:id_anggota, :id_user, :jenis, :nominal, :tgl)";
        
        // Menggunakan prepared statement untuk keamanan dari SQL Injection
        $stmt = $pdo->prepare($sql);
        
        // Mengeksekusi query dengan parameter
        $stmt->execute([
            ':id_anggota' => $id_anggota,
            ':id_user' => $id_user,
            ':jenis' => $jenis_simpanan,
            ':nominal' => $nominal,
            ':tgl' => $tgl_simpan
        ]);

        $pesan = "Data simpanan berhasil dicatat! Saldo anggota otomatis bertambah.";
    } catch (PDOException $e) {
        $pesan = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi Simpanan</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 20px;">

    <h2>Form Transaksi Simpanan Koperasi</h2>
    
    <!-- Notifikasi jika proses berhasil/gagal -->
    <?php if($pesan != ""): ?>
        <div style="background: #e2f3e5; padding: 10px; border: 1px solid #28a745; margin-bottom: 15px;">
            <b>Info:</b> <?= $pesan; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label>ID Anggota:</label><br>
        <!-- Asumsi anggota dengan ID 1 (Iqbal) sudah kita buat saat di phpMyAdmin tadi -->
        <input type="number" name="id_anggota" value="1" required><br><br>

        <label>Jenis Simpanan:</label><br>
        <select name="jenis_simpanan" required>
            <option value="Pokok">Simpanan Pokok</option>
            <option value="Wajib">Simpanan Wajib</option>
            <option value="Sukarela">Simpanan Sukarela</option>
        </select><br><br>

        <label>Nominal (Rp):</label><br>
        <input type="number" name="nominal" required placeholder="Misal: 100000"><br><br>

        <button type="submit" name="submit_simpanan">Simpan Transaksi</button>
    </form>

</body>
</html>