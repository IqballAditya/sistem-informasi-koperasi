-- ==============================================================================
-- NAMA DATABASE : db_koperasi
-- DESKRIPSI     : Script lengkap pembuatan struktur database Koperasi Sejahtera
--                 Mandiri beserta Trigger, Function, dan Stored Procedure.
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS db_koperasi;
USE db_koperasi;

-- --------------------------------------------------------
-- 1. PEMBUATAN TABEL DAN RELASI (LRS)
-- --------------------------------------------------------

CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE anggota (
    id_anggota INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    alamat TEXT NOT NULL,
    total_simpanan INT DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE simpanan (
    id_simpanan INT AUTO_INCREMENT PRIMARY KEY,
    id_anggota INT NOT NULL,
    id_user INT NOT NULL,
    jenis_simpanan ENUM('Pokok', 'Wajib', 'Sukarela') NOT NULL,
    nominal INT NOT NULL,
    tgl_simpan DATE NOT NULL,
    FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pinjaman (
    id_pinjaman INT AUTO_INCREMENT PRIMARY KEY,
    id_anggota INT NOT NULL,
    id_user INT NOT NULL,
    nominal_pinjaman INT NOT NULL,
    tenor INT NOT NULL,
    bunga DECIMAL(5,2) NOT NULL,
    sisa_tagihan INT NOT NULL,
    status ENUM('Berjalan', 'Lunas') DEFAULT 'Berjalan',
    FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE angsuran (
    id_angsuran INT AUTO_INCREMENT PRIMARY KEY,
    id_pinjaman INT NOT NULL,
    id_user INT NOT NULL,
    nominal_bayar INT NOT NULL,
    tgl_bayar DATE NOT NULL,
    FOREIGN KEY (id_pinjaman) REFERENCES pinjaman(id_pinjaman) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- 2. INSERT DATA DUMMY (DATA AWAL)
-- --------------------------------------------------------
INSERT INTO users (id_user, username, password) VALUES (1, 'kasir1', '12345');
INSERT INTO anggota (id_anggota, nama, alamat, total_simpanan) VALUES (1, 'Iqbal Aditya', 'Tangerang Selatan', 0);

-- --------------------------------------------------------
-- 3. PEMBUATAN TRIGGER (Otomatisasi Saldo)
-- --------------------------------------------------------
DELIMITER $$

CREATE TRIGGER trg_tambah_simpanan
AFTER INSERT ON simpanan
FOR EACH ROW
BEGIN
    UPDATE anggota 
    SET total_simpanan = total_simpanan + NEW.nominal 
    WHERE id_anggota = NEW.id_anggota;
END$$

CREATE TRIGGER trg_bayar_angsuran
AFTER INSERT ON angsuran
FOR EACH ROW
BEGIN
    UPDATE pinjaman 
    SET sisa_tagihan = sisa_tagihan - NEW.nominal_bayar 
    WHERE id_pinjaman = NEW.id_pinjaman;

    UPDATE pinjaman 
    SET status = 'Lunas' 
    WHERE id_pinjaman = NEW.id_pinjaman AND sisa_tagihan <= 0;
END$$

DELIMITER ;

-- --------------------------------------------------------
-- 4. PEMBUATAN FUNCTION & STORED PROCEDURE
-- --------------------------------------------------------
DELIMITER $$

-- Function: Menghitung total bunga
CREATE FUNCTION hitung_total_bunga(nominal INT, bunga DECIMAL(5,2), tenor INT) 
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE total_bunga INT;
    SET total_bunga = (nominal * (bunga / 100)) * tenor;
    RETURN total_bunga;
END$$

-- Procedure: Rekap data simpanan untuk menu Laporan
CREATE PROCEDURE rekap_simpanan_anggota(IN p_id_anggota INT)
BEGIN
    SELECT a.nama, s.jenis_simpanan, SUM(s.nominal) as total
    FROM anggota a
    JOIN simpanan s ON a.id_anggota = s.id_anggota
    WHERE a.id_anggota = p_id_anggota
    GROUP BY a.nama, s.jenis_simpanan;
END$$

DELIMITER ;