export interface AppSettings {
  id: number;
  nama_perusahaan: string;
  sub_nama: string;
  alamat: string;
  telepon: string;
  nama_bank: string;
  no_rekening: string;
  atas_nama: string;
  ppn_default: number;
  prefix_rvbw: string;
  invoice_counter: number;
  tarif_pln_per_kwh: number;
  faktor_rekening_minimum: number;
  persen_loses: number;
  persen_ppj: number;
  persen_jasa_listrik_air: number;
  tarif_kawasan_per_m2: number;
  persen_fee_kawasan: number;
  tarif_air_abodemen: number;
  tarif_air_tier1_batas: number;
  tarif_air_tier1: number;
  tarif_air_tier2_batas: number;
  tarif_air_tier2: number;
  tarif_air_tier3: number;
  updated_at?: string;
}

export interface User {
  id: number;
  username: string;
  nama_lengkap: string;
  email: string;
  role: 'Super Admin' | 'Finance Admin' | 'Billing Officer' | 'Resort Manager';
  avatar_color: string;
}

export interface Debitur {
  id: number;
  kode_kav: string;
  nama_owner: string;
  no_hp: string;
  email?: string;
  tipe_unit: string;
  status: 'aktif' | 'nonaktif';
  watt_listrik: number;
  luas_m2_kawasan: number;
  status_meteran?: string;
  alamat_korespondensi?: string;
  status_kepemilikan?: string;
  landscape_flat: number;
  kolam_flat: number;
  internet_flat: number;
  alamat_unit?: string;
  created_at?: string;
}

export interface InvoiceItem {
  id: number;
  invoice_id: number;
  tagihan_bulanan_kategori?: 'LISTRIK' | 'AIR' | 'KAWASAN' | 'INTERNET' | 'LAIN';
  keterangan: string;
  kode_coa: string;
  meteran_pln_awal?: number | null;
  meteran_pln_akhir?: number | null;
  meteran_air_awal?: number | null;
  meteran_air_akhir?: number | null;
  pemakaian_volume: number;
  nilai_dpp: number;
  ppn_persen: number;
  total: number;
}

export interface Invoice {
  id: number;
  nomor_invoice: string;
  debitur_id: number;
  tanggal_terbit: string;
  jatuh_tempo: string;
  periode_bulan: number;
  periode_tahun: number;
  subtotal_dpp: number;
  total_ppn: number;
  grand_total: number;
  status_bayar: 'Belum Bayar' | 'Lunas';
  tanggal_lunas?: string | null;
  sumber_rvbw_id?: number | null;
  created_at?: string;
}

export interface RvbwRecord {
  id: number;
  nomor_rvbw: string;
  tanggal: string;
  debitur_id: number;
  periode_bulan: number;
  periode_tahun: number;
  subtotal_dpp: number;
  total_ppn: number;
  grand_total: number;
  status: 'Draft' | 'Disetujui' | 'Diposting';
  created_at?: string;
}

export interface SkpProperty {
  id: number;
  nomor_skp: string;
  tanggal_skp: string;
  debitur_id: number;
  kode_kav: string;
  nama_owner: string;
  tipe_unit_skp: string;
  nilai_jual: number;
  uang_muka: number;
  tenor_bulan: number;
  tanggal_mulai_cicilan: string;
  nilai_angsuran_bulanan: number;
  status: 'Berjalan' | 'Lunas' | 'Batal';
  catatan?: string;
  created_at?: string;
}

export interface SpkRecord {
  id: number;
  nomor_spk: string;
  jenis_pekerjaan: string;
  nama_kontraktor: string;
  nominal_kontrak: number;
  pph_persen: number;
  pic_pekerjaan: string;
  keterangan: string;
  tanggal_spk: string;
  status_manual: 'Belum Selesai' | 'Selesai';
  created_at?: string;
}

export interface SpkPayment {
  id: number;
  spk_id: number;
  tahapan: string;
  nilai_pembayaran: number;
  nomor_voucher: string;
  tanggal_pembayaran: string;
  catatan?: string;
  created_at?: string;
}

export interface TitipanRvbw {
  id: number;
  nomor_rvbw: string;
  entitas: 'BWJ' | 'TLLI';
  tanggal_terima: string;
  nominal: number;
  keterangan: string;
  status: 'Aktif' | 'Selesai';
  created_at?: string;
}

export interface TitipanPvbw {
  id: number;
  titipan_rvbw_id: number;
  nomor_pvbw: string;
  tanggal_kembali: string;
  nominal: number;
  keterangan: string;
  created_at?: string;
}

export interface MasterCoa {
  id: number;
  kode_coa: string;
  nama_coa: string;
  deskripsi?: string;
}

export interface KartuEntry {
  id: number;
  tanggal: string;
  nomor_invoice: string;
  periode_bulan: number;
  periode_tahun: number;
  periode_label: string;
  debit: number;
  kredit: number;
  saldo: number;
  status_bayar: 'Belum Bayar' | 'Lunas';
  tgl_bayar?: string | null;
  breakdown: {
    listrik: number;
    air: number;
    kawasan: number;
    lain: number;
  };
}

export interface KartuDebitur {
  debitur_id: number;
  kode_kav: string;
  nama_owner: string;
  no_hp: string;
  total_debit: number;
  total_kredit: number;
  saldo_akhir: number;
  entri: KartuEntry[];
}
