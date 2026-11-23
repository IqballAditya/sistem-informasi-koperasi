<?php
/**
 * Modul Koneksi Basis Data (Poin 10-11 SKKNI)
 * 
 * Mengelola koneksi database menggunakan PDO (PHP Data Objects).
 * Menerapkan konfigurasi keamanan tinggi:
 * - ERRMODE_EXCEPTION: Melempar PDOException jika terjadi kegagalan query.
 * - ATTR_EMULATE_PREPARES = false: Memastikan Prepared Statements diproses secara native oleh engine MySQL.
 * - ATTR_DEFAULT_FETCH_MODE = FETCH_ASSOC: Memastikan hasil query berupa array asosiatif.
 * 
 * @package    KoperasiSejahtera
 * @subpackage Database
 * @author     Iqbal Aditya (Peserta Ujikom)
 * @version    1.1.0
 */

$host   = 'localhost';
$dbname = 'db_koperasi';
$user   = 'root';
$pass   = '';

try {
    // Membuat instance PDO untuk koneksi ke basis data MySQL
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false, // Prepared statement native untuk mencegah SQL Injection (Poin 10-11)
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);

} catch (PDOException $e) {
    // Menampilkan pesan terstandarisasi jika koneksi gagal (Debugging & Error Handling)
    die("Koneksi Database Gagal: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
