<?php
/**
 * Modul Ekspor Laporan ke Excel (Fitur 5)
 * 
 * Melakukan manipulasi HTTP Header Content-Type untuk menghasilkan file spreadsheet Excel (.xls)
 * langsung dari hasil eksekusi Stored Procedure basis data.
 * 
 * @package    KoperasiSejahtera
 * @subpackage Export
 * @author     Iqbal Aditya (Peserta Ujikom)
 * @version    1.2.0
 */

session_start();

// Validasi Keamanan Sesi dan Parameter
if (!isset($_SESSION['id_user']) || !isset($_GET['id'])) {
    exit("Akses ditolak: Anda harus login terlebih dahulu.");
}

require 'config/koneksi.php';

$id_anggota = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_anggota) {
    exit("Parameter ID Anggota tidak valid.");
}

try {
    // Memanggil Stored Procedure rekap_simpanan_anggota
    $stmt = $pdo->prepare("CALL rekap_simpanan_anggota(:id)");
    $stmt->execute([':id' => $id_anggota]);
    $hasil_laporan = $stmt->fetchAll();
    
    if (empty($hasil_laporan)) {
        exit("Data laporan kosong untuk anggota ini.");
    }

    $nama_anggota = $hasil_laporan[0]['nama'];

} catch (PDOException $e) {
    die("Terjadi kesalahan: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

// Manipulasi HTTP Header untuk Ekspor File Excel
$clean_nama = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nama_anggota);
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Rekap_Simpanan_" . $clean_nama . ".xls");
header("Pragma: no-cache");
header("Expires: 0");
?>

<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th colspan="3" style="background-color: #0d6efd; color: #ffffff; font-size: 16px;">
            KOPERASI SEJAHTERA MANDIRI
        </th>
    </tr>
    <tr>
        <th colspan="3">LAPORAN REKAPITULASI SIMPANAN ANGGOTA</th>
    </tr>
    <tr>
        <td colspan="3"><b>Nama Anggota:</b> <?= htmlspecialchars($nama_anggota, ENT_QUOTES, 'UTF-8'); ?></td>
    </tr>
    <tr>
        <td colspan="3"><b>Tanggal Unduh:</b> <?= date('d-m-Y H:i:s'); ?></td>
    </tr>
    <tr style="background-color: #e9ecef;">
        <th>No</th>
        <th>Jenis Simpanan</th>
        <th>Total Akumulasi (Rp)</th>
    </tr>
    <?php 
    $no = 1; 
    $total_seluruh = 0;
    foreach ($hasil_laporan as $baris): 
        $total_seluruh += (int)$baris['total'];
    ?>
    <tr>
        <td align="center"><?= $no++; ?></td>
        <td>Simpanan <?= htmlspecialchars($baris['jenis_simpanan'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td align="right"><?= $baris['total']; ?></td>
    </tr>
    <?php endforeach; ?>
    <tr style="background-color: #d1e7dd;">
        <th colspan="2" align="right">Total Keseluruhan :</th>
        <th align="right"><?= $total_seluruh; ?></th>
    </tr>
</table>
