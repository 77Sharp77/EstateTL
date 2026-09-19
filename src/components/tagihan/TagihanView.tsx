import React, { useMemo, useState } from 'react';
import {
  AlertTriangle,
  ArrowRight,
  Calculator,
  CheckCircle2,
  Droplet,
  FileCheck,
  Flame,
  Info,
  Layers,
  Save,
  Trees,
  Wifi,
  Zap,
} from 'lucide-react';
import { useApp } from '../../context/AppContext';
import {
  hitungTagihanAir,
  hitungTagihanKawasan,
  hitungTagihanListrik,
} from '../../utils/calculation';
import { InvoiceItem } from '../../types';
import {
  formatNumber,
  formatRupiah,
  generateInvoiceNumber,
  generateRvbwNumber,
} from '../../utils/format';

export const TagihanView: React.FC = () => {
  const {
    debiturs,
    settings,
    addInvoice,
    addRvbw,
    setActiveTab,
    setSelectedInvoiceForPrint,
  } = useApp();

  // Selected Debitur
  const [selectedDebiturId, setSelectedDebiturId] = useState<number>(
    debiturs.length > 0 ? debiturs[0].id : 1
  );

  // Period
  const [periodeBulan, setPeriodeBulan] = useState<number>(new Date().getMonth() + 1);
  const [periodeTahun, setPeriodeTahun] = useState<number>(new Date().getFullYear());

  // Meter inputs
  const [plnAwal, setPlnAwal] = useState<number>(1500);
  const [plnAkhir, setPlnAkhir] = useState<number>(2150);

  const [airAwal, setAirAwal] = useState<number>(50);
  const [airAkhir, setAirAkhir] = useState<number>(78);

  const [landscapeCustom, setLandscapeCustom] = useState<number | null>(null);
  const [kolamCustom, setKolamCustom] = useState<number | null>(null);
  const [internetCustom, setInternetCustom] = useState<number | null>(null);

  const [saveSuccessMessage, setSaveSuccessMessage] = useState<string | null>(null);

  const debitur = useMemo(() => {
    return debiturs.find((d) => d.id === selectedDebiturId) || debiturs[0];
  }, [debiturs, selectedDebiturId]);

  // Handle Debitur change to populate default flat fees
  const handleDebiturChange = (id: number) => {
    setSelectedDebiturId(id);
    const d = debiturs.find((item) => item.id === id);
    if (d) {
      setLandscapeCustom(d.landscape_flat);
      setKolamCustom(d.kolam_flat);
      setInternetCustom(d.internet_flat);
    }
  };

  const landscapeFee = landscapeCustom !== null ? landscapeCustom : debitur?.landscape_flat || 0;
  const kolamFee = kolamCustom !== null ? kolamCustom : debitur?.kolam_flat || 0;
  const internetFee = internetCustom !== null ? internetCustom : debitur?.internet_flat || 0;

  // Live Calculations
  const listrikCalc = useMemo(() => {
    if (!debitur) return null;
    return hitungTagihanListrik(plnAwal, plnAkhir, debitur.watt_listrik, settings);
  }, [plnAwal, plnAkhir, debitur, settings]);

  const airCalc = useMemo(() => {
    return hitungTagihanAir(airAwal, airAkhir, settings);
  }, [airAwal, airAkhir, settings]);

  const kawasanCalc = useMemo(() => {
    if (!debitur) return null;
    return hitungTagihanKawasan(debitur.luas_m2_kawasan, settings);
  }, [debitur, settings]);

  const grandTotal = useMemo(() => {
    const listr = listrikCalc?.total || 0;
    const air = airCalc?.total || 0;
    const kaw = kawasanCalc?.total || 0;
    const flat = landscapeFee + kolamFee + internetFee;
    return listr + air + kaw + flat;
  }, [listrikCalc, airCalc, kawasanCalc, landscapeFee, kolamFee, internetFee]);

  const subtotalDpp = useMemo(() => {
    const lDpp = (listrikCalc?.subtotal || 0) + (listrikCalc?.jasa || 0);
    const aDpp = (airCalc?.subtotal || 0) + (airCalc?.jasa || 0);
    const kDpp = kawasanCalc?.subtotal_dpp || 0;
    const fDpp = landscapeFee + kolamFee + internetFee;
    return lDpp + aDpp + kDpp + fDpp;
  }, [listrikCalc, airCalc, kawasanCalc, landscapeFee, kolamFee, internetFee]);

  const totalPpn = useMemo(() => {
    return kawasanCalc?.ppn || 0;
  }, [kawasanCalc]);

  // Actions
  const handleCreateInvoiceDirect = () => {
    if (!debitur || !listrikCalc || !airCalc || !kawasanCalc) return;

    const nomorInvoice = generateInvoiceNumber(
      settings.invoice_counter + 1,
      periodeTahun,
      periodeBulan
    );

    const nowStr = new Date().toISOString().split('T')[0];
    const dueDate = new Date();
    dueDate.setDate(dueDate.getDate() + 14);
    const dueStr = dueDate.toISOString().split('T')[0];

    const invoiceItemsData: Omit<InvoiceItem, 'id' | 'invoice_id'>[] = [
      {
        tagihan_bulanan_kategori: 'LISTRIK',
        keterangan: `Pemakaian Listrik PLN (${listrikCalc.pemakaian_kwh} kWh)`,
        kode_coa: '40602.0600.0000',
        meteran_pln_awal: plnAwal,
        meteran_pln_akhir: plnAkhir,
        pemakaian_volume: listrikCalc.pemakaian_kwh,
        nilai_dpp: listrikCalc.subtotal + listrikCalc.jasa,
        ppn_persen: 0,
        total: listrikCalc.total,
      },
      {
        tagihan_bulanan_kategori: 'AIR',
        keterangan: `Pemakaian Air PDAM (${airCalc.pemakaian_m3} m³)`,
        kode_coa: '40602.0600.0000',
        meteran_air_awal: airAwal,
        meteran_air_akhir: airAkhir,
        pemakaian_volume: airCalc.pemakaian_m3,
        nilai_dpp: airCalc.subtotal + airCalc.jasa,
        ppn_persen: 0,
        total: airCalc.total,
      },
      {
        tagihan_bulanan_kategori: 'KAWASAN',
        keterangan: `Iuran Pengelolaan Kawasan (${debitur.luas_m2_kawasan} m²)`,
        kode_coa: '40602.0100.0000',
        pemakaian_volume: debitur.luas_m2_kawasan,
        nilai_dpp: kawasanCalc.subtotal_dpp,
        ppn_persen: settings.ppn_default,
        total: kawasanCalc.total,
      },
    ];

    if (internetFee > 0) {
      invoiceItemsData.push({
        tagihan_bulanan_kategori: 'INTERNET',
        keterangan: `Iuran Internet & Wi-Fi Broadband`,
        kode_coa: '40602.0300.0000',
        pemakaian_volume: 1,
        nilai_dpp: internetFee,
        ppn_persen: 0,
        total: internetFee,
      });
    }

    if (landscapeFee > 0) {
      invoiceItemsData.push({
        tagihan_bulanan_kategori: 'LAIN',
        keterangan: `Perawatan Landscape & Taman`,
        kode_coa: '40602.0100.0000',
        pemakaian_volume: 1,
        nilai_dpp: landscapeFee,
        ppn_persen: 0,
        total: landscapeFee,
      });
    }

    if (kolamFee > 0) {
      invoiceItemsData.push({
        tagihan_bulanan_kategori: 'LAIN',
        keterangan: `Perawatan Kolam Renang Privat`,
        kode_coa: '40602.0100.0000',
        pemakaian_volume: 1,
        nilai_dpp: kolamFee,
        ppn_persen: 0,
        total: kolamFee,
      });
    }

    const newInv = addInvoice(
      {
        nomor_invoice: nomorInvoice,
        debitur_id: debitur.id,
        tanggal_terbit: nowStr,
        jatuh_tempo: dueStr,
        periode_bulan: periodeBulan,
        periode_tahun: periodeTahun,
        subtotal_dpp: subtotalDpp,
        total_ppn: totalPpn,
        grand_total: grandTotal,
        status_bayar: 'Belum Bayar',
        tanggal_lunas: null,
      },
      invoiceItemsData
    );

    setSaveSuccessMessage(`Invoice ${nomorInvoice} berhasil diterbitkan.`);
    setTimeout(() => {
      setSelectedInvoiceForPrint(newInv);
      setActiveTab('invoice');
    }, 800);
  };

  const handleSaveAsRvbwDraft = () => {
    if (!debitur) return;
    const nomorRvbw = generateRvbwNumber(
      Math.floor(Math.random() * 9000) + 1000,
      periodeTahun,
      periodeBulan,
      settings.prefix_rvbw
    );

    addRvbw({
      nomor_rvbw: nomorRvbw,
      tanggal: new Date().toISOString().split('T')[0],
      debitur_id: debitur.id,
      periode_bulan: periodeBulan,
      periode_tahun: periodeTahun,
      subtotal_dpp: subtotalDpp,
      total_ppn: totalPpn,
      grand_total: grandTotal,
      status: 'Draft',
    });

    setSaveSuccessMessage(`Draft RVBW ${nomorRvbw} berhasil disimpan.`);
    setTimeout(() => {
      setActiveTab('rvbw');
    }, 800);
  };

  return (
    <div className="space-y-6">
      {/* Top Title Banner */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-base font-bold text-white flex items-center gap-2">
            <Calculator className="w-5 h-5 text-cyan-400" />
            Generator Tagihan Bulanan (Utility & Estate)
          </h2>
          <p className="text-xs text-slate-400 mt-1">
            Hitung tagihan listrik PLN, air PDAM berjenjang (3 tier), dan iuran kawasan berdasarkan meteran unit.
          </p>
        </div>

        {saveSuccessMessage && (
          <div className="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold animate-fade-in">
            <CheckCircle2 className="w-4 h-4" />
            <span>{saveSuccessMessage}</span>
          </div>
        )}
      </div>

      {/* Unit Debitur & Periode Selection */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label className="block text-xs font-semibold text-slate-300 mb-1.5">
              Pilih Unit Kavling / Debitur
            </label>
            <select
              value={selectedDebiturId}
              onChange={(e) => handleDebiturChange(Number(e.target.value))}
              className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-medium focus:border-cyan-500"
            >
              {debiturs.map((d) => (
                <option key={d.id} value={d.id}>
                  {d.kode_kav} - {d.nama_owner} ({d.tipe_unit})
                </option>
              ))}
            </select>
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-300 mb-1.5">
              Periode Bulan
            </label>
            <select
              value={periodeBulan}
              onChange={(e) => setPeriodeBulan(Number(e.target.value))}
              className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-medium focus:border-cyan-500"
            >
              {[
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
              ].map((name, idx) => (
                <option key={idx + 1} value={idx + 1}>
                  Bulan {idx + 1} - {name}
                </option>
              ))}
            </select>
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-300 mb-1.5">
              Periode Tahun
            </label>
            <input
              type="number"
              value={periodeTahun}
              onChange={(e) => setPeriodeTahun(Number(e.target.value))}
              className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-mono focus:border-cyan-500 text-right"
            />
          </div>
        </div>

        {/* Debitur Specs Summary Bar */}
        {debitur && (
          <div className="mt-4 pt-3 border-t border-slate-800/80 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div>
              <span className="text-slate-500 block text-[10px]">Daya Terpasang:</span>
              <span className="font-mono font-bold text-amber-400">
                {formatNumber(debitur.watt_listrik)} VA
              </span>
            </div>
            <div>
              <span className="text-slate-500 block text-[10px]">Luas Kawasan:</span>
              <span className="font-mono font-bold text-slate-200">
                {formatNumber(debitur.luas_m2_kawasan)} m²
              </span>
            </div>
            <div>
              <span className="text-slate-500 block text-[10px]">No. WhatsApp:</span>
              <span className="font-mono text-slate-300">{debitur.no_hp}</span>
            </div>
            <div>
              <span className="text-slate-500 block text-[10px]">Alamat Unit:</span>
              <span className="text-slate-300 truncate block">
                {debitur.alamat_unit || debitur.tipe_unit}
              </span>
            </div>
          </div>
        )}
      </div>

      {/* 3 Calculation Panels: Listrik, Air, Kawasan */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* 1. Listrik PLN Panel */}
        <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-4">
          <div className="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 className="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-2">
              <Zap className="w-4 h-4 text-amber-400" />
              1. Listrik PLN
            </h3>
            <span className="text-[10px] font-mono text-slate-500">
              Tarif: Rp {formatNumber(settings.tarif_pln_per_kwh, 2)}/kWh
            </span>
          </div>

          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block text-[11px] font-medium text-slate-400 mb-1">
                Meter Awal (kWh)
              </label>
              <input
                type="number"
                step="0.1"
                value={plnAwal}
                onChange={(e) => setPlnAwal(Number(e.target.value))}
                className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2 font-mono text-right focus:border-amber-500"
              />
            </div>
            <div>
              <label className="block text-[11px] font-medium text-slate-400 mb-1">
                Meter Akhir (kWh)
              </label>
              <input
                type="number"
                step="0.1"
                value={plnAkhir}
                onChange={(e) => setPlnAkhir(Number(e.target.value))}
                className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2 font-mono text-right focus:border-amber-500"
              />
            </div>
          </div>

          {listrikCalc && (
            <div className="bg-[#1a1d23] rounded-lg p-3 space-y-2 text-xs border border-slate-800">
              <div className="flex justify-between">
                <span className="text-slate-400">Pemakaian Riil:</span>
                <span className="font-mono font-bold text-slate-200">
                  {formatNumber(listrikCalc.pemakaian_kwh, 2)} kWh
                </span>
              </div>
              <div className="flex justify-between items-center">
                <span className="text-slate-400 text-[11px] flex items-center gap-1">
                  Ambang RM (40 Jam Nyala):
                </span>
                <span className="font-mono text-slate-300">
                  {formatNumber(listrikCalc.ambang_rm_kwh, 1)} kWh
                </span>
              </div>
              {listrikCalc.rm_terpakai && (
                <div className="p-1.5 rounded bg-amber-500/10 border border-amber-500/20 text-[10px] text-amber-400 flex items-center gap-1.5">
                  <AlertTriangle className="w-3.5 h-3.5 shrink-0" />
                  <span>Dikenakan Rekening Minimum (Pemakaian &lt;= 40 jam daya).</span>
                </div>
              )}
              <div className="flex justify-between pt-1 border-t border-slate-800 text-slate-400">
                <span>Tagihan Dasar:</span>
                <span className="font-mono">{formatRupiah(listrikCalc.tagihan_dasar)}</span>
              </div>
              <div className="flex justify-between text-slate-400">
                <span>Biaya Loses ({settings.persen_loses}%):</span>
                <span className="font-mono">{formatRupiah(listrikCalc.biaya_loses)}</span>
              </div>
              <div className="flex justify-between text-slate-400">
                <span>Biaya PPJ ({settings.persen_ppj}%):</span>
                <span className="font-mono">{formatRupiah(listrikCalc.biaya_ppj)}</span>
              </div>
              <div className="flex justify-between text-slate-400">
                <span>Jasa Layanan ({settings.persen_jasa_listrik_air}%):</span>
                <span className="font-mono">{formatRupiah(listrikCalc.jasa)}</span>
              </div>
              <div className="flex justify-between pt-2 border-t border-slate-700 font-bold text-amber-400">
                <span>Total Listrik:</span>
                <span className="font-mono">{formatRupiah(listrikCalc.total)}</span>
              </div>
            </div>
          )}
        </div>

        {/* 2. Air PDAM Panel */}
        <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-4">
          <div className="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 className="text-xs font-bold text-cyan-400 uppercase tracking-wider flex items-center gap-2">
              <Droplet className="w-4 h-4 text-cyan-400" />
              2. Air PDAM (Tiering)
            </h3>
            <span className="text-[10px] font-mono text-slate-500">
              Abodemen: Rp {formatNumber(settings.tarif_air_abodemen)}
            </span>
          </div>

          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block text-[11px] font-medium text-slate-400 mb-1">
                Meter Awal (m³)
              </label>
              <input
                type="number"
                step="0.1"
                value={airAwal}
                onChange={(e) => setAirAwal(Number(e.target.value))}
                className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2 font-mono text-right focus:border-cyan-500"
              />
            </div>
            <div>
              <label className="block text-[11px] font-medium text-slate-400 mb-1">
                Meter Akhir (m³)
              </label>
              <input
                type="number"
                step="0.1"
                value={airAkhir}
                onChange={(e) => setAirAkhir(Number(e.target.value))}
                className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2 font-mono text-right focus:border-cyan-500"
              />
            </div>
          </div>

          {airCalc && (
            <div className="bg-[#1a1d23] rounded-lg p-3 space-y-2 text-xs border border-slate-800">
              <div className="flex justify-between">
                <span className="text-slate-400">Total Pemakaian:</span>
                <span className="font-mono font-bold text-slate-200">
                  {formatNumber(airCalc.pemakaian_m3, 2)} m³
                </span>
              </div>
              <div className="space-y-1 text-[11px] text-slate-400 border-t border-slate-800 pt-1.5">
                <div className="flex justify-between">
                  <span>Abodemen Bulanan:</span>
                  <span className="font-mono">{formatRupiah(airCalc.biaya_abodemen)}</span>
                </div>
                <div className="flex justify-between">
                  <span>Tier 1 (0-20 m³): {formatNumber(airCalc.vol_tier1)} m³</span>
                  <span className="font-mono">{formatRupiah(airCalc.biaya_tier1)}</span>
                </div>
                <div className="flex justify-between">
                  <span>Tier 2 (20-30 m³): {formatNumber(airCalc.vol_tier2)} m³</span>
                  <span className="font-mono">{formatRupiah(airCalc.biaya_tier2)}</span>
                </div>
                <div className="flex justify-between">
                  <span>Tier 3 (&gt;30 m³): {formatNumber(airCalc.vol_tier3)} m³</span>
                  <span className="font-mono">{formatRupiah(airCalc.biaya_tier3)}</span>
                </div>
                <div className="flex justify-between">
                  <span>Jasa Layanan ({settings.persen_jasa_listrik_air}%):</span>
                  <span className="font-mono">{formatRupiah(airCalc.jasa)}</span>
                </div>
              </div>
              <div className="flex justify-between pt-2 border-t border-slate-700 font-bold text-cyan-400">
                <span>Total Air PDAM:</span>
                <span className="font-mono">{formatRupiah(airCalc.total)}</span>
              </div>
            </div>
          )}
        </div>

        {/* 3. Kawasan & Flat Fees Panel */}
        <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-4">
          <div className="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 className="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-2">
              <Trees className="w-4 h-4 text-emerald-400" />
              3. Kawasan & Biaya Flat
            </h3>
            <span className="text-[10px] font-mono text-slate-500">
              Tarif: Rp {formatNumber(settings.tarif_kawasan_per_m2)}/m²
            </span>
          </div>

          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block text-[11px] font-medium text-slate-400 mb-1">
                Internet Flat (Rp)
              </label>
              <input
                type="number"
                value={internetFee}
                onChange={(e) => setInternetCustom(Number(e.target.value))}
                className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2 font-mono text-right focus:border-emerald-500"
              />
            </div>
            <div>
              <label className="block text-[11px] font-medium text-slate-400 mb-1">
                Landscape/Kolam (Rp)
              </label>
              <input
                type="number"
                value={landscapeFee + kolamFee}
                onChange={(e) => {
                  setLandscapeCustom(Number(e.target.value));
                  setKolamCustom(0);
                }}
                className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2 font-mono text-right focus:border-emerald-500"
              />
            </div>
          </div>

          {kawasanCalc && (
            <div className="bg-[#1a1d23] rounded-lg p-3 space-y-2 text-xs border border-slate-800">
              <div className="flex justify-between">
                <span className="text-slate-400">Luas Kavling:</span>
                <span className="font-mono font-bold text-slate-200">
                  {formatNumber(kawasanCalc.luas_m2)} m²
                </span>
              </div>
              <div className="space-y-1 text-[11px] text-slate-400 border-t border-slate-800 pt-1.5">
                <div className="flex justify-between">
                  <span>Tarif Dasar Kawasan:</span>
                  <span className="font-mono">{formatRupiah(kawasanCalc.total_sebelum_fee)}</span>
                </div>
                <div className="flex justify-between">
                  <span>Management Fee ({settings.persen_fee_kawasan}%):</span>
                  <span className="font-mono">{formatRupiah(kawasanCalc.fee)}</span>
                </div>
                <div className="flex justify-between">
                  <span>PPN Kawasan ({settings.ppn_default}%):</span>
                  <span className="font-mono">{formatRupiah(kawasanCalc.ppn)}</span>
                </div>
                <div className="flex justify-between">
                  <span>Total Kawasan (inc. PPN):</span>
                  <span className="font-mono font-semibold text-slate-300">
                    {formatRupiah(kawasanCalc.total)}
                  </span>
                </div>
                <div className="flex justify-between">
                  <span>Total Biaya Flat Tambahan:</span>
                  <span className="font-mono">{formatRupiah(internetFee + landscapeFee + kolamFee)}</span>
                </div>
              </div>
              <div className="flex justify-between pt-2 border-t border-slate-700 font-bold text-emerald-400">
                <span>Total Kawasan & Flat:</span>
                <span className="font-mono">
                  {formatRupiah(kawasanCalc.total + internetFee + landscapeFee + kolamFee)}
                </span>
              </div>
            </div>
          )}
        </div>
      </div>

      {/* Summary Grand Total & Action Buttons Bar */}
      <div className="bg-[#121418] border border-cyan-500/30 rounded-xl p-5 shadow-xl flex flex-col md:flex-row items-stretch md:items-center justify-between gap-6">
        <div className="space-y-1">
          <div className="text-[11px] uppercase tracking-wider text-slate-400 font-semibold flex items-center gap-1.5">
            <Info className="w-3.5 h-3.5 text-cyan-400" />
            Total Perhitungan Tagihan Unit {debitur?.kode_kav} (Periode {periodeBulan}/{periodeTahun})
          </div>
          <div className="flex items-baseline gap-3">
            <span className="text-3xl font-extrabold font-mono text-cyan-400">
              {formatRupiah(grandTotal)}
            </span>
            <span className="text-xs text-slate-400">
              (DPP: {formatRupiah(subtotalDpp)} + PPN: {formatRupiah(totalPpn)})
            </span>
          </div>
        </div>

        <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
          <button
            onClick={handleSaveAsRvbwDraft}
            className="flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-[#1a1d23] hover:bg-slate-800 text-slate-200 border border-slate-700 text-xs font-semibold transition-colors"
          >
            <Save className="w-4 h-4 text-slate-400" />
            <span>Simpan Draft RVBW</span>
          </button>

          <button
            onClick={handleCreateInvoiceDirect}
            className="flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold shadow-lg shadow-cyan-950/50 transition-colors"
          >
            <FileCheck className="w-4 h-4" />
            <span>Terbitkan Invoice Langsung</span>
            <ArrowRight className="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>
  );
};
