-- ==============================================================================
-- KALICAA VILLA - ESTATE MANAGEMENT & BILLING SYSTEM
-- DATABASE COMPLETE DUMP (SCHEMA + MASTER SEED DATA)
-- File: database.sql
-- Kompatibilitas: MySQL 5.7+, MySQL 8.0+, MariaDB 10.3+
-- ==============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";

-- ------------------------------------------------------------------------------
-- 1. DROP EXISTING TABLES IF ANY
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `titipan_pvbw`;
DROP TABLE IF EXISTS `titipan_rvbw`;
DROP TABLE IF EXISTS `spk_payments`;
DROP TABLE IF EXISTS `spk_records`;
DROP TABLE IF EXISTS `rvbw_linked_invoices`;
DROP TABLE IF EXISTS `rvbw_items`;
DROP TABLE IF EXISTS `rvbw`;
DROP TABLE IF EXISTS `invoice_items`;
DROP TABLE IF EXISTS `invoices`;
DROP TABLE IF EXISTS `skp_properties`;
DROP TABLE IF EXISTS `debiturs`;
DROP TABLE IF EXISTS `master_coa`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `app_settings`;

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------------------------
-- 2. TABEL: app_settings (Konfigurasi Sistem & Master Tarif)
-- ------------------------------------------------------------------------------
CREATE TABLE `app_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_perusahaan` VARCHAR(150) NOT NULL DEFAULT 'Kalicaa Villa',
  `sub_nama` VARCHAR(150) NOT NULL DEFAULT 'Tanjung Lesung Beach Resort',
  `alamat` TEXT,
  `telepon` VARCHAR(50) DEFAULT '021-12345678',
  `nama_bank` VARCHAR(50) NOT NULL DEFAULT 'BCA',
  `no_rekening` VARCHAR(50) NOT NULL DEFAULT '1234567890',
  `atas_nama` VARCHAR(150) NOT NULL DEFAULT 'PT Kalicaa Management',
  `ppn_default` DECIMAL(5,2) NOT NULL DEFAULT 11.00,
  `prefix_rvbw` VARCHAR(20) NOT NULL DEFAULT 'RVBW',
  `invoice_counter` INT NOT NULL DEFAULT 1,
  -- Master Tarif Listrik PLN
  `tarif_pln_per_kwh` DECIMAL(12,2) NOT NULL DEFAULT 1699.53,
  `faktor_rekening_minimum` DECIMAL(6,4) NOT NULL DEFAULT 0.0400,
  `persen_loses` DECIMAL(5,2) NOT NULL DEFAULT 5.00,
  `persen_ppj` DECIMAL(5,2) NOT NULL DEFAULT 5.00,
  `persen_jasa_listrik_air` DECIMAL(5,2) NOT NULL DEFAULT 10.00,
  -- Master Tarif Kawasan & IPL
  `tarif_kawasan_per_m2` DECIMAL(12,2) NOT NULL DEFAULT 2000.00,
  `persen_fee_kawasan` DECIMAL(5,2) NOT NULL DEFAULT 4.00,
  -- Master Tarif Air PDAM
  `tarif_air_abodemen` DECIMAL(12,2) NOT NULL DEFAULT 22000.00,
  `tarif_air_tier1_batas` INT NOT NULL DEFAULT 20,
  `tarif_air_tier1` DECIMAL(12,2) NOT NULL DEFAULT 9700.00,
  `tarif_air_tier2_batas` INT NOT NULL DEFAULT 30,
  `tarif_air_tier2` DECIMAL(12,2) NOT NULL DEFAULT 10785.00,
  `tarif_air_tier3` DECIMAL(12,2) NOT NULL DEFAULT 11445.00,
  -- SMTP Email Konfigurasi
  `smtp_host` VARCHAR(150) NULL,
  `smtp_port` INT NULL DEFAULT 587,
  `smtp_secure` TINYINT(1) NOT NULL DEFAULT 0,
  `smtp_user` VARCHAR(150) NULL,
  `smtp_pass` VARCHAR(255) NULL,
  `smtp_from_name` VARCHAR(150) NULL DEFAULT 'Manajemen Kalicaa',
  `smtp_from_email` VARCHAR(150) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 3. TABEL: users (Autentikasi & Hak Akses Pengguna)
-- ------------------------------------------------------------------------------
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `role` ENUM('Super Admin', 'Finance Admin', 'Billing Officer', 'Resort Manager') NOT NULL DEFAULT 'Finance Admin',
  `avatar_color` VARCHAR(20) NOT NULL DEFAULT '#06b6d4',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 4. TABEL: master_coa (Chart of Accounts Akuntansi)
-- ------------------------------------------------------------------------------
CREATE TABLE `master_coa` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_coa` VARCHAR(50) UNIQUE NOT NULL,
  `nama_coa` VARCHAR(150) NOT NULL,
  `deskripsi` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 5. TABEL: debiturs (Data Debitur / Pemilik Kavling & Villa)
-- ------------------------------------------------------------------------------
CREATE TABLE `debiturs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_kav` VARCHAR(50) UNIQUE NOT NULL,
  `nama_owner` VARCHAR(150) NOT NULL,
  `no_hp` VARCHAR(30) NOT NULL,
  `email` VARCHAR(100) NULL,
  `tipe_unit` VARCHAR(100) NOT NULL,
  `status` ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
  `watt_listrik` INT NOT NULL DEFAULT 6600,
  `luas_m2_kawasan` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `landscape_flat` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `kolam_flat` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `internet_flat` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `alamat_unit` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 6. TABEL: skp_properties (Surat Kontrak Penjualan / Piutang Unit)
-- ------------------------------------------------------------------------------
CREATE TABLE `skp_properties` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nomor_skp` VARCHAR(100) UNIQUE NOT NULL,
  `tanggal_skp` DATE NOT NULL,
  `debitur_id` INT NOT NULL,
  `kode_kav` VARCHAR(50) NOT NULL,
  `nama_owner` VARCHAR(150) NOT NULL,
  `tipe_unit_skp` ENUM('Residensial', 'Commercial') NOT NULL DEFAULT 'Residensial',
  `nilai_jual` DECIMAL(15,2) NOT NULL,
  `uang_muka` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `tenor_bulan` INT NOT NULL,
  `tanggal_mulai_cicilan` DATE NOT NULL,
  `nilai_angsuran_bulanan` DECIMAL(15,2) NOT NULL,
  `status` ENUM('Berjalan', 'Lunas', 'Batal') NOT NULL DEFAULT 'Berjalan',
  `catatan` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`debitur_id`),
  FOREIGN KEY (`debitur_id`) REFERENCES `debiturs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 7. TABEL: invoices (Faktur Tagihan Bulanan / Cicilan SKP)
-- ------------------------------------------------------------------------------
CREATE TABLE `invoices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nomor_invoice` VARCHAR(100) UNIQUE NOT NULL,
  `nomor_rvbw` VARCHAR(50) NULL,
  `rvbw_id` INT NULL,
  `debitur_id` INT NOT NULL,
  `kode_kav` VARCHAR(50) NOT NULL,
  `nama_debitur` VARCHAR(150) NOT NULL,
  `no_hp` VARCHAR(30) NOT NULL,
  `periode_bulan` INT NOT NULL,
  `periode_tahun` INT NOT NULL,
  `subtotal_dpp` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `total_ppn` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `grand_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `tanggal_terbit` DATE NOT NULL,
  `status_kirim_wa` TINYINT(1) NOT NULL DEFAULT 0,
  `wa_sent_at` DATETIME NULL,
  `status_bayar` ENUM('Belum Bayar', 'Lunas') NOT NULL DEFAULT 'Belum Bayar',
  `tgl_bayar` DATE NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`debitur_id`),
  INDEX (`periode_tahun`, `periode_bulan`),
  INDEX (`status_bayar`),
  FOREIGN KEY (`debitur_id`) REFERENCES `debiturs`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 8. TABEL: invoice_items (Detail Rincian Item Tagihan)
-- ------------------------------------------------------------------------------
CREATE TABLE `invoice_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_id` INT NOT NULL,
  `keterangan` VARCHAR(255) NOT NULL,
  `kode_coa` VARCHAR(50) NOT NULL,
  `nilai_dpp` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `ppn_persen` DECIMAL(5,2) NOT NULL DEFAULT 11.00,
  `nilai_ppn` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `skp_id` INT NULL,
  `tagihan_bulanan_kategori` ENUM('LISTRIK', 'AIR', 'KAWASAN', 'LANDSCAPE', 'KOLAM', 'INTERNET') NULL,
  `meteran_pln_awal` DECIMAL(10,2) NULL,
  `meteran_pln_akhir` DECIMAL(10,2) NULL,
  `meteran_air_awal` DECIMAL(10,2) NULL,
  `meteran_air_akhir` DECIMAL(10,2) NULL,
  INDEX (`invoice_id`),
  INDEX (`skp_id`),
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`skp_id`) REFERENCES `skp_properties`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 9. TABEL: rvbw (Receipt Voucher Bank Wholesale / Bukti Penerimaan)
-- ------------------------------------------------------------------------------
CREATE TABLE `rvbw` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nomor_rvbw` VARCHAR(50) UNIQUE NOT NULL,
  `tanggal` DATE NOT NULL,
  `debitur_id` INT NOT NULL,
  `kode_kav` VARCHAR(50) NOT NULL,
  `nama_debitur` VARCHAR(150) NOT NULL,
  `no_hp` VARCHAR(30) NOT NULL,
  `periode_bulan` INT NOT NULL,
  `periode_tahun` INT NOT NULL,
  `subtotal_dpp` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `total_ppn` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `grand_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('Draft', 'Checking', 'Posted') NOT NULL DEFAULT 'Draft',
  `invoice_id` INT NULL,
  `catatan` TEXT NULL,
  `keterangan_periode` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `posted_at` DATETIME NULL,
  INDEX (`debitur_id`),
  INDEX (`status`),
  FOREIGN KEY (`debitur_id`) REFERENCES `debiturs`(`id`),
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 10. TABEL: rvbw_linked_invoices (Relasi Many-to-Many Pelunasan RVBW & Invoices)
-- ------------------------------------------------------------------------------
CREATE TABLE `rvbw_linked_invoices` (
  `rvbw_id` INT NOT NULL,
  `invoice_id` INT NOT NULL,
  PRIMARY KEY (`rvbw_id`, `invoice_id`),
  FOREIGN KEY (`rvbw_id`) REFERENCES `rvbw`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 11. TABEL: spk_records (Surat Perintah Kerja Kontraktor / Inhouse)
-- ------------------------------------------------------------------------------
CREATE TABLE `spk_records` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nomor_spk` VARCHAR(100) UNIQUE NOT NULL,
  `jenis_pekerjaan` ENUM('Kontraktor', 'Inhouse') NOT NULL DEFAULT 'Kontraktor',
  `nama_kontraktor` VARCHAR(150) NOT NULL,
  `nominal_kontrak` DECIMAL(15,2) NOT NULL,
  `pph_persen` DECIMAL(5,2) NOT NULL DEFAULT 2.00,
  `pic_pekerjaan` VARCHAR(100) NOT NULL,
  `keterangan` TEXT NOT NULL,
  `tanggal_spk` DATE NOT NULL,
  `status_manual` ENUM('Belum Selesai', 'Selesai') NOT NULL DEFAULT 'Belum Selesai',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `spk_payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `spk_id` INT NOT NULL,
  `tahapan` VARCHAR(50) NOT NULL,
  `nilai_pembayaran` DECIMAL(15,2) NOT NULL,
  `nomor_voucher` VARCHAR(50) NOT NULL,
  `tanggal_pembayaran` DATE NOT NULL,
  `catatan` TEXT NULL,
  INDEX (`spk_id`),
  FOREIGN KEY (`spk_id`) REFERENCES `spk_records`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 12. TABEL: titipan_rvbw & titipan_pvbw (Modul Rekening Titipan Dana BWJ / TLLI)
-- ------------------------------------------------------------------------------
CREATE TABLE `titipan_rvbw` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nomor` VARCHAR(50) UNIQUE NOT NULL,
  `entitas` ENUM('BWJ', 'TLLI') NOT NULL,
  `tanggal_terima` DATE NOT NULL,
  `keterangan` TEXT NOT NULL,
  `nominal` DECIMAL(15,2) NOT NULL,
  `status` ENUM('Titipan', 'Selesai') NOT NULL DEFAULT 'Titipan',
  `total_dikembalikan` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `sisa_titipan` DECIMAL(15,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `titipan_pvbw` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nomor` VARCHAR(50) UNIQUE NOT NULL,
  `entitas` ENUM('BWJ', 'TLLI') NOT NULL,
  `rvbw_id` INT NOT NULL,
  `nomor_rvbw_ref` VARCHAR(50) NOT NULL,
  `tanggal_kembali` DATE NOT NULL,
  `keterangan` TEXT NOT NULL,
  `nominal` DECIMAL(15,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`rvbw_id`),
  FOREIGN KEY (`rvbw_id`) REFERENCES `titipan_rvbw`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==============================================================================
-- 13. MASTER SEED DATA (DATA AWAL SISTEM)
-- ==============================================================================

-- 13.1 Pengaturan Default Aplikasi & Tarif
INSERT INTO `app_settings` (
  `id`, `nama_perusahaan`, `sub_nama`, `alamat`, `telepon`, `nama_bank`, `no_rekening`, `atas_nama`,
  `ppn_default`, `prefix_rvbw`, `invoice_counter`,
  `tarif_pln_per_kwh`, `faktor_rekening_minimum`, `persen_loses`, `persen_ppj`, `persen_jasa_listrik_air`,
  `tarif_kawasan_per_m2`, `persen_fee_kawasan`,
  `tarif_air_abodemen`, `tarif_air_tier1_batas`, `tarif_air_tier1`, `tarif_air_tier2_batas`, `tarif_air_tier2`, `tarif_air_tier3`
) VALUES (
  1, 'Kalicaa Villa', 'Tanjung Lesung Beach Resort',
  'Jl. Pantai Tanjung Lesung, Kab. Pandeglang, Banten 42285',
  '(0253) 401-234', 'BCA', '1234567890', 'PT Kalicaa Management',
  11.00, 'RVBW', 1,
  1699.53, 0.0400, 5.00, 5.00, 10.00,
  2000.00, 4.00,
  22000.00, 20, 9700.00, 30, 10785.00, 11445.00
) ON DUPLICATE KEY UPDATE `nama_perusahaan`=VALUES(`nama_perusahaan`);

-- 13.2 Akun Pengguna Default (Password Default: password123)
INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `email`, `role`, `avatar_color`) VALUES
(1, 'superadmin', '$2y$10$eA3zE4vV1gO.m3hK7yFw.uvX3w7qYp3bWz6SgE6L2NqE8vF8G0Z6m', 'Super Administrator', 'superadmin@kalicaavilla.com', 'Super Admin', '#06b6d4'),
(2, 'finance', '$2y$10$eA3zE4vV1gO.m3hK7yFw.uvX3w7qYp3bWz6SgE6L2NqE8vF8G0Z6m', 'Siti Rahmawati', 'finance@kalicaavilla.com', 'Finance Admin', '#10b981'),
(3, 'billing', '$2y$10$eA3zE4vV1gO.m3hK7yFw.uvX3w7qYp3bWz6SgE6L2NqE8vF8G0Z6m', 'Budi Billing', 'billing@kalicaavilla.com', 'Billing Officer', '#f59e0b'),
(4, 'manager', '$2y$10$eA3zE4vV1gO.m3hK7yFw.uvX3w7qYp3bWz6SgE6L2NqE8vF8G0Z6m', 'Doni Kusuma', 'manager@kalicaavilla.com', 'Resort Manager', '#8b5cf6')
ON DUPLICATE KEY UPDATE `username`=VALUES(`username`);

-- 13.3 Master Chart of Accounts (COA)
INSERT INTO `master_coa` (`id`, `kode_coa`, `nama_coa`, `deskripsi`) VALUES
(1, '40602.0600.0000', 'Pendapatan Recharged Listrik & Air', 'Tagihan pemakaian listrik & air yang dibebankan kembali ke owner'),
(2, '40602.0100.0000', 'Pendapatan Perawatan Lingkungan (Kawasan)', 'Iuran Pengelolaan Lingkungan (IPL) bulanan'),
(3, '40602.0200.0000', 'Pendapatan Dana Cadangan (Sinking Fund)', 'Iuran sinking fund untuk pemeliharaan gedung jangka panjang'),
(4, '40602.0300.0000', 'Pendapatan Wi-Fi & TV Kabel', 'Iuran internet broadband & TV berlangganan kawasan')
ON DUPLICATE KEY UPDATE `kode_coa`=VALUES(`kode_coa`);

-- 13.4 Master Debitur & Kavling Sample
INSERT INTO `debiturs` (`id`, `kode_kav`, `nama_owner`, `no_hp`, `email`, `tipe_unit`, `status`, `watt_listrik`, `luas_m2_kawasan`, `landscape_flat`, `kolam_flat`, `internet_flat`, `alamat_unit`) VALUES
(1, 'EM-1010', 'Budi Santoso', '6281234567890', 'budi.santoso@email.com', 'Villa Type A', 'aktif', 6600, 350.00, 250000.00, 300000.00, 450000.00, 'Private Residential Kalicaa Villa EM-1010'),
(2, 'EM-1025', 'Sri Wahyuni', '6282345678901', 'sri.wahyuni@email.com', 'Villa Type B', 'aktif', 10600, 420.00, 300000.00, 350000.00, 450000.00, 'Private Residential Kalicaa Villa EM-1025'),
(3, 'EM-2005', 'Ahmad Fauzi', '6283456789012', 'ahmad.fauzi@email.com', 'Villa Type A', 'aktif', 6600, 300.00, 0.00, 0.00, 450000.00, 'Private Residential Kalicaa Villa EM-2005'),
(4, 'EM-3200', 'Indira Bambang', '6285678901234', 'indira.b@email.com', 'Villa Type Royal', 'aktif', 13200, 600.00, 500000.00, 500000.00, 500000.00, 'Grand Royal Estate Kalicaa Villa EM-3200')
ON DUPLICATE KEY UPDATE `kode_kav`=VALUES(`kode_kav`);

-- 13.5 Master SKP Piutang Sample
INSERT INTO `skp_properties` (`id`, `nomor_skp`, `tanggal_skp`, `debitur_id`, `kode_kav`, `nama_owner`, `tipe_unit_skp`, `nilai_jual`, `uang_muka`, `tenor_bulan`, `tanggal_mulai_cicilan`, `nilai_angsuran_bulanan`, `status`, `catatan`) VALUES
(1, 'SKP/2026/EM-1010/001', '2026-01-15', 1, 'EM-1010', 'Budi Santoso', 'Residensial', 1500000000.00, 300000000.00, 24, '2026-02-01', 50000000.00, 'Berjalan', 'Kontrak kepemilikan unit Villa Kalicaa Blok EM-1010')
ON DUPLICATE KEY UPDATE `nomor_skp`=VALUES(`nomor_skp`);

-- 13.6 SPK Proyek Sample
INSERT INTO `spk_records` (`id`, `nomor_spk`, `jenis_pekerjaan`, `nama_kontraktor`, `nominal_kontrak`, `pph_persen`, `pic_pekerjaan`, `keterangan`, `tanggal_spk`, `status_manual`) VALUES
(1, 'SPK/2026/001/MAINT', 'Kontraktor', 'PT Mitra Beton Perkasa', 85000000.00, 2.00, 'Agus Hartono', 'Perbaikan dak atap dan waterproofing unit EM-1010 - EM-1025', '2026-04-10', 'Belum Selesai')
ON DUPLICATE KEY UPDATE `nomor_spk`=VALUES(`nomor_spk`);

INSERT INTO `spk_payments` (`id`, `spk_id`, `tahapan`, `nilai_pembayaran`, `nomor_voucher`, `tanggal_pembayaran`, `catatan`) VALUES
(1, 1, 'DP', 25500000.00, 'PVBW26040001', '2026-04-12', 'Pembayaran DP 30% proyek renovasi')
ON DUPLICATE KEY UPDATE `nomor_voucher`=VALUES(`nomor_voucher`);

-- 13.7 Titipan Dana Sample
INSERT INTO `titipan_rvbw` (`id`, `nomor`, `entitas`, `tanggal_terima`, `keterangan`, `nominal`, `status`, `total_dikembalikan`, `sisa_titipan`) VALUES
(1, 'RVBW26050001', 'BWJ', '2026-05-02', 'Titipan dana jaminan renovasi unit Budi Santoso', 10000000.00, 'Titipan', 0.00, 10000000.00)
ON DUPLICATE KEY UPDATE `nomor`=VALUES(`nomor`);

-- Selesai
COMMIT;
