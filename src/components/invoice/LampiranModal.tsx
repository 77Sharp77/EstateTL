import React, { useMemo } from 'react';
import { Printer, X } from 'lucide-react';
import { useApp } from '../../context/AppContext';
import { Invoice } from '../../types';
import {
  hitungTagihanAir,
  hitungTagihanKawasan,
  hitungTagihanListrik,
} from '../../utils/calculation';
import { formatDateIndo, formatNumber, formatRupiah } from '../../utils/format';

interface LampiranModalProps {
  invoice: Invoice;
  onClose: () => void;
}

export const LampiranModal: React.FC<LampiranModalProps> = ({ invoice, onClose }) => {
  const { debiturs, invoiceItems, settings } = useApp();

  const debitur = useMemo(() => {
    return debiturs.find((d) => d.id === invoice.debitur_id);
  }, [debiturs, invoice.debitur_id]);

  const items = useMemo(() => {
    return invoiceItems.filter((it) => it.invoice_id === invoice.id);
  }, [invoiceItems, invoice.id]);

  const itemListrik = items.find(
    (it) => it.tagihan_bulanan_kategori === 'LISTRIK' || it.keterangan.includes('Listrik')
  );
  const itemAir = items.find(
    (it) => it.tagihan_bulanan_kategori === 'AIR' || it.keterangan.includes('Air')
  );
  const itemKawasan = items.find(
    (it) => it.tagihan_bulanan_kategori === 'KAWASAN' || it.keterangan.includes('Kawasan')
  );

  const calcListrik = useMemo(() => {
    if (!itemListrik || !debitur) return null;
    const awal = itemListrik.meteran_pln_awal ?? 0;
    const akhir = itemListrik.meteran_pln_akhir ?? itemListrik.pemakaian_volume;
    return hitungTagihanListrik(awal, akhir, debitur.watt_listrik, settings);
  }, [itemListrik, debitur, settings]);

  const calcAir = useMemo(() => {
    if (!itemAir) return null;
    const awal = itemAir.meteran_air_awal ?? 0;
    const akhir = itemAir.meteran_air_akhir ?? itemAir.pemakaian_volume;
    return hitungTagihanAir(awal, akhir, settings);
  }, [itemAir, settings]);

  const calcKawasan = useMemo(() => {
    if (!debitur) return null;
    return hitungTagihanKawasan(debitur.luas_m2_kawasan, settings);
  }, [debitur, settings]);

  return (
    <div className="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-2 sm:p-4 overflow-y-auto">
      <div className="bg-white text-slate-900 rounded-xl max-w-4xl w-full p-6 sm:p-8 shadow-2xl relative my-auto max-h-[95vh] overflow-y-auto print:max-w-none print:m-0 print:p-6 print:shadow-none print:rounded-none">
        {/* Toolbar */}
        <div className="no-print flex items-center justify-between pb-4 mb-4 border-b border-slate-200">
          <div className="text-xs text-slate-500 font-semibold uppercase tracking-wider">
            Lampiran Rincian & Formula Perhitungan Meteran (Utility & Kawasan)
          </div>
          <div className="flex items-center gap-2">
            <button
              onClick={() => window.print()}
              className="flex items-center gap-1.5 px-4 py-2 bg-cyan-700 hover:bg-cyan-800 text-white rounded-lg text-xs font-bold shadow-md transition-colors"
            >
              <Printer className="w-4 h-4" />
              <span>Cetak Lampiran</span>
            </button>
            <button
              onClick={onClose}
              className="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors"
            >
              <X className="w-5 h-5" />
            </button>
          </div>
        </div>

        {/* --- PRINTABLE CONTENT --- */}
        <div className="text-[11px] leading-normal font-sans">
          {/* Header */}
          <table className="w-full border-b-2 border-black pb-2 mb-4">
            <tbody>
              <tr>
                <td>
                  <div className="text-base font-bold text-cyan-800 uppercase">
                    {settings.nama_perusahaan}
                  </div>
                  <div className="text-xs font-bold text-slate-700">{settings.sub_nama}</div>
                </td>
                <td className="text-right align-middle">
                  <div className="font-bold uppercase text-xs">Lampiran Perhitungan Meteran</div>
                  <div className="font-mono text-xs text-slate-600">
                    Ref: {invoice.nomor_invoice}
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <div className="text-center my-3">
            <h2 className="text-sm font-bold uppercase tracking-wide">
              RINCIAN PERHITUNGAN PEMAKAIAN LISTRIK, AIR & KAWASAN
            </h2>
            <p className="text-xs font-medium text-slate-700 mt-0.5">
              Unit: <strong>{debitur?.kode_kav}</strong> - {debitur?.nama_owner} (Periode: Bulan{' '}
              {invoice.periode_bulan}/{invoice.periode_tahun})
            </p>
          </div>

          {/* 1. PLN Calculation */}
          {calcListrik && debitur && (
            <div className="border border-slate-700 rounded mb-4 overflow-hidden">
              <div className="bg-slate-100 px-3 py-1.5 font-bold uppercase text-[10px] border-b border-slate-700">
                1. Perhitungan Listrik PLN (Daya: {formatNumber(debitur.watt_listrik)} VA)
              </div>
              <table className="w-full text-[10.5px]">
                <tbody className="divide-y divide-slate-200">
                  <tr>
                    <td className="py-1 px-3 w-2/5">Meteran Awal s.d. Akhir</td>
                    <td className="py-1 px-3 font-mono font-bold">
                      {itemListrik?.meteran_pln_awal ?? 0} s.d. {itemListrik?.meteran_pln_akhir ?? 0}{' '}
                      (Pemakaian: {formatNumber(calcListrik.pemakaian_kwh, 2)} kWh)
                    </td>
                  </tr>
                  <tr>
                    <td className="py-1 px-3">Ambang Rekening Minimum (RM = 40 Jam Nyala)</td>
                    <td className="py-1 px-3 font-mono">
                      {formatNumber(debitur.watt_listrik)} VA × {settings.faktor_rekening_minimum} ={' '}
                      <strong>{formatNumber(calcListrik.ambang_rm_kwh, 1)} kWh</strong>{' '}
                      {calcListrik.rm_terpakai ? (
                        <span className="text-amber-700 font-bold ml-2">
                          [Dikenakan RM karena pemakaian &lt;= batas]
                        </span>
                      ) : (
                        <span className="text-emerald-700 font-bold ml-2">
                          [Pemakaian Normal di atas RM]
                        </span>
                      )}
                    </td>
                  </tr>
                  <tr>
                    <td className="py-1 px-3">Tagihan Dasar (Tarif Rp {formatNumber(settings.tarif_pln_per_kwh, 2)}/kWh)</td>
                    <td className="py-1 px-3 font-mono font-bold">
                      {formatRupiah(calcListrik.tagihan_dasar)}
                    </td>
                  </tr>
                  <tr>
                    <td className="py-1 px-3">Biaya Loses ({settings.persen_loses}%)</td>
                    <td className="py-1 px-3 font-mono">
                      {calcListrik.rm_terpakai
                        ? 'Rp 0 (Pembebasan Loses pada RM)'
                        : formatRupiah(calcListrik.biaya_loses)}
                    </td>
                  </tr>
                  <tr>
                    <td className="py-1 px-3">Pajak Penerangan Jalan (PPJ {settings.persen_ppj}%)</td>
                    <td className="py-1 px-3 font-mono">{formatRupiah(calcListrik.biaya_ppj)}</td>
                  </tr>
                  <tr>
                    <td className="py-1 px-3">Jasa Pengelolaan Listrik ({settings.persen_jasa_listrik_air}%)</td>
                    <td className="py-1 px-3 font-mono">{formatRupiah(calcListrik.jasa)}</td>
                  </tr>
                  <tr className="bg-amber-50 font-bold">
                    <td className="py-1.5 px-3">Subtotal Tagihan Listrik PLN</td>
                    <td className="py-1.5 px-3 font-mono text-amber-900">
                      {formatRupiah(calcListrik.total)}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          )}

          {/* 2. PDAM Water Calculation */}
          {calcAir && (
            <div className="border border-slate-700 rounded mb-4 overflow-hidden">
              <div className="bg-slate-100 px-3 py-1.5 font-bold uppercase text-[10px] border-b border-slate-700">
                2. Perhitungan Air Bersih PDAM (Sistem Tiering Tarif Progresif)
              </div>
              <table className="w-full text-[10.5px]">
                <tbody className="divide-y divide-slate-200">
                  <tr>
                    <td className="py-1 px-3 w-2/5">Meteran Awal s.d. Akhir</td>
                    <td className="py-1 px-3 font-mono font-bold">
                      {itemAir?.meteran_air_awal ?? 0} s.d. {itemAir?.meteran_air_akhir ?? 0}{' '}
                      (Pemakaian: {formatNumber(calcAir.pemakaian_m3, 2)} m³)
                    </td>
                  </tr>
                  <tr>
                    <td className="py-1 px-3">Biaya Abodemen Bulanan</td>
                    <td className="py-1 px-3 font-mono">{formatRupiah(calcAir.biaya_abodemen)}</td>
                  </tr>
                  <tr>
                    <td className="py-1 px-3">
                      Tier 1 (0 - {settings.tarif_air_tier1_batas} m³ @ Rp{' '}
                      {formatNumber(settings.tarif_air_tier1)})
                    </td>
                    <td className="py-1 px-3 font-mono">
                      {formatNumber(calcAir.vol_tier1)} m³ = {formatRupiah(calcAir.biaya_tier1)}
                    </td>
                  </tr>
                  <tr>
                    <td className="py-1 px-3">
                      Tier 2 ({settings.tarif_air_tier1_batas} - {settings.tarif_air_tier2_batas} m³ @ Rp{' '}
                      {formatNumber(settings.tarif_air_tier2)})
                    </td>
                    <td className="py-1 px-3 font-mono">
                      {formatNumber(calcAir.vol_tier2)} m³ = {formatRupiah(calcAir.biaya_tier2)}
                    </td>
                  </tr>
                  <tr>
                    <td className="py-1 px-3">
                      Tier 3 (&gt; {settings.tarif_air_tier2_batas} m³ @ Rp{' '}
                      {formatNumber(settings.tarif_air_tier3)})
                    </td>
                    <td className="py-1 px-3 font-mono">
                      {formatNumber(calcAir.vol_tier3)} m³ = {formatRupiah(calcAir.biaya_tier3)}
                    </td>
                  </tr>
                  <tr>
                    <td className="py-1 px-3">Jasa Pengelolaan Air ({settings.persen_jasa_listrik_air}%)</td>
                    <td className="py-1 px-3 font-mono">{formatRupiah(calcAir.jasa)}</td>
                  </tr>
                  <tr className="bg-cyan-50 font-bold">
                    <td className="py-1.5 px-3">Subtotal Tagihan Air PDAM</td>
                    <td className="py-1.5 px-3 font-mono text-cyan-900">
                      {formatRupiah(calcAir.total)}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          )}

          {/* 3. Kawasan & Flat Calculation */}
          {calcKawasan && debitur && (
            <div className="border border-slate-700 rounded mb-4 overflow-hidden">
              <div className="bg-slate-100 px-3 py-1.5 font-bold uppercase text-[10px] border-b border-slate-700">
                3. Perhitungan Iuran Pengelolaan Kawasan & Layanan Lingkungan
              </div>
              <table className="w-full text-[10.5px]">
                <tbody className="divide-y divide-slate-200">
                  <tr>
                    <td className="py-1 px-3 w-2/5">Luas Kawasan Unit</td>
                    <td className="py-1 px-3 font-mono font-bold">
                      {formatNumber(debitur.luas_m2_kawasan)} m² @ Rp{' '}
                      {formatNumber(settings.tarif_kawasan_per_m2)} ={' '}
                      {formatRupiah(calcKawasan.total_sebelum_fee)}
                    </td>
                  </tr>
                  <tr>
                    <td className="py-1 px-3">Management Fee Kawasan ({settings.persen_fee_kawasan}%)</td>
                    <td className="py-1 px-3 font-mono">{formatRupiah(calcKawasan.fee)}</td>
                  </tr>
                  <tr>
                    <td className="py-1 px-3">Subtotal DPP Kawasan</td>
                    <td className="py-1 px-3 font-mono font-bold">
                      {formatRupiah(calcKawasan.subtotal_dpp)}
                    </td>
                  </tr>
                  <tr>
                    <td className="py-1 px-3">PPN Kawasan ({settings.ppn_default}%)</td>
                    <td className="py-1 px-3 font-mono">{formatRupiah(calcKawasan.ppn)}</td>
                  </tr>
                  <tr className="bg-emerald-50 font-bold">
                    <td className="py-1.5 px-3">Subtotal Iuran Kawasan (inc. PPN)</td>
                    <td className="py-1.5 px-3 font-mono text-emerald-900">
                      {formatRupiah(calcKawasan.total)}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          )}

          {/* Signatures */}
          <div className="flex justify-between items-end mt-6 pt-4 border-t border-slate-300 text-center">
            <div className="w-1/3">
              <div className="text-[10px] text-slate-500">Diverifikasi Oleh:</div>
              <div className="h-14"></div>
              <div className="font-bold border-t border-slate-400 pt-1 text-xs">
                Billing Officer
              </div>
            </div>
            <div className="w-1/3">
              <div className="text-[10px] text-slate-500">Disetujui Oleh:</div>
              <div className="h-14"></div>
              <div className="font-bold border-t border-slate-400 pt-1 text-xs">
                Finance / Resort Manager
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
