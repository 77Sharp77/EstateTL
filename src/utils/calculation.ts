import { AppSettings } from '../types';

export interface HitungListrikResult {
  pemakaian_kwh: number;
  ambang_rm_kwh: number;
  rm_terpakai: boolean;
  tagihan_dasar: number;
  biaya_loses: number;
  biaya_ppj: number;
  subtotal: number;
  jasa: number;
  total: number;
}

export function hitungTagihanListrik(
  meteranAwal: number,
  meteranAkhir: number,
  wattListrik: number,
  settings: AppSettings
): HitungListrikResult {
  const pemakaian_kwh = Math.max(0, meteranAkhir - meteranAwal);
  const faktor_rm = Number(settings.faktor_rekening_minimum) || 0.04;
  const tarif_kwh = Number(settings.tarif_pln_per_kwh) || 1699.53;
  const persen_loses = Number(settings.persen_loses) || 5.0;
  const persen_ppj = Number(settings.persen_ppj) || 5.0;
  const persen_jasa = Number(settings.persen_jasa_listrik_air) || 10.0;

  const ambang_rm_kwh = wattListrik * faktor_rm;
  const rm_terpakai = pemakaian_kwh <= ambang_rm_kwh;

  const tagihan_dasar = rm_terpakai
    ? ambang_rm_kwh * tarif_kwh
    : pemakaian_kwh * tarif_kwh;

  const biaya_loses = rm_terpakai ? 0 : tagihan_dasar * (persen_loses / 100);
  const biaya_ppj = (tagihan_dasar + biaya_loses) * (persen_ppj / 100);
  const subtotal = tagihan_dasar + biaya_loses + biaya_ppj;
  const jasa = subtotal * (persen_jasa / 100);
  const total = subtotal + jasa;

  return {
    pemakaian_kwh: Math.round(pemakaian_kwh * 100) / 100,
    ambang_rm_kwh: Math.round(ambang_rm_kwh * 100) / 100,
    rm_terpakai,
    tagihan_dasar: Math.round(tagihan_dasar * 100) / 100,
    biaya_loses: Math.round(biaya_loses * 100) / 100,
    biaya_ppj: Math.round(biaya_ppj * 100) / 100,
    subtotal: Math.round(subtotal * 100) / 100,
    jasa: Math.round(jasa * 100) / 100,
    total: Math.round(total * 100) / 100,
  };
}

export interface HitungAirResult {
  pemakaian_m3: number;
  vol_tier1: number;
  vol_tier2: number;
  vol_tier3: number;
  biaya_abodemen: number;
  biaya_tier1: number;
  biaya_tier2: number;
  biaya_tier3: number;
  subtotal: number;
  jasa: number;
  total: number;
}

export function hitungTagihanAir(
  meteranAwal: number,
  meteranAkhir: number,
  settings: AppSettings
): HitungAirResult {
  const pemakaian_m3 = Math.max(0, meteranAkhir - meteranAwal);
  const b1 = Number(settings.tarif_air_tier1_batas) || 20;
  const b2 = Number(settings.tarif_air_tier2_batas) || 30;

  const vol_tier1 = Math.min(pemakaian_m3, b1);
  const vol_tier2 = Math.min(Math.max(pemakaian_m3 - b1, 0), b2 - b1);
  const vol_tier3 = Math.max(pemakaian_m3 - b2, 0);

  const biaya_abodemen = Number(settings.tarif_air_abodemen) || 22000;
  const biaya_tier1 = vol_tier1 * (Number(settings.tarif_air_tier1) || 9700);
  const biaya_tier2 = vol_tier2 * (Number(settings.tarif_air_tier2) || 10785);
  const biaya_tier3 = vol_tier3 * (Number(settings.tarif_air_tier3) || 11445);

  const subtotal = biaya_abodemen + biaya_tier1 + biaya_tier2 + biaya_tier3;
  const persen_jasa = Number(settings.persen_jasa_listrik_air) || 10;
  const jasa = subtotal * (persen_jasa / 100);
  const total = subtotal + jasa;

  return {
    pemakaian_m3: Math.round(pemakaian_m3 * 100) / 100,
    vol_tier1: Math.round(vol_tier1 * 100) / 100,
    vol_tier2: Math.round(vol_tier2 * 100) / 100,
    vol_tier3: Math.round(vol_tier3 * 100) / 100,
    biaya_abodemen: Math.round(biaya_abodemen * 100) / 100,
    biaya_tier1: Math.round(biaya_tier1 * 100) / 100,
    biaya_tier2: Math.round(biaya_tier2 * 100) / 100,
    biaya_tier3: Math.round(biaya_tier3 * 100) / 100,
    subtotal: Math.round(subtotal * 100) / 100,
    jasa: Math.round(jasa * 100) / 100,
    total: Math.round(total * 100) / 100,
  };
}

export interface HitungKawasanResult {
  luas_m2: number;
  total_sebelum_fee: number;
  fee: number;
  subtotal_dpp: number;
  ppn: number;
  total: number;
}

export function hitungTagihanKawasan(
  luasM2: number,
  settings: AppSettings
): HitungKawasanResult {
  const tarif = Number(settings.tarif_kawasan_per_m2) || 2000;
  const feePct = Number(settings.persen_fee_kawasan) || 4;
  const ppnPct = Number(settings.ppn_default) || 11;

  const total_sebelum_fee = luasM2 * tarif;
  const fee = total_sebelum_fee * (feePct / 100);
  const subtotal_dpp = total_sebelum_fee + fee;
  const ppn = subtotal_dpp * (ppnPct / 100);
  const total = subtotal_dpp + ppn;

  return {
    luas_m2: Math.round(luasM2 * 100) / 100,
    total_sebelum_fee: Math.round(total_sebelum_fee * 100) / 100,
    fee: Math.round(fee * 100) / 100,
    subtotal_dpp: Math.round(subtotal_dpp * 100) / 100,
    ppn: Math.round(ppn * 100) / 100,
    total: Math.round(total * 100) / 100,
  };
}
