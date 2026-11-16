<?php
// Memulai sesi untuk menyimpan status login
session_start();

// Memanggil file koneksi database
require 'config/koneksi.php';

/**
 * ====================================================================
 * NAMA MODUL    : Modul Otentikasi Login (Fitur 1)
 * DESKRIPSI     : Memvalidasi hak akses petugas/kasir untuk masuk ke sistem.
 * 
 * ALUR LOGIKA PEMROGRAMAN (ALGORITMA) :
 * 1. Terima input 'username' dan 'password' dari form.
 * 2. Lakukan pencarian di tabel 'users' menggunakan PDO Prepared Statement.
 * 3. Jika username ditemukan, cocokkan password-nya.
 * 4. Jika valid, buat Session dan alihkan (redirect) ke dashboard.php.
 * 5. Jika tidak valid, tampilkan notifikasi error ke layar (Debugging).
 * ====================================================================
 */

$pesan_error = '';

// Mengecek apakah tombol login ditekan
if (isset($_POST['btn_login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Menerapkan akses basis data untuk mencari user
        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        // Implementasi Algoritma Validasi (Kondisional)
        if ($user && $password === $user['password']) {
            // Jika sukses, set variabel session
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            
            // Arahkan ke halaman Dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            $pesan_error = "Validasi gagal: Username atau Password salah!";
        }
    } catch (PDOException $e) {
        // Melakukan penangkapan error (Debugging)
        $pesan_error = "Terjadi kesalahan sistem: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Koperasi Sejahtera Mandiri</title>
    <!-- Menggunakan Framework CSS Bootstrap agar tampilan langsung rapi -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Login Koperasi</h4>
                        <small>Sejahtera Mandiri</small>
                    </div>
                    <div class="card-body">
                        
                        <!-- Menampilkan pesan error jika login gagal -->
                        <?php if($pesan_error != ''): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= $pesan_error; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Form Input Login -->
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required placeholder="Masukkan username">
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required placeholder="Masukkan password">
                            </div>
                            <button type="submit" name="btn_login" class="btn btn-primary w-100">Masuk Sistem</button>
                        </form>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>