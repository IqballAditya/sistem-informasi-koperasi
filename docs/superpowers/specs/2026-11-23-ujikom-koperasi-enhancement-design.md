# Design Doc: Pemenuhan Standar Uji Kompetensi (LSP) Aplikasi Koperasi

**Tanggal:** 2026-11-23  
**Status:** Menunggu Persetujuan  
**Target:** Penyempurnaan Proyek Uji Kompetensi Keahlian (LSP) Analis Pemrograman / Junior Web Developer

---

## 1. Intent & Why
Memastikan seluruh kriteria instrumen asesmen LSP (khususnya Poin 8, 10, 11, 13, 17, dan 22-24) serta standar kelengkapan CRUD dan keamanan autentikasi terpenuhi 100% pada aplikasi Koperasi "Sejahtera Mandiri" tanpa merubah struktur tabel database yang sudah ada.

---

## 2. Goals, Non-Goals & Anti-Goals
* **Goals:**
  * **Poin 8:** Menerapkan transaksi manual database (`$pdo->beginTransaction()`, `$pdo->commit()`, dan `$pdo->rollBack()`) di dalam blok `try-catch` pada `transaksi.php` dan `simpanan.php`.
  * **Poin 10 & 11:** Memastikan seluruh query DML 100% menggunakan PDO Prepared Statements dengan parameter binding (`:param`), melengkapi fungsi **Update (Edit Anggota)** di `master_anggota.php`, mencegah XSS dengan `htmlspecialchars()`, dan memperkuat login dengan `password_hash()` + `password_verify()`.
  * **Poin 13 & 17:** Memberikan penanda komentar khusus dan dokumentasi eksplisit terkait **Varian**, **Invarian**, dan **Kompleksitas Algoritma (Big-O)** yang siap ditunjukkan ke asesor.
  * **Poin 22 - 24:** Standarisasi dokumentasi kode berbasis PHPDoc (`@package`, `@author`, `@param`, `@return`, `@throws`) di seluruh file PHP, serta membuat file dokumentasi teknis `DOKUMENTASI_TEKNIS.md`.
  * **Kerapian:** Menyelaraskan tampilan `simpanan.php` dengan Bootstrap 5 dan proteksi session agar aman dan rapi saat dicek asesor.
* **Non-Goals:**
  * Tidak merubah struktur database (DDL) atau skema tabel (`db_koperasi.sql` tetap mempertahankan struktur kolom, relasi, trigger, function, dan procedure aslinya).
  * Tidak menambahkan library external/framework baru (tetap PHP Native murni + Bootstrap 5 CDN).
* **Anti-Goals:**
  * Menghapus file `simpanan.php` (anti-goal karena file ini secara eksplisit tercantum dalam rubrik pemeriksaan asesor).
  * Merusak data akun login yang sudah ada (`kasir1` / `12345` tetap harus bisa login langsung).

---

## 3. Constraints
* PHP 8+ Native, PDO MySQL, XAMPP environment.
* 0% Perubahan skema database DDL.

---

## 4. Approaches Considered
* **Pendekatan A (Terpilih): Modifikasi Terfokus pada Logika PHP & Dokumentasi Teknis.**
  * Mempertahankan struktur tabel database.
  * Menambahkan blok transaksi `$pdo->beginTransaction()`, `$pdo->commit()`, dan `$pdo->rollBack()`.
  * Menambahkan form modal edit anggota untuk melengkapi CRUD (Create, Read, Update, Delete).
  * Menggunakan `password_verify()` dengan mekanisme auto-hash transparan.
  * Menambahkan PHPDoc dan dokumen `DOKUMENTASI_TEKNIS.md` beserta lembar tanya-jawab varian/invarian/kompleksitas algoritma.
  * *Trade-off:* Paling aman, langsung menjawab rubrik uji tanpa risiko galat database.
* **Pendekatan B (Ditolak): Restrukturisasi Skema Database.**
  * Menambah tabel audit log dan mengubah tabel pinjaman.
  * *Alasan ditolak:* Berisiko merusak database yang sudah di-import di localhost dan memberatkan peserta saat demo.

---

## 5. Rencana Detail Modifikasi File

1. `config/koneksi.php`:
   * Tambahkan header PHPDoc standar.
   * Pastikan konfigurasi PDO ATTR_EMULATE_PREPARES disetel `false` untuk keamanan prepared statements murni.

2. `index.php`:
   * Header PHPDoc lengkap.
   * Implementasi `password_verify()` dengan backward compatibility: jika password di DB masih plaintext (`$password === $user['password']`), otomatis di-rehash ke bcrypt dan disimpan via prepared statement `UPDATE users SET password = :hash`.

3. `master_anggota.php`:
   * Header PHPDoc lengkap.
   * Lengkapi fitur **Update / Edit Anggota** (form modal edit atau form dinamis).
   * Seluruh query DML menggunakan PDO Prepared Statements.
   * Sanitasi XSS menggunakan `htmlspecialchars()`.

4. `simpanan.php`:
   * Header PHPDoc lengkap.
   * Integrasi session check login (`$_SESSION['id_user']`).
   * Desain Bootstrap 5 selaras dengan dashboard.
   * **Implementasi Poin 8:** Transaksi `$pdo->beginTransaction()`, `$pdo->commit()`, dan `$pdo->rollBack()`.

5. `transaksi.php`:
   * Header PHPDoc lengkap.
   * **Implementasi Poin 8:** Transaksi `$pdo->beginTransaction()`, `$pdo->commit()`, dan `$pdo->rollBack()` pada Simpanan, Pinjaman, dan Angsuran.
   * Validasi sisa tagihan: jika `nominal_bayar > sisa_tagihan`, tolak transaksi sebelum eksekusi.
   * Atribut HTML `min="1"` dan validasi PHP nominal > 0.
   * **Penanda Poin 13 & 17:** Komentar Varian, Invarian, dan Kompleksitas Algoritma.

6. `laporan.php`, `cetak_laporan.php`, `export_excel.php`, `dashboard.php`:
   * Tambahkan header PHPDoc standar.
   * Di `dashboard.php`, tampilkan ringkasan 3 kartu metrik dinamis (Total Anggota, Total Simpanan, Pinjaman Aktif).

7. `DOKUMENTASI_TEKNIS.md`:
   * Berisi kamus modul, fungsi, alur parameter, dan tabel lembar tanya-jawab asesor (Varian, Invarian, Kompleksitas Algoritma $O(1)$ dan $O(N)$).
