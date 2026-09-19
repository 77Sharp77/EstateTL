-- ==========================================================
-- KALICAA VILLA - SQLITE SCHEMA (Portability & Local Dev)
-- ==========================================================

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

CREATE TABLE `app_settings` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `nama_perusahaan` TEXT NOT NULL DEFAULT 'Kalicaa Villa',
  `sub_nama` TEXT NOT NULL DEFAULT 'Tanjung Lesung Beach Resort',
  `alamat` TEXT,
  `telepon` TEXT DEFAULT '021-12345678',
  `nama_bank` TEXT NOT NULL DEFAULT 'BCA',
  `no_rekening` TEXT NOT NULL DEFAULT '1234567890',
  `atas_nama` TEXT NOT NULL DEFAULT 'PT Kalicaa Management',
  `ppn_default` NUMERIC NOT NULL DEFAULT 11.00,
  `prefix_rvbw` TEXT NOT NULL DEFAULT 'RVBW',
  `invoice_counter` INTEGER NOT NULL DEFAULT 1,
  `tarif_pln_per_kwh` NUMERIC NOT NULL DEFAULT 1699.53,
  `faktor_rekening_minimum` NUMERIC NOT NULL DEFAULT 0.0400,
  `persen_loses` NUMERIC NOT NULL DEFAULT 5.00,
  `persen_ppj` NUMERIC NOT NULL DEFAULT 5.00,
  `persen_jasa_listrik_air` NUMERIC NOT NULL DEFAULT 10.00,
  `tarif_kawasan_per_m2` NUMERIC NOT NULL DEFAULT 2000.00,
  `persen_fee_kawasan` NUMERIC NOT NULL DEFAULT 4.00,
  `tarif_air_abodemen` NUMERIC NOT NULL DEFAULT 22000.00,
  `tarif_air_tier1_batas` NUMERIC NOT NULL DEFAULT 20.00,
  `tarif_air_tier1` NUMERIC NOT NULL DEFAULT 9700.00,
  `tarif_air_tier2_batas` NUMERIC NOT NULL DEFAULT 30.00,
  `tarif_air_tier2` NUMERIC NOT NULL DEFAULT 10785.00,
  `tarif_air_tier3` NUMERIC NOT NULL DEFAULT 11445.00,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `users` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `username` TEXT NOT NULL UNIQUE,
  `password` TEXT NOT NULL,
  `nama_lengkap` TEXT NOT NULL,
  `email` TEXT NOT NULL,
  `role` TEXT NOT NULL DEFAULT 'Finance Admin',
  `avatar_color` TEXT DEFAULT '#06b6d4',
  `is_active` INTEGER NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `master_coa` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `kode_coa` TEXT NOT NULL UNIQUE,
  `nama_coa` TEXT NOT NULL,
  `deskripsi` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `debiturs` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `kode_kav` TEXT NOT NULL UNIQUE,
  `nama_owner` TEXT NOT NULL,
  `no_hp` TEXT NOT NULL,
  `email` TEXT,
  `tipe_unit` TEXT NOT NULL DEFAULT 'Villa Standard',
  `status` TEXT NOT NULL DEFAULT 'aktif',
  `watt_listrik` INTEGER NOT NULL DEFAULT 6600,
  `luas_m2_kawasan` NUMERIC NOT NULL DEFAULT 350.00,
  `landscape_flat` NUMERIC NOT NULL DEFAULT 0.00,
  `kolam_flat` NUMERIC NOT NULL DEFAULT 0.00,
  `internet_flat` NUMERIC NOT NULL DEFAULT 450000.00,
  `alamat_unit` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `skp_properties` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `nomor_skp` TEXT NOT NULL UNIQUE,
  `tanggal_skp` DATE NOT NULL,
  `debitur_id` INTEGER NOT NULL,
  `kode_kav` TEXT NOT NULL,
  `nama_owner` TEXT NOT NULL,
  `tipe_unit_skp` TEXT NOT NULL DEFAULT 'Residensi',
  `nilai_jual` NUMERIC NOT NULL DEFAULT 0.00,
  `uang_muka` NUMERIC NOT NULL DEFAULT 0.00,
  `tenor_bulan` INTEGER NOT NULL DEFAULT 12,
  `tanggal_mulai_cicilan` DATE NOT NULL,
  `nilai_angsuran_bulanan` NUMERIC NOT NULL DEFAULT 0.00,
  `status` TEXT NOT NULL DEFAULT 'Berjalan',
  `catatan` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `invoices` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `nomor_invoice` TEXT NOT NULL UNIQUE,
  `debitur_id` INTEGER NOT NULL,
  `tanggal_terbit` DATE NOT NULL,
  `jatuh_tempo` DATE NOT NULL,
  `periode_bulan` INTEGER NOT NULL,
  `periode_tahun` INTEGER NOT NULL,
  `subtotal_dpp` NUMERIC NOT NULL DEFAULT 0.00,
  `total_ppn` NUMERIC NOT NULL DEFAULT 0.00,
  `grand_total` NUMERIC NOT NULL DEFAULT 0.00,
  `status_bayar` TEXT NOT NULL DEFAULT 'Belum Bayar',
  `tanggal_lunas` DATE,
  `sumber_rvbw_id` INTEGER,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `invoice_items` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `invoice_id` INTEGER NOT NULL,
  `tagihan_bulanan_kategori` TEXT,
  `keterangan` TEXT NOT NULL,
  `kode_coa` TEXT DEFAULT '40602.0600.0000',
  `meteran_pln_awal` NUMERIC,
  `meteran_pln_akhir` NUMERIC,
  `meteran_air_awal` NUMERIC,
  `meteran_air_akhir` NUMERIC,
  `pemakaian_volume` NUMERIC NOT NULL DEFAULT 0.00,
  `nilai_dpp` NUMERIC NOT NULL DEFAULT 0.00,
  `ppn_persen` NUMERIC NOT NULL DEFAULT 0.00,
  `total` NUMERIC NOT NULL DEFAULT 0.00
);

CREATE TABLE `rvbw` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `nomor_rvbw` TEXT NOT NULL UNIQUE,
  `tanggal` DATE NOT NULL,
  `debitur_id` INTEGER NOT NULL,
  `periode_bulan` INTEGER NOT NULL,
  `periode_tahun` INTEGER NOT NULL,
  `subtotal_dpp` NUMERIC NOT NULL DEFAULT 0.00,
  `total_ppn` NUMERIC NOT NULL DEFAULT 0.00,
  `grand_total` NUMERIC NOT NULL DEFAULT 0.00,
  `status` TEXT NOT NULL DEFAULT 'Draft',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `rvbw_items` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `rvbw_id` INTEGER NOT NULL,
  `kategori` TEXT NOT NULL,
  `keterangan` TEXT NOT NULL,
  `kode_coa` TEXT DEFAULT '40602.0600.0000',
  `nilai_dpp` NUMERIC NOT NULL DEFAULT 0.00,
  `nilai_ppn` NUMERIC NOT NULL DEFAULT 0.00,
  `total` NUMERIC NOT NULL DEFAULT 0.00
);

CREATE TABLE `rvbw_linked_invoices` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `rvbw_id` INTEGER NOT NULL,
  `invoice_id` INTEGER NOT NULL,
  `alokasi_nominal` NUMERIC NOT NULL DEFAULT 0.00,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `spk_records` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `nomor_spk` TEXT NOT NULL UNIQUE,
  `jenis_pekerjaan` TEXT NOT NULL DEFAULT 'Kontraktor',
  `nama_kontraktor` TEXT NOT NULL,
  `nominal_kontrak` NUMERIC NOT NULL DEFAULT 0.00,
  `pph_persen` NUMERIC NOT NULL DEFAULT 2.00,
  `pic_pekerjaan` TEXT NOT NULL,
  `keterangan` TEXT NOT NULL,
  `tanggal_spk` DATE NOT NULL,
  `status_manual` TEXT NOT NULL DEFAULT 'Belum Selesai',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `spk_payments` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `spk_id` INTEGER NOT NULL,
  `tahapan` TEXT NOT NULL,
  `nilai_pembayaran` NUMERIC NOT NULL DEFAULT 0.00,
  `nomor_voucher` TEXT NOT NULL,
  `tanggal_pembayaran` DATE NOT NULL,
  `catatan` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `titipan_rvbw` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `nomor_rvbw` TEXT NOT NULL UNIQUE,
  `entitas` TEXT NOT NULL DEFAULT 'BWJ',
  `tanggal_terima` DATE NOT NULL,
  `nominal` NUMERIC NOT NULL DEFAULT 0.00,
  `keterangan` TEXT NOT NULL,
  `status` TEXT NOT NULL DEFAULT 'Aktif',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `titipan_pvbw` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `titipan_rvbw_id` INTEGER NOT NULL,
  `nomor_pvbw` TEXT NOT NULL UNIQUE,
  `tanggal_kembali` DATE NOT NULL,
  `nominal` NUMERIC NOT NULL DEFAULT 0.00,
  `keterangan` TEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);
