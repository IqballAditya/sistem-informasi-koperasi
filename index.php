<?php
/**
 * Modul Otentikasi Pengguna (Poin 10-11 SKKNI)
 * 
 * Memvalidasi hak akses petugas/kasir untuk masuk ke dalam sistem.
 * 
 * ALUR LOGIKA PEMROGRAMAN (ALGORITMA) :
 * 1. Terima input 'username' dan 'password' dari form login.
 * 2. Lakukan pencarian user menggunakan PDO Prepared Statement (:username) - Mencegah SQL Injection.
 * 3. Jika user ditemukan, verifikasi kecocokan password menggunakan password_verify() (Bcrypt).
 * 4. Jika password masih berupa plaintext lama, lakukan auto-hash migrasi ke bcrypt.
 * 5. Jika valid, buat Session id_user & username lalu redirect ke dashboard.php.
 * 6. Jika tidak valid, tampilkan notifikasi error yang disanitasi menggunakan htmlspecialchars() - Mencegah XSS.
 * 
 * @package    KoperasiSejahtera
 * @subpackage Auth
 * @author     Iqbal Aditya (Peserta Ujikom)
 * @version    1.1.0
 */

// Memulai sesi untuk menyimpan status login
session_start();

// Memanggil file koneksi database
require 'config/koneksi.php';

// Jika sudah login, langsung alihkan ke dashboard
if (isset($_SESSION['id_user'])) {
    header("Location: dashboard.php");
    exit;
}

$pesan_error = '';

// Mengecek apakah tombol login ditekan
if (isset($_POST['btn_login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $pesan_error = "Username dan Password wajib diisi!";
    } else {
        try {
            // Menerapkan akses basis data dengan Prepared Statement (Poin 10-11)
            $sql = "SELECT id_user, username, password FROM users WHERE username = :username";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            // Implementasi Algoritma Validasi & Keamanan Enkripsi
            if ($user) {
                $password_valid = false;

                // Verifikasi hash Bcrypt standar
                if (password_verify($password, $user['password'])) {
                    $password_valid = true;
                } 
                // Kompatibilitas mundur: jika di database masih berupa plaintext lama, auto-migrasi ke bcrypt
                elseif ($password === $user['password']) {
                    $password_valid = true;
                    $hash_baru = password_hash($password, PASSWORD_BCRYPT);
                    $stmt_migrasi = $pdo->prepare("UPDATE users SET password = :hash WHERE id_user = :id");
                    $stmt_migrasi->execute([':hash' => $hash_baru, ':id' => $user['id_user']]);
                }

                if ($password_valid) {
                    // Set variabel session petugas
                    $_SESSION['id_user'] = $user['id_user'];
                    $_SESSION['username'] = $user['username'];
                    
                    // Arahkan ke halaman Dashboard
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $pesan_error = "Validasi gagal: Username atau Password salah!";
                }
            } else {
                $pesan_error = "Validasi gagal: Username atau Password salah!";
            }
        } catch (PDOException $e) {
            // Penanganan Exception (Debugging terstruktur)
            $pesan_error = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Koperasi Sejahtera Mandiri</title>
    <!-- Menggunakan Framework CSS Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h4 class="mb-0">Login Koperasi</h4>
                        <small>Sejahtera Mandiri</small>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- Menampilkan pesan error jika login gagal (Pencegahan XSS dengan htmlspecialchars) -->
                        <?php if($pesan_error != ''): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= htmlspecialchars($pesan_error, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Form Input Login -->
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Username</label>
                                <input type="text" name="username" class="form-control" required placeholder="Masukkan username" autocomplete="username">
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Password</label>
                                <input type="password" name="password" class="form-control" required placeholder="Masukkan password" autocomplete="current-password">
                            </div>
                            <button type="submit" name="btn_login" class="btn btn-primary w-100 py-2">Masuk Sistem</button>
                        </form>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
