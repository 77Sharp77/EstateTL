-- ==========================================================
-- KALICAA VILLA - SQLITE SEEDER
-- ==========================================================

INSERT OR REPLACE INTO `app_settings` (
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
);

INSERT OR REPLACE INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `email`, `role`, `avatar_color`) VALUES
(1, 'superadmin', '$2y$10$eA3zE4vV1gO.m3hK7yFw.uvX3w7qYp3bWz6SgE6L2NqE8vF8G0Z6m', 'Super Administrator', 'superadmin@kalicaavilla.com', 'Super Admin', '#06b6d4'),
(2, 'finance', '$2y$10$eA3zE4vV1gO.m3hK7yFw.uvX3w7qYp3bWz6SgE6L2NqE8vF8G0Z6m', 'Siti Rahmawati', 'finance@kalicaavilla.com', 'Finance Admin', '#10b981'),
(3, 'billing', '$2y$10$eA3zE4vV1gO.m3hK7yFw.uvX3w7qYp3bWz6SgE6L2NqE8vF8G0Z6m', 'Budi Billing', 'billing@kalicaavilla.com', 'Billing Officer', '#f59e0b'),
(4, 'manager', '$2y$10$eA3zE4vV1gO.m3hK7yFw.uvX3w7qYp3bWz6SgE6L2NqE8vF8G0Z6m', 'Doni Kusuma', 'manager@kalicaavilla.com', 'Resort Manager', '#8b5cf6');

INSERT OR REPLACE INTO `master_coa` (`id`, `kode_coa`, `nama_coa`, `deskripsi`) VALUES
(1, '40602.0600.0000', 'Pendapatan Recharged Listrik & Air', 'Tagihan pemakaian listrik & air yang dibebankan kembali ke owner'),
(2, '40602.0100.0000', 'Pendapatan Perawatan Lingkungan (Kawasan)', 'Iuran Pengelolaan Lingkungan (IPL) bulanan'),
(3, '40602.0200.0000', 'Pendapatan Dana Cadangan (Sinking Fund)', 'Iuran sinking fund untuk pemeliharaan gedung jangka panjang'),
(4, '40602.0300.0000', 'Pendapatan Wi-Fi & TV Kabel', 'Iuran internet broadband & TV berlangganan kawasan');

INSERT OR REPLACE INTO `debiturs` (`id`, `kode_kav`, `nama_owner`, `no_hp`, `email`, `tipe_unit`, `status`, `watt_listrik`, `luas_m2_kawasan`, `landscape_flat`, `kolam_flat`, `internet_flat`, `alamat_unit`) VALUES
(1, 'EM-1010', 'Budi Santoso', '6281234567890', 'budi.santoso@email.com', 'Villa Type A', 'aktif', 6600, 350.00, 250000.00, 300000.00, 450000.00, 'Private Residential Kalicaa Villa EM-1010'),
(2, 'EM-1025', 'Sri Wahyuni', '6282345678901', 'sri.wahyuni@email.com', 'Villa Type B', 'aktif', 10600, 420.00, 300000.00, 350000.00, 450000.00, 'Private Residential Kalicaa Villa EM-1025'),
(3, 'EM-2005', 'Ahmad Fauzi', '6283456789012', 'ahmad.fauzi@email.com', 'Villa Type A', 'aktif', 6600, 300.00, 0.00, 0.00, 450000.00, 'Private Residential Kalicaa Villa EM-2005'),
(4, 'EM-3200', 'Indira Bambang', '6285678901234', 'indira.b@email.com', 'Villa Type Royal', 'aktif', 13200, 600.00, 500000.00, 500000.00, 500000.00, 'Grand Royal Estate Kalicaa Villa EM-3200');

INSERT OR REPLACE INTO `skp_properties` (`id`, `nomor_skp`, `tanggal_skp`, `debitur_id`, `kode_kav`, `nama_owner`, `tipe_unit_skp`, `nilai_jual`, `uang_muka`, `tenor_bulan`, `tanggal_mulai_cicilan`, `nilai_angsuran_bulanan`, `status`, `catatan`) VALUES
(1, 'SKP/2026/EM-1010/001', '2026-01-15', 1, 'EM-1010', 'Budi Santoso', 'Residensi', 1500000000.00, 300000000.00, 24, '2026-02-01', 50000000.00, 'Berjalan', 'Kontrak kepemilikan unit Villa Kalicaa Blok EM-1010');

INSERT OR REPLACE INTO `spk_records` (`id`, `nomor_spk`, `jenis_pekerjaan`, `nama_kontraktor`, `nominal_kontrak`, `pph_persen`, `pic_pekerjaan`, `keterangan`, `tanggal_spk`, `status_manual`) VALUES
(1, 'SPK/2026/001/MAINT', 'Kontraktor', 'PT Mitra Beton Perkasa', 85000000.00, 2.00, 'Agus Hartono', 'Perbaikan dak atap dan waterproofing unit EM-1010 - EM-1025', '2026-04-10', 'Belum Selesai');

INSERT OR REPLACE INTO `spk_payments` (`id`, `spk_id`, `tahapan`, `nilai_pembayaran`, `nomor_voucher`, `tanggal_pembayaran`, `catatan`) VALUES
(1, 1, 'DP', 25500000.00, 'PVBW26040001', '2026-04-12', 'Pembayaran DP 30% proyek renovasi');

INSERT OR REPLACE INTO `titipan_rvbw` (`id`, `nomor_rvbw`, `entitas`, `tanggal_terima`, `nominal`, `keterangan`, `status`) VALUES
(1, 'RVBW26040001', 'BWJ', '2026-04-05', 15000000.00, 'Titipan security deposit renovasi villa kav EM-1010', 'Aktif'),
(2, 'RVTL26050002', 'TLLI', '2026-05-12', 20000000.00, 'Titipan dana event community resort', 'Aktif');

INSERT OR REPLACE INTO `titipan_pvbw` (`id`, `titipan_rvbw_id`, `nomor_pvbw`, `tanggal_kembali`, `nominal`, `keterangan`) VALUES
(1, 1, 'PVBW26050001', '2026-05-10', 5000000.00, 'Pengembalian sebagian jaminan kebersihan renovasi');

-- Sample Invoices for Kartu Piutang
INSERT OR REPLACE INTO `invoices` (`id`, `nomor_invoice`, `debitur_id`, `tanggal_terbit`, `jatuh_tempo`, `periode_bulan`, `periode_tahun`, `subtotal_dpp`, `total_ppn`, `grand_total`, `status_bayar`, `tanggal_lunas`) VALUES
(1, 'IVBW/2026/04/001', 1, '2026-04-01', '2026-04-15', 4, 2026, 4500000.00, 77000.00, 4577000.00, 'Lunas', '2026-04-14'),
(2, 'IVBW/2026/05/001', 1, '2026-05-01', '2026-05-15', 5, 2026, 4820000.00, 77000.00, 4897000.00, 'Belum Bayar', NULL),
(3, 'IVBW/2026/06/001', 1, '2026-06-01', '2026-06-15', 6, 2026, 5100000.00, 77000.00, 5177000.00, 'Belum Bayar', NULL),
(4, 'IVBW/2026/05/002', 4, '2026-05-01', '2026-05-15', 5, 2026, 120500000.00, 132000.00, 120632000.00, 'Belum Bayar', NULL),
(5, 'IVBW/2026/06/002', 4, '2026-06-01', '2026-06-15', 6, 2026, 121500000.00, 132000.00, 121604460.00, 'Belum Bayar', NULL);

INSERT OR REPLACE INTO `invoice_items` (`id`, `invoice_id`, `tagihan_bulanan_kategori`, `keterangan`, `kode_coa`, `meteran_pln_awal`, `meteran_pln_akhir`, `pemakaian_volume`, `nilai_dpp`, `ppn_persen`, `total`) VALUES
(1, 1, 'LISTRIK', 'Pemakaian Listrik PLN (500 kWh)', '40602.0600.0000', 1000, 1500, 500, 1100000.00, 0, 1100000.00),
(2, 1, 'AIR', 'Pemakaian Air PDAM (25 m³)', '40602.0600.0000', 50, 75, 25, 450000.00, 0, 450000.00),
(3, 1, 'KAWASAN', 'Iuran Pengelolaan Kawasan', '40602.0100.0000', NULL, NULL, 350, 700000.00, 11, 777000.00),
(4, 2, 'LISTRIK', 'Pemakaian Listrik PLN (550 kWh)', '40602.0600.0000', 1500, 2050, 550, 1250000.00, 0, 1250000.00),
(5, 2, 'AIR', 'Pemakaian Air PDAM (28 m³)', '40602.0600.0000', 75, 103, 28, 520000.00, 0, 520000.00),
(6, 2, 'KAWASAN', 'Iuran Pengelolaan Kawasan', '40602.0100.0000', NULL, NULL, 350, 700000.00, 11, 777000.00),
(7, 3, 'LISTRIK', 'Pemakaian Listrik PLN (600 kWh)', '40602.0600.0000', 2050, 2650, 600, 1400000.00, 0, 1400000.00),
(8, 3, 'AIR', 'Pemakaian Air PDAM (30 m³)', '40602.0600.0000', 103, 133, 30, 580000.00, 0, 580000.00),
(9, 3, 'KAWASAN', 'Iuran Pengelolaan Kawasan', '40602.0100.0000', NULL, NULL, 350, 700000.00, 11, 777000.00),
(10, 4, 'LISTRIK', 'Pemakaian Listrik PLN (12000 kWh)', '40602.0600.0000', 10000, 22000, 12000, 80000000.00, 0, 80000000.00),
(11, 4, 'KAWASAN', 'Iuran Pengelolaan Kawasan', '40602.0100.0000', NULL, NULL, 600, 1200000.00, 11, 1332000.00),
(12, 5, 'LISTRIK', 'Pemakaian Listrik PLN (12500 kWh)', '40602.0600.0000', 22000, 34500, 12500, 82000000.00, 0, 82000000.00),
(13, 5, 'KAWASAN', 'Iuran Pengelolaan Kawasan', '40602.0100.0000', NULL, NULL, 600, 1200000.00, 11, 1332000.00);
