# Pemenuhan Standar UJIKOM Koperasi Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menyempurnakan seluruh file aplikasi Koperasi "Sejahtera Mandiri" agar memenuhi 100% rubrik Uji Kompetensi Keahlian (Poin 8 Transaksi COMMIT/ROLLBACK, Poin 10-11 Prepared Statements & XSS, Poin 13 & 17 Varian/Invarian/Kompleksitas Algoritma, Poin 22-24 PHPDoc & Dokumentasi Teknis) serta melengkapi fitur CRUD Update tanpa merubah skema database.

**Architecture:** Arsitektur PHP 8 Native berbasis PDO (PHP Data Objects) dengan Prepared Statements, Transaction Management (ACID), sanitasi output XSS, dan antarmuka responsif Bootstrap 5.

**Tech Stack:** PHP 8+, MariaDB/MySQL (InnoDB Engine), HTML5, CSS3, Bootstrap 5 CDN.

**Spec:** [2026-11-23-ujikom-koperasi-enhancement-design.md](file:///C:/xampp/htdocs/koperasi/docs/superpowers/specs/2026-11-23-ujikom-koperasi-enhancement-design.md)

## Global Constraints
* 0% Perubahan skema database DDL (tidak ada query ALTER TABLE / DROP TABLE).
* Seluruh kueri basis data wajib menggunakan PDO Prepared Statements (`:param`).
* Seluruh output teks dinamis wajib dibungkus `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`.
* Kredensial default `kasir1` / `12345` tetap harus dapat login langsung.
* File `simpanan.php` tidak boleh dihapus, melainkan disempurnakan.

## Review Focus
* Kegagalan login dengan password plaintext bawaan `12345` pasca penambahan `password_verify` (ditangani dengan migrasi otomatis/fallback).
* Nilai angsuran melebihi sisa tagihan (ditolak di PHP sebelum kueri dieksekusi).
* Input nominal negatif atau nol (dibatasi `min="1"` di HTML dan validasi di PHP).
* Eksekusi rollback ketika terjadi query error di dalam transaksi PDO.
* Kerusakan relasi foreign key saat Update data anggota (hanya kolom nama dan alamat yang diizinkan untuk diedit).

---

### Task 1: Standarisasi Koneksi & Keamanan Autentikasi Login (config/koneksi.php & index.php)

**Files:**
- Modify: `config/koneksi.php`
- Modify: `index.php`

**Interfaces:**
- Produces: `$pdo` object dengan `ATTR_EMULATE_PREPARES => false`, autentikasi `password_verify()` dan auto-migration password lama.

- [ ] **Step 1: Tulis skrip verifikasi otomatis untuk koneksi & login**
Buat `scratch/test_task1.php` untuk memvalidasi bahwa koneksi PDO siap dan fungsi login memverifikasi password `12345` serta meng-update hash bcrypt ke tabel `users`.

- [ ] **Step 2: Jalankan skrip verifikasi untuk memastikan status awal**
Run: `C:\xampp\php\php.exe scratch/test_task1.php` (unsandboxed jika butuh socket DB).

- [ ] **Step 3: Perbarui `config/koneksi.php`**
Tambahkan header PHPDoc dan setel `$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false)`.

- [ ] **Step 4: Perbarui `index.php`**
Tambahkan header PHPDoc, implementasikan `password_verify()`, auto-hash password plaintext ke bcrypt, sanitasi output `htmlspecialchars()`, dan blok penanda keamanan Poin 10-11.

- [ ] **Step 5: Jalankan skrip verifikasi dan pastikan lolos (PASS)**
Run: `C:\xampp\php\php.exe scratch/test_task1.php`.
Expected: PASS (koneksi sukses, login `kasir1` valid, password terenkripsi bcrypt).

---

### Task 2: Lengkapi CRUD Anggota - Fitur Update / Edit (master_anggota.php)

**Files:**
- Modify: `master_anggota.php`

**Interfaces:**
- Consumes: `$pdo` dari `config/koneksi.php`, session `id_user`.
- Produces: Form modal Edit Anggota dan handler POST `btn_update` yang menjalankan `UPDATE anggota SET nama = :nama, alamat = :alamat WHERE id_anggota = :id`.

- [ ] **Step 1: Tulis skrip uji CRUD Anggota**
Buat `scratch/test_task2.php` yang menguji Create, Read, Update, dan Delete anggota via PDO.

- [ ] **Step 2: Jalankan skrip uji CRUD Anggota**
Run: `C:\xampp\php\php.exe scratch/test_task2.php`.

- [ ] **Step 3: Perbarui `master_anggota.php`**
Tambahkan header PHPDoc, tombol Edit pada tabel anggota, modal Bootstrap 5 untuk edit data, handler POST `btn_update` dengan prepared statement, sanitasi XSS `htmlspecialchars()`, dan penanda Poin 10 & 13.

- [ ] **Step 4: Verifikasi tampilan dan fungsionalitas edit**
Pastikan tombol edit membuka modal dengan data terisi dan form update berhasil mengubah nama/alamat tanpa error.

---

### Task 3: Transaksi Database ACID (COMMIT & ROLLBACK) & Validasi (transaksi.php & simpanan.php)

**Files:**
- Modify: `transaksi.php`
- Modify: `simpanan.php`

**Interfaces:**
- Consumes: `$pdo`, session `id_user`.
- Produces: Blok transaksi `$pdo->beginTransaction()`, `$pdo->commit()`, `$pdo->rollBack()` pada Simpanan, Pinjaman, dan Angsuran; validasi `nominal_bayar <= sisa_tagihan` dan `nominal > 0`.

- [ ] **Step 1: Tulis skrip uji Transaksi ACID**
Buat `scratch/test_task3.php` yang menguji alur commit sukses dan simulasi rollback jika terjadi error.

- [ ] **Step 2: Jalankan skrip uji**
Run: `C:\xampp\php\php.exe scratch/test_task3.php`.

- [ ] **Step 3: Perbarui `transaksi.php`**
Tambahkan header PHPDoc, pasang `$pdo->beginTransaction()`, `$pdo->commit()`, dan `$pdo->rollBack()` di seluruh pemrosesan (Simpanan, Pinjaman, Angsuran). Tambahkan validasi nominal angsuran tidak boleh melebihi sisa tagihan, serta sematkan blok penanda Poin 8, 13 (Varian/Invarian), dan 17 (Kompleksitas Algoritma).

- [ ] **Step 4: Perbarui `simpanan.php`**
Tambahkan header PHPDoc, sesuaikan antarmuka ke Bootstrap 5 + navbar, tambahkan session check, gunakan dropdown nama anggota, pasang transaksi `$pdo->beginTransaction()`, `$pdo->commit()`, dan `$pdo->rollBack()`.

- [ ] **Step 5: Jalankan skrip uji verifikasi**
Run: `C:\xampp\php\php.exe scratch/test_task3.php`.
Expected: PASS (seluruh transaksi commit dan rollback berfungsi dengan benar).

---

### Task 4: Metrik Dashboard & Penyempurnaan Modul Laporan (dashboard.php, laporan.php, export_excel.php, cetak_laporan.php)

**Files:**
- Modify: `dashboard.php`
- Modify: `laporan.php`
- Modify: `export_excel.php`
- Modify: `cetak_laporan.php`

**Interfaces:**
- Consumes: `$pdo`, session `id_user`.
- Produces: Dashboard dengan metrik dinamis (Total Anggota, Total Simpanan, Pinjaman Berjalan), standarisasi PHPDoc pada seluruh modul laporan.

- [ ] **Step 1: Perbarui `dashboard.php`**
Tambahkan header PHPDoc, tambahkan query agregat `COUNT(id_anggota)` as total_anggota, `SUM(total_simpanan)` as total_kas, dan `COUNT(id_pinjaman)` where status='Berjalan' as pinjaman_aktif. Tampilkan pada 3 kartu metrik Bootstrap di bagian atas.

- [ ] **Step 2: Perbarui `laporan.php`, `export_excel.php`, dan `cetak_laporan.php`**
Tambahkan header PHPDoc resmi, perjelas penanda Poin 13 & 17, pastikan sanitasi XSS pada seluruh pencetakan data.

- [ ] **Step 3: Uji jalankan halaman dashboard dan laporan**
Run: `C:\xampp\php\php.exe -l dashboard.php` dan file lainnya untuk memeriksa tidak ada syntax error.

---

### Task 5: Dokumentasi Teknis Standar UJIKOM & Contekan Tanya Jawab Asesor (DOKUMENTASI_TEKNIS.md)

**Files:**
- Create: `DOKUMENTASI_TEKNIS.md`

**Interfaces:**
- Produces: Dokumen lengkap pemenuhan Poin 8, 10-11, 13, 17, 22-24 untuk diserahkan/ditunjukkan ke asesor.

- [ ] **Step 1: Buat file `DOKUMENTASI_TEKNIS.md`**
Tuliskan dokumentasi terstruktur yang berisi:
1. Kamus Modul dan Arsitektur Aplikasi.
2. Kamus Fungsi dan Parameter (PHPDoc Summary).
3. Bukti Implementasi Poin 8 (COMMIT & ROLLBACK di PHP PDO).
4. Bukti Implementasi Poin 10 & 11 (Prepared Statement & Sanitasi XSS).
5. Lembar Contekan Poin 13 (Varian & Invarian) dan Poin 17 (Kompleksitas Algoritma).
6. Struktur Database & Relasi (LRS).

- [ ] **Step 2: Review kelengkapan dokumen teknis**
Pastikan format Markdown rapi dan mudah dibaca langsung di browser atau dicetak.
