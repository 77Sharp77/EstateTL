-- ==========================================================
-- KALICAA VILLA - DATABASE SEEDER SQL
-- ==========================================================

-- 1. App Settings
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

-- 2. Master Users (Default password: password123 / md5/bcrypt)
-- Hash generated using standard password_hash('password123', PASSWORD_BCRYPT)
INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `email`, `role`, `avatar_color`) VALUES
(1, 'superadmin', '$2y$10$eA3zE4vV1gO.m3hK7yFw.uvX3w7qYp3bWz6SgE6L2NqE8vF8G0Z6m', 'Super Administrator', 'superadmin@kalicaavilla.com', 'Super Admin', '#06b6d4'),
(2, 'finance', '$2y$10$eA3zE4vV1gO.m3hK7yFw.uvX3w7qYp3bWz6SgE6L2NqE8vF8G0Z6m', 'Siti Rahmawati', 'finance@kalicaavilla.com', 'Finance Admin', '#10b981'),
(3, 'billing', '$2y$10$eA3zE4vV1gO.m3hK7yFw.uvX3w7qYp3bWz6SgE6L2NqE8vF8G0Z6m', 'Budi Billing', 'billing@kalicaavilla.com', 'Billing Officer', '#f59e0b'),
(4, 'manager', '$2y$10$eA3zE4vV1gO.m3hK7yFw.uvX3w7qYp3bWz6SgE6L2NqE8vF8G0Z6m', 'Doni Kusuma', 'manager@kalicaavilla.com', 'Resort Manager', '#8b5cf6')
ON DUPLICATE KEY UPDATE `username`=VALUES(`username`);

-- 3. Master COA
INSERT INTO `master_coa` (`id`, `kode_coa`, `nama_coa`, `deskripsi`) VALUES
(1, '40602.0600.0000', 'Pendapatan Recharged Listrik & Air', 'Tagihan pemakaian listrik & air yang dibebankan kembali ke owner'),
(2, '40602.0100.0000', 'Pendapatan Perawatan Lingkungan (Kawasan)', 'Iuran Pengelolaan Lingkungan (IPL) bulanan'),
(3, '40602.0200.0000', 'Pendapatan Dana Cadangan (Sinking Fund)', 'Iuran sinking fund untuk pemeliharaan gedung jangka panjang'),
(4, '40602.0300.0000', 'Pendapatan Wi-Fi & TV Kabel', 'Iuran internet broadband & TV berlangganan kawasan')
ON DUPLICATE KEY UPDATE `kode_coa`=VALUES(`kode_coa`);

-- 4. Debitur Sample
INSERT INTO `debiturs` (`id`, `kode_kav`, `nama_owner`, `no_hp`, `email`, `tipe_unit`, `status`, `watt_listrik`, `luas_m2_kawasan`, `landscape_flat`, `kolam_flat`, `internet_flat`, `alamat_unit`) VALUES
(1, 'EM-1010', 'Budi Santoso', '6281234567890', 'budi.santoso@email.com', 'Villa Type A', 'aktif', 6600, 350.00, 250000.00, 300000.00, 450000.00, 'Private Residential Kalicaa Villa EM-1010'),
(2, 'EM-1025', 'Sri Wahyuni', '6282345678901', 'sri.wahyuni@email.com', 'Villa Type B', 'aktif', 10600, 420.00, 300000.00, 350000.00, 450000.00, 'Private Residential Kalicaa Villa EM-1025'),
(3, 'EM-2005', 'Ahmad Fauzi', '6283456789012', 'ahmad.fauzi@email.com', 'Villa Type A', 'aktif', 6600, 300.00, 0.00, 0.00, 450000.00, 'Private Residential Kalicaa Villa EM-2005'),
(4, 'EM-3200', 'Indira Bambang', '6285678901234', 'indira.b@email.com', 'Villa Type Royal', 'aktif', 13200, 600.00, 500000.00, 500000.00, 500000.00, 'Grand Royal Estate Kalicaa Villa EM-3200')
ON DUPLICATE KEY UPDATE `kode_kav`=VALUES(`kode_kav`);

-- 5. Master SKP Sample
INSERT INTO `skp_properties` (`id`, `nomor_skp`, `tanggal_skp`, `debitur_id`, `kode_kav`, `nama_owner`, `tipe_unit_skp`, `nilai_jual`, `uang_muka`, `tenor_bulan`, `tanggal_mulai_cicilan`, `nilai_angsuran_bulanan`, `status`, `catatan`) VALUES
(1, 'SKP/2026/EM-1010/001', '2026-01-15', 1, 'EM-1010', 'Budi Santoso', 'Residensi', 1500000000.00, 300000000.00, 24, '2026-02-01', 50000000.00, 'Berjalan', 'Kontrak kepemilikan unit Villa Kalicaa Blok EM-1010')
ON DUPLICATE KEY UPDATE `nomor_skp`=VALUES(`nomor_skp`);

-- 6. Sample SPK
INSERT INTO `spk_records` (`id`, `nomor_spk`, `jenis_pekerjaan`, `nama_kontraktor`, `nominal_kontrak`, `pph_persen`, `pic_pekerjaan`, `keterangan`, `tanggal_spk`, `status_manual`) VALUES
(1, 'SPK/2026/001/MAINT', 'Kontraktor', 'PT Mitra Beton Perkasa', 85000000.00, 2.00, 'Agus Hartono', 'Perbaikan dak atap dan waterproofing unit EM-1010 - EM-1025', '2026-04-10', 'Belum Selesai')
ON DUPLICATE KEY UPDATE `nomor_spk`=VALUES(`nomor_spk`);

INSERT INTO `spk_payments` (`id`, `spk_id`, `tahapan`, `nilai_pembayaran`, `nomor_voucher`, `tanggal_pembayaran`, `catatan`) VALUES
(1, 1, 'DP', 25500000.00, 'PVBW26040001', '2026-04-12', 'Pembayaran DP 30% proyek renovasi')
ON DUPLICATE KEY UPDATE `nomor_voucher`=VALUES(`nomor_voucher`);

-- 7. Sample Titipan
INSERT INTO `titipan_rvbw` (`id`, `nomor`, `entitas`, `tanggal_terima`, `keterangan`, `nominal`, `status`, `total_dikembalikan`, `sisa_titipan`) VALUES
(1, 'RVBW26050001', 'BWJ', '2026-05-02', 'Titipan dana jaminan renovasi unit Budi Santoso', 10000000.00, 'Titipan', 0.00, 10000000.00)
ON DUPLICATE KEY UPDATE `nomor`=VALUES(`nomor`);
