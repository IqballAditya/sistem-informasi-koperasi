<?php
session_start();
if (!isset($_SESSION['id_user']) || !isset($_GET['id'])) {
    exit("Akses ditolak.");
}

require 'config/koneksi.php';
$id_anggota = $_GET['id'];

try {
    $stmt = $pdo->prepare("CALL rekap_simpanan_anggota(:id)");
    $stmt->execute([':id' => $id_anggota]);
    $hasil_laporan = $stmt->fetchAll();
    
    if(empty($hasil_laporan)) exit("Data kosong.");
    $nama_anggota = $hasil_laporan[0]['nama'];

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// MEMANIPULASI HEADER AGAR MENJADI FILE EXCEL
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Rekap_Simpanan_".$nama_anggota.".xls");
?>

<table border="1">
    <tr>
        <th colspan="3">LAPORAN REKAPITULASI SIMPANAN KOPERASI</th>
    </tr>
    <tr>
        <th colspan="3">Nama Anggota: <?= htmlspecialchars($nama_anggota); ?></th>
    </tr>
    <tr>
        <th>No</th>
        <th>Jenis Simpanan</th>
        <th>Total Akumulasi (Rp)</th>
    </tr>
    <?php $no = 1; foreach ($hasil_laporan as $baris): ?>
    <tr>
        <td><?= $no++; ?></td>
        <td>Simpanan <?= htmlspecialchars($baris['jenis_simpanan']); ?></td>
        <td><?= $baris['total']; ?></td>
    </tr>
    <?php endforeach; ?>
</table>