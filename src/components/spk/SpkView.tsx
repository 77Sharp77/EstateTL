import React, { useMemo, useState } from 'react';
import {
  Check,
  CreditCard,
  Hammer,
  History,
  Plus,
  Search,
  X,
} from 'lucide-react';
import { useApp } from '../../context/AppContext';
import { SpkRecord } from '../../types';
import { formatDateIndo, formatRupiah } from '../../utils/format';

export const SpkView: React.FC = () => {
  const { spkList, spkPayments, addSpk, addSpkPayment } = useApp();
  const [searchTerm, setSearchTerm] = useState('');
  const [showAddSpkModal, setShowAddSpkModal] = useState(false);
  const [selectedSpkForPay, setSelectedSpkForPay] = useState<SpkRecord | null>(null);

  // New SPK form
  const [spkForm, setSpkForm] = useState({
    nomor_spk: `SPK/2026/00${spkList.length + 1}/MAINT`,
    jenis_pekerjaan: 'Kontraktor Sipil & Bangunan',
    nama_kontraktor: '',
    nominal_kontrak: 50000000,
    pph_persen: 2.0,
    pic_pekerjaan: '',
    keterangan: '',
    tanggal_spk: new Date().toISOString().split('T')[0],
  });

  // New Payment form
  const [payForm, setPayForm] = useState({
    tahapan: 'Termin 1 (50%)',
    nilai_pembayaran: 25000000,
    nomor_voucher: `PVBW260${spkPayments.length + 1}0001`,
    tanggal_pembayaran: new Date().toISOString().split('T')[0],
    catatan: '',
  });

  const filteredSpk = useMemo(() => {
    return spkList.filter(
      (s) =>
        s.nomor_spk.toLowerCase().includes(searchTerm.toLowerCase()) ||
        s.nama_kontraktor.toLowerCase().includes(searchTerm.toLowerCase()) ||
        s.jenis_pekerjaan.toLowerCase().includes(searchTerm.toLowerCase())
    );
  }, [spkList, searchTerm]);

  const handleSaveSpk = (e: React.FormEvent) => {
    e.preventDefault();
    addSpk({
      ...spkForm,
      status_manual: 'Belum Selesai',
    });
    setShowAddSpkModal(false);
  };

  const handleSavePayment = (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedSpkForPay) return;

    addSpkPayment({
      spk_id: selectedSpkForPay.id,
      ...payForm,
    });
    setSelectedSpkForPay(null);
  };

  return (
    <div className="space-y-6">
      {/* Banner */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-base font-bold text-white flex items-center gap-2">
            <Hammer className="w-5 h-5 text-cyan-400" />
            SPK (Surat Perintah Kerja Kontraktor)
          </h2>
          <p className="text-xs text-slate-400 mt-1">
            Monitoring kontrak kerja pemeliharaan resort, pemotongan PPh 2%, dan histori pencairan voucher pembayaran termin.
          </p>
        </div>

        <button
          onClick={() => setShowAddSpkModal(true)}
          className="flex items-center gap-2 px-4 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold shadow-md transition-colors shrink-0"
        >
          <Plus className="w-4 h-4" />
          <span>Buat SPK Baru</span>
        </button>
      </div>

      {/* Filter Bar */}
      <div className="flex items-center justify-between gap-3">
        <div className="relative flex-1 max-w-sm">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            placeholder="Cari kontraktor atau nomor SPK..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full bg-[#121418] border border-slate-800 text-slate-200 text-xs rounded-lg pl-9 pr-4 py-2.5 focus:border-cyan-500"
          />
        </div>
      </div>

      {/* SPK List Cards */}
      <div className="space-y-4">
        {filteredSpk.map((spk) => {
          const payments = spkPayments.filter((p) => p.spk_id === spk.id);
          const totalPaid = payments.reduce((sum, p) => sum + Number(p.nilai_pembayaran), 0);
          const pphNominal = spk.nominal_kontrak * (spk.pph_persen / 100);
          const sisaKontrak = Math.max(0, spk.nominal_kontrak - totalPaid);

          return (
            <div
              key={spk.id}
              className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-4"
            >
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800/80 pb-3">
                <div>
                  <div className="flex items-center gap-2">
                    <span className="font-mono font-bold text-cyan-400 text-sm">
                      {spk.nomor_spk}
                    </span>
                    <span className="text-xs text-slate-400 font-medium">
                      ({formatDateIndo(spk.tanggal_spk)})
                    </span>
                  </div>
                  <h3 className="text-sm font-semibold text-slate-200 mt-0.5">
                    {spk.nama_kontraktor}
                  </h3>
                  <p className="text-xs text-slate-400">{spk.keterangan}</p>
                </div>

                <div className="flex items-center gap-2">
                  <button
                    onClick={() => {
                      setSelectedSpkForPay(spk);
                      setPayForm((prev) => ({
                        ...prev,
                        nilai_pembayaran: Math.min(sisaKontrak, spk.nominal_kontrak * 0.3),
                      }));
                    }}
                    className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-cyan-600/20 hover:bg-cyan-600/30 text-cyan-400 border border-cyan-500/30 text-xs font-semibold transition-colors"
                  >
                    <Plus className="w-3.5 h-3.5" />
                    <span>Input Pembayaran</span>
                  </button>
                </div>
              </div>

              {/* Financial Progress Grid */}
              <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                  <span className="text-slate-500 text-[10px] uppercase block">
                    Nilai Kontrak:
                  </span>
                  <span className="font-mono font-bold text-slate-200 text-sm">
                    {formatRupiah(spk.nominal_kontrak)}
                  </span>
                </div>
                <div>
                  <span className="text-slate-500 text-[10px] uppercase block">
                    Potongan PPh ({spk.pph_persen}%):
                  </span>
                  <span className="font-mono text-amber-400">
                    {formatRupiah(pphNominal)}
                  </span>
                </div>
                <div>
                  <span className="text-slate-500 text-[10px] uppercase block">
                    Total Dicairkan:
                  </span>
                  <span className="font-mono text-emerald-400 font-bold">
                    {formatRupiah(totalPaid)}
                  </span>
                </div>
                <div>
                  <span className="text-slate-500 text-[10px] uppercase block">
                    Sisa Kontrak:
                  </span>
                  <span className="font-mono text-rose-400 font-bold">
                    {formatRupiah(sisaKontrak)}
                  </span>
                </div>
              </div>

              {/* Payments History Table */}
              {payments.length > 0 && (
                <div className="bg-[#1a1d23] rounded-lg border border-slate-800 p-3">
                  <div className="text-[11px] font-semibold text-slate-300 mb-2 flex items-center gap-1.5">
                    <History className="w-3.5 h-3.5 text-cyan-400" />
                    Histori Pencairan Termin Pembayaran
                  </div>
                  <div className="space-y-1.5">
                    {payments.map((p) => (
                      <div
                        key={p.id}
                        className="flex items-center justify-between text-xs py-1 px-2 rounded bg-slate-900/60"
                      >
                        <div className="flex items-center gap-2">
                          <span className="font-mono text-cyan-400 text-[11px]">
                            {p.nomor_voucher}
                          </span>
                          <span className="text-slate-300 font-medium">{p.tahapan}</span>
                          <span className="text-[10px] text-slate-500">
                            • {formatDateIndo(p.tanggal_pembayaran)}
                          </span>
                        </div>
                        <span className="font-mono font-bold text-emerald-400">
                          {formatRupiah(p.nilai_pembayaran)}
                        </span>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>
          );
        })}
      </div>

      {/* Modal Tambah SPK */}
      {showAddSpkModal && (
        <div className="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4">
          <div className="bg-[#121418] border border-slate-700 rounded-xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between border-b border-slate-800 pb-3">
              <h3 className="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                <Hammer className="w-4 h-4 text-cyan-400" />
                Buat SPK Kontraktor Baru
              </h3>
              <button
                onClick={() => setShowAddSpkModal(false)}
                className="text-slate-400 hover:text-white p-1"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleSaveSpk} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  Nomor Dokumen SPK *
                </label>
                <input
                  type="text"
                  required
                  value={spkForm.nomor_spk}
                  onChange={(e) => setSpkForm({ ...spkForm, nomor_spk: e.target.value })}
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-mono focus:border-cyan-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    Nama Vendor / Kontraktor *
                  </label>
                  <input
                    type="text"
                    required
                    placeholder="PT / CV Kontraktor"
                    value={spkForm.nama_kontraktor}
                    onChange={(e) =>
                      setSpkForm({ ...spkForm, nama_kontraktor: e.target.value })
                    }
                    className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 focus:border-cyan-500"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    PIC Pekerjaan
                  </label>
                  <input
                    type="text"
                    placeholder="Nama Penanggung Jawab"
                    value={spkForm.pic_pekerjaan}
                    onChange={(e) => setSpkForm({ ...spkForm, pic_pekerjaan: e.target.value })}
                    className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 focus:border-cyan-500"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    Nominal Kontrak (Rp) *
                  </label>
                  <input
                    type="number"
                    step="1000000"
                    value={spkForm.nominal_kontrak}
                    onChange={(e) =>
                      setSpkForm({ ...spkForm, nominal_kontrak: Number(e.target.value) })
                    }
                    className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-mono text-right focus:border-cyan-500"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    Pajak PPh (%)
                  </label>
                  <input
                    type="number"
                    step="0.5"
                    value={spkForm.pph_persen}
                    onChange={(e) =>
                      setSpkForm({ ...spkForm, pph_persen: Number(e.target.value) })
                    }
                    className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-mono text-right focus:border-cyan-500"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  Uraian Lingkup Pekerjaan
                </label>
                <textarea
                  rows={2}
                  value={spkForm.keterangan}
                  onChange={(e) => setSpkForm({ ...spkForm, keterangan: e.target.value })}
                  placeholder="Detail perbaikan atau renovasi..."
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 focus:border-cyan-500"
                ></textarea>
              </div>

              <div className="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button
                  type="button"
                  onClick={() => setShowAddSpkModal(false)}
                  className="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  className="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold"
                >
                  <Check className="w-4 h-4" />
                  <span>Simpan SPK</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal Input Pembayaran Termin */}
      {selectedSpkForPay && (
        <div className="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4">
          <div className="bg-[#121418] border border-slate-700 rounded-xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div className="flex items-center justify-between border-b border-slate-800 pb-3">
              <h3 className="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                <CreditCard className="w-4 h-4 text-cyan-400" />
                Input Pembayaran Termin SPK
              </h3>
              <button
                onClick={() => setSelectedSpkForPay(null)}
                className="text-slate-400 hover:text-white p-1"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="text-xs text-slate-400">
              Kontrak: <strong className="text-slate-200">{selectedSpkForPay.nomor_spk}</strong> (
              {selectedSpkForPay.nama_kontraktor})
            </div>

            <form onSubmit={handleSavePayment} className="space-y-3">
              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  Keterangan Tahapan
                </label>
                <input
                  type="text"
                  required
                  placeholder="Contoh: DP 30%, Termin 2, Pelunasan"
                  value={payForm.tahapan}
                  onChange={(e) => setPayForm({ ...payForm, tahapan: e.target.value })}
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 focus:border-cyan-500"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  Nomor Voucher Kas / Bank (PVBW)
                </label>
                <input
                  type="text"
                  required
                  value={payForm.nomor_voucher}
                  onChange={(e) => setPayForm({ ...payForm, nomor_voucher: e.target.value })}
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-mono focus:border-cyan-500"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  Nominal Pencairan (Rp)
                </label>
                <input
                  type="number"
                  step="100000"
                  value={payForm.nilai_pembayaran}
                  onChange={(e) =>
                    setPayForm({ ...payForm, nilai_pembayaran: Number(e.target.value) })
                  }
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-mono text-right focus:border-cyan-500"
                />
              </div>

              <div className="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button
                  type="button"
                  onClick={() => setSelectedSpkForPay(null)}
                  className="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  className="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold"
                >
                  <Check className="w-4 h-4" />
                  <span>Simpan Voucher</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
