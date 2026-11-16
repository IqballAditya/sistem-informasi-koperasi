# Sistem Informasi Koperasi "Sejahtera Mandiri"

Aplikasi pencatatan keuangan mikro (Simpanan, Pinjaman, dan Angsuran) berbasis Web yang dikembangkan menggunakan PHP Native dan MySQL. Proyek ini dibangun sebagai penyelesaian portofolio Uji Kompetensi Keahlian (LSP) Skema Analis Pemrograman.

## 🚀 Fitur Utama
- **Otentikasi & Keamanan:** Login kasir dengan arsitektur PDO (PHP Data Objects) Prepared Statements untuk mencegah SQL Injection.
- **Manajemen Data (CRUD):** Pengelolaan data master anggota dilengkapi relasi *ON DELETE CASCADE*.
- **Otomatisasi Database (Backend):** 
  - **Trigger MySQL:** Penambahan saldo simpanan dan pengurangan tagihan berjalan otomatis di level database.
  - **Function SQL:** Algoritma kalkulasi bunga pinjaman tetap (flat) secara dinamis.
  - **Stored Procedure:** Pengolahan rekapitulasi data laporan yang cepat dan terpusat.
- **Ekspor Laporan:** Fitur unduh laporan terintegrasi dalam format Excel (.xls) dan Cetak PDF (print-ready layout).

## 🛠️ Teknologi yang Digunakan
- **Bahasa:** PHP 8+ (Native)
- **Database:** MySQL / MariaDB (InnoDB Engine)
- **Frontend:** HTML5, CSS3, Bootstrap 5
- **Konsep:** Single Role Architecture, Exception Handling (Try-Catch Debugging)

## 📂 Cara Instalasi (Deployment)
1. Clone repositori ini ke folder `htdocs` (XAMPP).
2. Buat database baru di phpMyAdmin dengan nama `db_koperasi`.
3. Import file `database/db_koperasi.sql` ke dalam database tersebut (seluruh struktur tabel, trigger, dan procedure sudah termasuk di dalamnya).
4. Akses aplikasi melalui browser di `http://localhost/koperasi/`.
5. Gunakan akun default untuk masuk:
   - **Username:** kasir1
   - **Password:** 12345