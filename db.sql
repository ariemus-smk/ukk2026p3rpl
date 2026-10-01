-- ==============================================================
-- UJI KOMPETENSI KEAHLIAN (UKK) 2025/2026 - PAKET 3
-- Rekayasa Perangkat Lunak (RPL)
-- Judul: Aplikasi Pengaduan Sarana Sekolah
-- File: db.sql (Skema Database & Data Awal)
-- ==============================================================

-- Buat Database (jika belum ada) dan gunakan database tersebut
CREATE DATABASE IF NOT EXISTS `ukk_arie` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ukk_arie`;

-- --------------------------------------------------------
-- 1. Tabel: admin
-- Berisi data akun administrator untuk verifikasi & umpan balik
-- --------------------------------------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Awal untuk tabel admin
-- Username: admin
-- Password default: admin123 (menggunakan password_hash BCRYPT standar PHP)
INSERT INTO `admin` (`username`, `password`) VALUES
('admin', '$2y$10$ryJd4hXvzYZyYblYwxDe9Ov1/x9b3VkOmVP/dutlDOauuWca1iG9.');
-- Catatan: Hash di atas cocok dengan teks: admin123

-- --------------------------------------------------------
-- 2. Tabel: siswa
-- Sesuai Gambar Kerja: nis(int,10), kelas(varchar,10)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `siswa`;
CREATE TABLE `siswa` (
  `nis` INT(10) NOT NULL,
  `kelas` VARCHAR(10) NOT NULL,
  PRIMARY KEY (`nis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Awal Siswa
INSERT INTO `siswa` (`nis`, `kelas`) VALUES
(1021001, 'XII RPL 1'),
(1021002, 'XII RPL 2'),
(1021003, 'XII TKJ 1');

-- --------------------------------------------------------
-- 3. Tabel: kategori
-- Sesuai Gambar Kerja: id_kategori(int,5), ket_kategori(varchar,30)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `kategori`;
CREATE TABLE `kategori` (
  `id_kategori` INT(5) NOT NULL AUTO_INCREMENT,
  `ket_kategori` VARCHAR(30) NOT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Awal Kategori Sarana
INSERT INTO `kategori` (`id_kategori`, `ket_kategori`) VALUES
(1, 'Ruang Kelas'),
(2, 'Laboratorium Komputer'),
(3, 'Toilet & Sanitasi'),
(4, 'Lapangan & Olahraga'),
(5, 'Kelistrikan & AC');

-- --------------------------------------------------------
-- 4. Tabel: input_aspirasi
-- Sesuai Gambar Kerja: Id_pelaporan(int,5), nis(int,10), id_kategori(int,5), lokasi(varchar,50), ket(varchar,50)
-- Ditambahkan kolom tanggal (DATE) untuk mendukung filter per tanggal & bulan pada Halaman 2 Soal.
-- --------------------------------------------------------
DROP TABLE IF EXISTS `input_aspirasi`;
CREATE TABLE `input_aspirasi` (
  `id_pelaporan` INT(5) NOT NULL AUTO_INCREMENT,
  `nis` INT(10) NOT NULL,
  `id_kategori` INT(5) NOT NULL,
  `lokasi` VARCHAR(50) NOT NULL,
  `ket` VARCHAR(255) NOT NULL,
  `tanggal` DATE NOT NULL DEFAULT (CURRENT_DATE),
  PRIMARY KEY (`id_pelaporan`),
  KEY `fk_input_aspirasi_siswa` (`nis`),
  KEY `fk_input_aspirasi_kategori` (`id_kategori`),
  CONSTRAINT `fk_input_aspirasi_siswa` FOREIGN KEY (`nis`) REFERENCES `siswa` (`nis`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_input_aspirasi_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Awal Input Aspirasi
INSERT INTO `input_aspirasi` (`id_pelaporan`, `nis`, `id_kategori`, `lokasi`, `ket`, `tanggal`) VALUES
(1, 1021001, 2, 'Lab Komputer 2', 'Keyboard pada PC nomor 08 tidak berfungsi', '2026-02-10'),
(2, 1021002, 3, 'Toilet Lantai 2 Gedung B', 'Kran air patah dan air terus mengalir', '2026-02-15'),
(3, 1021003, 5, 'Ruang Kelas XII TKJ 1', 'Stop kontak bagian belakang korsleting', '2026-03-01');

-- --------------------------------------------------------
-- 5. Tabel: aspirasi
-- Sesuai Gambar Kerja: id_aspirasi(int,5), status(enum "Menunggu;Proses;Selesai"), id_kategori(int,5), feedback
-- Terhubung ke input_aspirasi melalui id_pelaporan
-- --------------------------------------------------------
DROP TABLE IF EXISTS `aspirasi`;
CREATE TABLE `aspirasi` (
  `id_aspirasi` INT(5) NOT NULL AUTO_INCREMENT,
  `id_pelaporan` INT(5) NOT NULL,
  `status` ENUM('Menunggu', 'Proses', 'Selesai') NOT NULL DEFAULT 'Menunggu',
  `id_kategori` INT(5) NOT NULL,
  `feedback` TEXT DEFAULT NULL,
  PRIMARY KEY (`id_aspirasi`),
  KEY `fk_aspirasi_pelaporan` (`id_pelaporan`),
  KEY `fk_aspirasi_kategori` (`id_kategori`),
  CONSTRAINT `fk_aspirasi_pelaporan` FOREIGN KEY (`id_pelaporan`) REFERENCES `input_aspirasi` (`id_pelaporan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_aspirasi_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Awal Aspirasi (Status & Feedback yang dikelola oleh Admin)
INSERT INTO `aspirasi` (`id_aspirasi`, `id_pelaporan`, `status`, `id_kategori`, `feedback`) VALUES
(1, 1, 'Selesai', 2, 'Keyboard PC-08 sudah diganti baru oleh teknisi lab.'),
(2, 2, 'Proses', 3, 'Kran sudah dilaporkan ke bagian sarpras dan sedang proses perbaikan.'),
(3, 3, 'Menunggu', 5, NULL);
