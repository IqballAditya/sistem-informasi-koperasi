<?php
/**
 * ====================================================================
 * NAMA MODUL    : Koneksi Database
 * DESKRIPSI     : Menerapkan akses basis data dengan PDO (PHP Data Objects).
 *                 Memastikan keamanan koneksi dengan ERRMODE_EXCEPTION.
 * PEMBUAT       : Iqbal Aditya (Peserta Ujikom)
 * ====================================================================
 */

$host = 'localhost';
$dbname = 'db_koperasi';
$user = 'root';
$pass = '';

try {
    // Membuat instance PDO untuk koneksi
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    
    // Konfigurasi error mode agar exception/error mudah dilacak
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Menampilkan pesan jika koneksi gagal
    die("Koneksi Database Gagal: " . $e->getMessage());
}
?>