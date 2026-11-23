<?php
/**
 * Modul Cetak Laporan PDF / Print Ready Layout (Fitur 5)
 * 
 * Menghasilkan tampilan laporan cetak dokumen yang rapi dan memicu dialog pencetakan browser otomatis (window.print).
 * 
 * @package    KoperasiSejahtera
 * @subpackage Cetak
 * @author     Iqbal Aditya (Peserta Ujikom)
 * @version    1.2.0
 */

session_start();

// Validasi Keamanan Sesi
if (!isset($_SESSION['id_user']) || !isset($_GET['id'])) {
    exit("Akses ditolak: Anda harus login terlebih dahulu.");
}

require 'config/koneksi.php';

$id_anggota = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_anggota) {
    exit("Parameter ID Anggota tidak valid.");
}

try {
    $stmt = $pdo->prepare("CALL rekap_simpanan_anggota(:id)");
    $stmt->execute([':id' => $id_anggota]);
    $hasil_laporan = $stmt->fetchAll();
    
    if (empty($hasil_laporan)) {
        exit("Data laporan kosong.");
    }

    $nama_anggota = $hasil_laporan[0]['nama'];

} catch (PDOException $e) {
    die("Terjadi kesalahan: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan - <?= htmlspecialchars($nama_anggota, ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 25px; color: #333; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #000; padding-bottom: 12px; }
        .header h2 { margin: 0 0 5px 0; letter-spacing: 1px; }
        .header p { margin: 0; font-size: 14px; color: #666; }
        .info-table { width: 100%; margin-bottom: 20px; font-size: 14px; }
        .info-table td { padding: 4px 0; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 10px; text-align: left; }
        table.data-table th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer-ttd { margin-top: 50px; float: right; width: 220px; text-align: center; font-size: 14px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>KOPERASI SEJAHTERA MANDIRI</h2>
        <p>Laporan Rekapitulasi Akumulasi Simpanan Anggota</p>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 140px;"><b>Nama Anggota</b></td>
            <td>: <?= htmlspecialchars($nama_anggota, ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
        <tr>
            <td><b>Tanggal Cetak</b></td>
            <td>: <?= date('d-m-Y H:i'); ?> WIB</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 50px;">No</th>
                <th>Jenis Simpanan</th>
                <th class="text-right" style="width: 220px;">Total Akumulasi (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1; 
            $grand_total = 0;
            foreach ($hasil_laporan as $baris): 
                $grand_total += (int)$baris['total'];
            ?>
            <tr>
                <td class="text-center"><?= $no++; ?></td>
                <td>Simpanan <?= htmlspecialchars($baris['jenis_simpanan'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="text-right"><?= number_format($baris['total'], 0, ',', '.'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #fafafa;">
                <td colspan="2" class="text-right">Total Simpanan Keseluruhan :</td>
                <td class="text-right">Rp <?= number_format($grand_total, 0, ',', '.'); ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-ttd">
        <p>Tangerang Selatan, <?= date('d F Y'); ?></p>
        <p>Petugas Kasir,</p>
        <br><br><br>
        <p><b>( <?= htmlspecialchars($_SESSION['username'] ?? 'Petugas', ENT_QUOTES, 'UTF-8'); ?> )</b></p>
    </div>

    <!-- Script Otomatis Membuka Dialog Print Browser -->
    <script>
        window.print();
    </script>

</body>
</html>
