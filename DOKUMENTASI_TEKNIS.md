# DOKUMENTASI TEKNIS SISTEM INFORMASI KOPERASI "SEJAHTERA MANDIRI"
**Standar Kompetensi LSP - Skema Analis Pemrograman / Junior Web Developer**  
**Peserta Ujikom:** Iqbal Aditya  
**Versi Aplikasi:** 1.2.0 (Production / Exam-Ready)

---

## 📋 DAFTAR ISI
1. [Arsitektur & Spesifikasi Sistem](#1-arsitektur--spesifikasi-sistem)
2. [Kamus Modul & Struktur File](#2-kamus-modul--struktur-file)
3. [Panduan & Bukti Pemenuhan Poin Uji Kompetensi](#3-panduan--bukti-pemenuhan-poin-uji-kompetensi)
   - [Poin 8: Transaksi Basis Data (COMMIT & ROLLBACK)](#poin-8-transaksi-basis-data-commit--rollback)
   - [Poin 10 & 11: Keamanan Basis Data & Pencegahan XSS](#poin-10--11-keamanan-basis-data--pencegahan-xss)
   - [Poin 13: Lembar Jawaban Asesor - Konsep Varian & Invarian](#poin-13-lembar-jawaban-asesor---konsep-varian--invarian)
   - [Poin 17: Lembar Jawaban Asesor - Analisis Kompleksitas Algoritma](#poin-17-lembar-jawaban-asesor---analisis-kompleksitas-algoritma)
   - [Poin 22 - 24: Standar Dokumentasi PHPDoc](#poin-22---24-standar-dokumentasi-phpdoc)
4. [Struktur Basis Data & Logika Otomatisasi (Database Objects)](#4-struktur-basis-data--logika-otomatisasi-database-objects)
5. [Panduan Uji Coba Demo di Depan Asesor](#5-panduan-uji-coba-demo-di-depan-asesor)

---

## 1. Arsitektur & Spesifikasi Sistem

| Komponen | Spesifikasi | Keterangan |
| :--- | :--- | :--- |
| **Bahasa Pemrograman** | PHP 8.x Native | Tanpa framework pihak ketiga (Pure Core) |
| **Driver Basis Data** | PDO (PHP Data Objects) | Native Prepared Statements, Exception Mode |
| **DBMS** | MariaDB / MySQL 10.x | Storage Engine: InnoDB (Mendukung ACID) |
| **Frontend Framework** | Bootstrap 5.3 CDN | Tampilan responsif & modern |
| **Standar Dokumentasi** | PHPDoc (DocBlock /** */) | Sesuai SKKNI Poin 22-24 |

---

## 2. Kamus Modul & Struktur File

```
koperasi/
│
├── config/
│   └── koneksi.php          # Koneksi database PDO, konfigurasi error mode & native prepared statements
│
├── database/
│   └── db_koperasi.sql      # Skrip DDL tabel, dummy data, Trigger, Function, dan Stored Procedure
│
├── index.php                # Modul otentikasi login dengan bcrypt (password_verify) & proteksi XSS
├── dashboard.php            # Dashboard utama menampilkan ringkasan metrik real-time & menu
├── master_anggota.php       # Modul Master Anggota dengan Full CRUD (Create, Read, Update via Modal, Delete)
├── transaksi.php            # Modul Transaksi Terpadu: Simpanan, Pinjaman, dan Bayar Angsuran (ACID)
├── simpanan.php             # Modul Khusus Setor Simpanan Mandiri (COMMIT & ROLLBACK)
├── laporan.php              # Rekapitulasi simpanan per anggota via MySQL Stored Procedure
├── export_excel.php         # Handler unduh file spreadsheet Microsoft Excel (.xls)
├── cetak_laporan.php        # Layout cetak dokumen PDF print-ready (auto trigger window.print)
├── logout.php               # Penghancuran sesi dan redirect aman ke login
├── README.md                # Dokumentasi instalasi dan gambaran umum proyek
└── DOKUMENTASI_TEKNIS.md    # Dokumen teknis komprehensif untuk pengujian UJIKOM/LSP
```

---

## 3. Panduan & Bukti Pemenuhan Poin Uji Kompetensi

### Poin 8: Transaksi Basis Data (COMMIT & ROLLBACK)
**Tuntutan Asesor:** Menunjukkan penggunaan transaksi manual di level PHP di dalam blok `try-catch` untuk menjamin pembatalan otomatis (*Atomicity*) jika terjadi kegagalan.

* **Lokasi Kode:** `transaksi.php` (baris pemrosesan simpanan, pinjaman, angsuran) dan `simpanan.php`.
* **Implementasi PDO (Setara MySQLi):**
  * Memulai transaksi: `$pdo->beginTransaction()` (setara `mysqli_begin_transaction()`)
  * Menyimpan permanen: `$pdo->commit()` (setara `mysqli_commit()`)
  * Membatalkan jika error: `$pdo->rollBack()` (setara `mysqli_rollback()`)
* **Contoh Potongan Kode:**
  ```php
  try {
      $pdo->beginTransaction(); // 1. Mulai transaksi manual

      $sql = "INSERT INTO simpanan (id_anggota, id_user, jenis_simpanan, nominal, tgl_simpan) 
              VALUES (:id_anggota, :id_user, :jenis, :nominal, :tgl)";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([...]);

      $pdo->commit(); // 2. Simpan jika semua proses sukses
  } catch (PDOException $e) {
      if ($pdo->inTransaction()) {
          $pdo->rollBack(); // 3. Batalkan mutasi data jika terjadi exception
      }
      $pesan = "Transaksi Dibatalkan (ROLLBACK): " . $e->getMessage();
  }
  ```

---

### Poin 10 & 11: Keamanan Basis Data & Pencegahan XSS
**Tuntutan Asesor:** Seluruh query INSERT, UPDATE, DELETE, dan SELECT menggunakan Prepared Statement serta sanitasi output `htmlspecialchars()`.

1. **Prepared Statements (Cegah SQL Injection):**
   * Diatur secara native pada `config/koneksi.php`: `PDO::ATTR_EMULATE_PREPARES => false`.
   * Seluruh query menggunakan parameter binding (`:username`, `:nama`, `:alamat`, `:nominal`, `:id`).
2. **Keamanan Login (Enkripsi Bcrypt):**
   * Di `index.php`, verifikasi password menggunakan `password_verify($password, $user['password'])`.
   * Jika password di database masih plaintext lama (`12345`), sistem secara otomatis mengonversinya ke hash aman bcrypt menggunakan `password_hash($password, PASSWORD_BCRYPT)`.
3. **Pencegahan Cross-Site Scripting (XSS):**
   * Seluruh output teks dinamis dari database dibungkus `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')`.

---

### Poin 13: Lembar Jawaban Asesor - Konsep Varian & Invarian

> **Pertanyaan Asesor:** *"Tunjukkan di mana konsep Varian dan Invarian pada kode program yang Anda buat dan jelaskan maksudnya!"*

* **Definisi:**
  * **Varian:** Elemen program (variabel, state, parameter) yang **nilainya dapat berubah-ubah** selama program berjalan atau antar-pemanggilan.
  * **Invarian:** Kondisi logika, rumus, atau struktur program yang **wajib selalu bernilai benar (tetap konstan)** sepanjang eksekusi program demi menjaga integritas data.
* **Tunjuk Kode Ini:**
  * **Buka file `transaksi.php` atau `laporan.php`:**
    * **Varian:**
      `$id_anggota = $_POST['id_anggota'];`
      `$nominal = $_POST['nominal'];`
      *(Penjelasan: Nilai variabel ini bersifat variatif/berubah-ubah tergantung data yang diinput oleh user).*
    * **Invarian:**
      * Validasi logika: `if ($nominal_bayar > $pinjam_info['sisa_tagihan'])`
        *(Penjelasan: Invarian bisnis koperasinya adalah "Sisa tagihan tidak boleh bernilai negatif ($sisa\_tagihan \ge 0$)").*
      * Parameter query: `"CALL rekap_simpanan_anggota(:id)"`
        *(Penjelasan: Struktur pemanggilan procedure dan rumus kalkulasinya di database bersifat tetap/invarian).*

---

### Poin 17: Lembar Jawaban Asesor - Analisis Kompleksitas Algoritma

> **Pertanyaan Asesor:** *"Berapa kompleksitas algoritma (Big-O) dari proses yang ada pada sistem Anda?"*

* **Tunjuk Kode Ini:**
  1. **Pada Operasi Transaksi (`transaksi.php`):**
     * **Time Complexity: $O(1)$ [Konstan]**  
       *Alasan:* Operasi penambahan simpanan atau pinjaman menggunakan kueri `INSERT` ke tabel InnoDB dengan Primary Key `AUTO_INCREMENT`. Waktu eksekusi tidak bergantung pada jumlah data yang ada di tabel, sehingga bernilai konstan $O(1)$.
     * **Space Complexity: $O(1)$ [Konstan]**  
       *Alasan:* Memori yang digunakan hanya untuk menampung beberapa variabel penampung sederhana (`$id_anggota`, `$nominal`), tidak ada array yang membesar secara dinamis.
  2. **Pada Operasi Laporan (`laporan.php`):**
     * **Time Complexity: $O(N)$ [Linear]**  
       *Alasan:* Stored Procedure `rekap_simpanan_anggota` melakukan pembacaan (*fetch*) terhadap $N$ baris riwayat simpanan milik anggota yang bersangkutan. Karena kolom `id_anggota` memiliki indeks Foreign Key B-Tree, proses pencariannya sangat efisien dengan kompleksitas $O(\log M + N)$ di mana $M$ adalah total baris tabel.
     * **Space Complexity: $O(K)$ [Konstan kecil]**  
       *Alasan:* Hasil agregasi hanya menghasilkan maksimal $K=3$ baris (sesuai ENUM: Pokok, Wajib, Sukarela).

---

### Poin 22 - 24: Standar Dokumentasi PHPDoc

Seluruh file sistem telah dilengkapi blok dokumentasi standar industri:
* `@package` : Nama paket aplikasi (`KoperasiSejahtera`)
* `@subpackage` : Modul kerja (`Auth`, `MasterData`, `Transaksi`, `Laporan`)
* `@author` : Nama peserta ujikom (`Iqbal Aditya`)
* `@version` : Versi rilis modul (`1.2.0`)
* `@param` : Tipe data dan deskripsi parameter masukan
* `@return` : Tipe data dan deskripsi nilai kembalian
* `@throws` : Jenis Exception yang dapat dilempar (`PDOException`)

---

## 4. Struktur Basis Data & Logika Otomatisasi (Database Objects)

### Diagram Tabel (LRS)
* `users` (`id_user`, `username`, `password VARCHAR(255)`)
* `anggota` (`id_anggota`, `nama`, `alamat`, `total_simpanan`)
* `simpanan` (`id_simpanan`, `id_anggota` FK, `id_user` FK, `jenis_simpanan`, `nominal`, `tgl_simpan`)
* `pinjaman` (`id_pinjaman`, `id_anggota` FK, `id_user` FK, `nominal_pinjaman`, `tenor`, `bunga`, `sisa_tagihan`, `status`)
* `angsuran` (`id_angsuran`, `id_pinjaman` FK, `id_user` FK, `nominal_bayar`, `tgl_bayar`)

### Database Objects (Otomatisasi Level DBMS)
1. **Trigger `trg_tambah_simpanan`:**
   Dijalankan `AFTER INSERT ON simpanan`. Mengakumulasikan saldo anggota:
   `UPDATE anggota SET total_simpanan = total_simpanan + NEW.nominal WHERE id_anggota = NEW.id_anggota;`
2. **Trigger `trg_bayar_angsuran`:**
   Dijalankan `AFTER INSERT ON angsuran`. Mengurangi sisa tagihan pinjaman:
   `UPDATE pinjaman SET sisa_tagihan = sisa_tagihan - NEW.nominal_bayar WHERE id_pinjaman = NEW.id_pinjaman;`
   Dan otomatis mengubah status pinjaman menjadi `'Lunas'` jika sisa tagihan $\le 0$.
3. **Function `hitung_total_bunga`:**
   Kalkulasi bunga tetap secara matematis di MySQL:
   `RETURN (nominal * (bunga / 100)) * tenor;`
4. **Stored Procedure `rekap_simpanan_anggota`:**
   Menghasilkan data agregasi rekap simpanan per jenis simpanan berdasarkan ID Anggota.

---

## 5. Panduan Uji Coba Demo di Depan Asesor

1. **Login:**
   * Buka browser: `http://localhost/koperasi/`
   * Masuk dengan Akun: `kasir1` / `12345`
   * Buka phpMyAdmin, tunjukkan bahwa password di tabel `users` sudah ter-enkripsi aman secara otomatis (Bcrypt hash).
2. **Master Data Anggota (Full CRUD):**
   * Buka menu **Data Master**.
   * Tambah anggota baru (Create).
   * Klik tombol **Edit** (warna kuning), ubah nama/alamat anggota pada popup modal, lalu simpan (Update).
   * Tunjukkan bahwa tabel diperbarui seketika (Read).
   * Hapus anggota uji coba (Delete).
3. **Transaksi Terpadu (ACID & Trigger):**
   * Buka menu **Transaksi**.
   * Input Simpanan Rp 100.000 -> Tunjukkan notifikasi "COMMIT berhasil" dan saldo anggota bertambah otomatis (Trigger).
   * Input Pinjaman Pokok Rp 1.000.000 (Bunga 1.5%, Tenor 12 bulan) -> Tunjukkan total tagihan otomatis dihitung oleh SQL Function.
   * Coba bayar angsuran dengan nominal melebihi sisa tagihan -> Tunjukkan sistem menolak transaksi (Validasi Invarian).
   * Bayar angsuran dengan nominal valid -> Sisa tagihan berkurang otomatis.
4. **Laporan & Ekspor:**
   * Buka menu **Laporan**.
   * Pilih nama anggota -> Klik **Tampilkan Laporan** (menjalankan Stored Procedure).
   * Klik **Download Excel** -> File spreadsheet otomatis terunduh.
   * Klik **Cetak PDF** -> Kotak dialog cetak dokumen langsung muncul di layar.
