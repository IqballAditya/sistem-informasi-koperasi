<?php
/**
 * Modul Logout Pengguna (Poin 22-24 SKKNI)
 * 
 * Menghancurkan seluruh data sesi (session_destroy) dan mengalihkan pengguna kembali ke halaman login.
 * 
 * @package    KoperasiSejahtera
 * @subpackage Auth
 * @author     Iqbal Aditya (Peserta Ujikom)
 * @version    1.2.0
 */

session_start();
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
header("Location: index.php");
exit;
