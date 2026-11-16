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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan - <?= $nama_anggota; ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
    </style>
</head>
<body>

    <div class="header">
        <h2>KOPERASI SEJAHTERA MANDIRI</h2>
        <p>Laporan Rekapitulasi Simpanan Anggota</p>
    </div>

    <p><b>Nama Anggota :</b> <?= htmlspecialchars($nama_anggota); ?></p>
    <p><b>Tanggal Cetak :</b> <?= date('d-m-Y'); ?></p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Jenis Simpanan</th>
                <th class="text-right">Total Akumulasi (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($hasil_laporan as $baris): ?>
            <tr>
                <td><?= $no++; ?></td>
                <td>Simpanan <?= htmlspecialchars($baris['jenis_simpanan']); ?></td>
                <td class="text-right"><?= number_format($baris['total'], 0, ',', '.'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- SCRIPT OTOMATIS MEMUNCULKAN DIALOG PRINT / SAVE AS PDF -->
    <script>
        window.print();
    </script>

</body>
</html>