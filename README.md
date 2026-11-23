# Sistem Informasi Koperasi "Sejahtera Mandiri"

Aplikasi pencatatan keuangan mikro (Simpanan, Pinjaman, dan Angsuran) berbasis Web yang dikembangkan menggunakan PHP Native dan MySQL. Proyek ini dibangun sebagai penyelesaian portofolio Uji Kompetensi Keahlian (LSP) Skema Analis Pemrograman / Junior Web Developer.

---

## 🚀 Fitur Utama & Pemenuhan Standar Ujikom
- **Otentikasi & Keamanan (Poin 10-11):** Login kasir dengan arsitektur PDO Prepared Statements dan enkripsi password Bcrypt (`password_verify` & `password_hash`).
- **Transaksi Basis Data ACID (Poin 8):** Implementasi transaksi manual `$pdo->beginTransaction()`, `$pdo->commit()`, dan `$pdo->rollBack()` di dalam blok `try-catch` untuk menjamin integritas data simpanan, pinjaman, dan angsuran.
- **Manajemen Data Anggota (Full CRUD):** Pengelolaan data anggota lengkap (Create, Read, Update via Modal Bootstrap, dan Delete dengan relasi *ON DELETE CASCADE*).
- **Otomatisasi Database (Backend):** 
  - **Trigger MySQL:** Penambahan saldo simpanan dan pengurangan tagihan berjalan otomatis di level database.
  - **Function SQL:** Algoritma kalkulasi bunga pinjaman tetap (flat) secara dinamis.
  - **Stored Procedure:** Pengolahan rekapitulasi data laporan yang cepat dan terpusat.
- **Teori Pemrograman (Poin 13 & 17):** Pembuktian konsep Varian & Invarian serta analisis Kompleksitas Algoritma $O(1)$ dan $O(N)$.
- **Standar Dokumentasi (Poin 22-24):** Blok komentar terstruktur standar **PHPDoc** di seluruh modul dan fungsi, serta tersedianya dokumen teknis komprehensif `DOKUMENTASI_TEKNIS.md`.
- **Ekspor Laporan:** Fitur unduh laporan terintegrasi dalam format Excel (.xls) dan Cetak PDF (print-ready layout).

---

## 🛠️ Teknologi yang Digunakan
- **Bahasa:** PHP 8+ (Native)
- **Database:** MySQL / MariaDB (InnoDB Engine)
- **Frontend:** HTML5, CSS3, Bootstrap 5.3 CDN
- **Konsep:** Single Role Architecture, Exception Handling (Try-Catch Debugging), ACID Transactions

---

## 📂 Cara Instalasi (Deployment)
1. Clone repositori ini ke folder `htdocs` (XAMPP): `C:\xampp\htdocs\koperasi`.
2. Nyalakan modul **Apache** dan **MySQL** pada XAMPP Control Panel.
3. Buat database baru di phpMyAdmin dengan nama `db_koperasi`.
4. Import file `database/db_koperasi.sql` ke dalam database tersebut.
5. Akses aplikasi melalui browser di `http://localhost/koperasi/`.
6. Gunakan akun default untuk masuk:
   - **Username:** `kasir1`
   - **Password:** `12345`

---

## 📖 Dokumen Teknis & Panduan Asesor
Panduan lengkap jawaban tanya-jawab asesor, letak bukti kode Poin 8, 10-11, 13, 17, 22-24, dan alur demo uji kompetensi tersedia pada file:  
👉 **[DOKUMENTASI_TEKNIS.md](DOKUMENTASI_TEKNIS.md)**
