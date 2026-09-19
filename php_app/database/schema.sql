-- ==========================================================
-- KALICAA VILLA - ESTATE MANAGEMENT & BILLING SYSTEM (PHP/MYSQL)
-- FULL DATABASE SCHEMA DDL
-- ==========================================================

SET FOREIGN_KEY_CHECKS = 0;
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

-- 1. Tabel Konfigurasi & Master Tarif
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
  -- Master Tarif Kawasan
  `tarif_kawasan_per_m2` DECIMAL(12,2) NOT NULL DEFAULT 2000.00,
  `persen_fee_kawasan` DECIMAL(5,2) NOT NULL DEFAULT 4.00,
  -- Master Tarif Air PDAM
  `tarif_air_abodemen` DECIMAL(12,2) NOT NULL DEFAULT 22000.00,
  `tarif_air_tier1_batas` INT NOT NULL DEFAULT 20,
  `tarif_air_tier1` DECIMAL(12,2) NOT NULL DEFAULT 9700.00,
  `tarif_air_tier2_batas` INT NOT NULL DEFAULT 30,
  `tarif_air_tier2` DECIMAL(12,2) NOT NULL DEFAULT 10785.00,
  `tarif_air_tier3` DECIMAL(12,2) NOT NULL DEFAULT 11445.00,
  -- SMTP Config
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

-- 2. Tabel Users (RBAC)
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

-- 3. Master COA
CREATE TABLE `master_coa` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_coa` VARCHAR(50) UNIQUE NOT NULL,
  `nama_coa` VARCHAR(150) NOT NULL,
  `deskripsi` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Master Debitur (Pemilik Unit Villa)
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

-- 5. Master SKP (Penjualan Unit Villa)
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

-- 6. Tabel Invoices
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

-- 7. Item Tagihan Invoice
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

-- 8. Transaksi RVBW
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

-- 9. Relasi Many-to-Many RVBW dengan Invoice yang Dilunasi
CREATE TABLE `rvbw_linked_invoices` (
  `rvbw_id` INT NOT NULL,
  `invoice_id` INT NOT NULL,
  PRIMARY KEY (`rvbw_id`, `invoice_id`),
  FOREIGN KEY (`rvbw_id`) REFERENCES `rvbw`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Modul Mandiri SPK
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

-- 11. Modul Titipan Dana (BWJ / TLLI)
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
