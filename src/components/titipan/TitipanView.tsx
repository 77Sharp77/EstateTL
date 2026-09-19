import React, { useMemo, useState } from 'react';
import {
  ArrowDownLeft,
  ArrowUpRight,
  Check,
  Plus,
  Search,
  Wallet,
  X,
} from 'lucide-react';
import { useApp } from '../../context/AppContext';
import { TitipanRvbw } from '../../types';
import { formatDateIndo, formatRupiah } from '../../utils/format';

export const TitipanView: React.FC = () => {
  const { titipanList, titipanPvbwList, addTitipan, addTitipanPvbw } = useApp();
  const [searchTerm, setSearchTerm] = useState('');
  const [showAddRvModal, setShowAddRvModal] = useState(false);
  const [selectedTitipanForPv, setSelectedTitipanForPv] = useState<TitipanRvbw | null>(null);

  // New Titipan RVBW Form
  const [rvForm, setRvForm] = useState({
    nomor_rvbw: `RVBW260${titipanList.length + 1}0001`,
    entitas: 'BWJ' as 'BWJ' | 'TLLI',
    tanggal_terima: new Date().toISOString().split('T')[0],
    nominal: 10000000,
    keterangan: '',
  });

  // New PVBW Return Form
  const [pvForm, setPvForm] = useState({
    nomor_pvbw: `PVBW260${titipanPvbwList.length + 1}0001`,
    tanggal_kembali: new Date().toISOString().split('T')[0],
    nominal: 5000000,
    keterangan: '',
  });

  const filteredList = useMemo(() => {
    return titipanList.filter(
      (t) =>
        t.nomor_rvbw.toLowerCase().includes(searchTerm.toLowerCase()) ||
        t.keterangan.toLowerCase().includes(searchTerm.toLowerCase())
    );
  }, [titipanList, searchTerm]);

  const totalTitipanMasuk = useMemo(() => {
    return titipanList.reduce((sum, t) => sum + Number(t.nominal), 0);
  }, [titipanList]);

  const totalDikembalikan = useMemo(() => {
    return titipanPvbwList.reduce((sum, p) => sum + Number(p.nominal), 0);
  }, [titipanPvbwList]);

  const sisaSaldoTitipan = totalTitipanMasuk - totalDikembalikan;

  const handleSaveTitipan = (e: React.FormEvent) => {
    e.preventDefault();
    addTitipan({
      ...rvForm,
      status: 'Aktif',
    });
    setShowAddRvModal(false);
  };

  const handleSavePvbw = (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedTitipanForPv) return;

    addTitipanPvbw({
      titipan_rvbw_id: selectedTitipanForPv.id,
      ...pvForm,
    });
    setSelectedTitipanForPv(null);
  };

  return (
    <div className="space-y-6">
      {/* Banner */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-base font-bold text-white flex items-center gap-2">
            <Wallet className="w-5 h-5 text-cyan-400" />
            Titipan Dana Jaminan (RVBW & PVBW)
          </h2>
          <p className="text-xs text-slate-400 mt-1">
            Pencatatan dana titipan/escrow (Security Deposit renovasi, jaminan kebersihan) entitas BWJ dan TLLI.
          </p>
        </div>

        <button
          onClick={() => setShowAddRvModal(true)}
          className="flex items-center gap-2 px-4 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold shadow-md transition-colors shrink-0"
        >
          <Plus className="w-4 h-4" />
          <span>Terima Titipan Baru</span>
        </button>
      </div>

      {/* Summary KPI Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-4">
          <div className="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-1 flex items-center gap-1.5">
            <ArrowDownLeft className="w-4 h-4 text-emerald-400" />
            Total Titipan Diterima
          </div>
          <div className="text-xl font-bold font-mono text-emerald-400">
            {formatRupiah(totalTitipanMasuk)}
          </div>
        </div>

        <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-4">
          <div className="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-1 flex items-center gap-1.5">
            <ArrowUpRight className="w-4 h-4 text-amber-400" />
            Total Dikembalikan
          </div>
          <div className="text-xl font-bold font-mono text-amber-400">
            {formatRupiah(totalDikembalikan)}
          </div>
        </div>

        <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-4">
          <div className="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-1 flex items-center gap-1.5">
            <Wallet className="w-4 h-4 text-cyan-400" />
            Sisa Saldo Titipan Mengendap
          </div>
          <div className="text-xl font-bold font-mono text-cyan-400">
            {formatRupiah(sisaSaldoTitipan)}
          </div>
        </div>
      </div>

      {/* List Container */}
      <div className="space-y-4">
        {filteredList.map((t) => {
          const returns = titipanPvbwList.filter((p) => p.titipan_rvbw_id === t.id);
          const totalRet = returns.reduce((sum, p) => sum + Number(p.nominal), 0);
          const sisa = Math.max(0, t.nominal - totalRet);

          return (
            <div
              key={t.id}
              className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-4"
            >
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800/80 pb-3">
                <div>
                  <div className="flex items-center gap-2">
                    <span className="font-mono font-bold text-cyan-400 text-sm">
                      {t.nomor_rvbw}
                    </span>
                    <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                      Entitas {t.entitas}
                    </span>
                    <span className="text-xs text-slate-500 font-medium">
                      ({formatDateIndo(t.tanggal_terima)})
                    </span>
                  </div>
                  <p className="text-xs text-slate-300 mt-1 font-medium">{t.keterangan}</p>
                </div>

                <div className="flex items-center gap-2">
                  {sisa > 0 && (
                    <button
                      onClick={() => {
                        setSelectedTitipanForPv(t);
                        setPvForm((prev) => ({ ...prev, nominal: sisa }));
                      }}
                      className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-semibold transition-colors"
                    >
                      <ArrowUpRight className="w-3.5 h-3.5" />
                      <span>Input Pengembalian (PVBW)</span>
                    </button>
                  )}
                </div>
              </div>

              <div className="grid grid-cols-3 gap-4 text-xs">
                <div>
                  <span className="text-slate-500 text-[10px] uppercase block">
                    Nominal Titipan Awal:
                  </span>
                  <span className="font-mono font-bold text-slate-200">
                    {formatRupiah(t.nominal)}
                  </span>
                </div>
                <div>
                  <span className="text-slate-500 text-[10px] uppercase block">
                    Sudah Dikembalikan:
                  </span>
                  <span className="font-mono text-amber-400 font-bold">
                    {formatRupiah(totalRet)}
                  </span>
                </div>
                <div>
                  <span className="text-slate-500 text-[10px] uppercase block">
                    Sisa Saldo Jaminan:
                  </span>
                  <span
                    className={`font-mono font-bold ${
                      sisa > 0 ? 'text-cyan-400' : 'text-slate-500'
                    }`}
                  >
                    {formatRupiah(sisa)} {sisa === 0 && '(Nol / Selesai)'}
                  </span>
                </div>
              </div>

              {/* Histori PVBW Returns */}
              {returns.length > 0 && (
                <div className="bg-[#1a1d23] rounded-lg border border-slate-800 p-3 space-y-1.5">
                  <div className="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                    Histori Voucher Pengembalian Dana (PVBW):
                  </div>
                  {returns.map((r) => (
                    <div
                      key={r.id}
                      className="flex items-center justify-between text-xs py-1 px-2.5 rounded bg-slate-900/60"
                    >
                      <div className="flex items-center gap-2">
                        <span className="font-mono text-amber-400">{r.nomor_pvbw}</span>
                        <span className="text-slate-300">{r.keterangan}</span>
                        <span className="text-[10px] text-slate-500">
                          ({formatDateIndo(r.tanggal_kembali)})
                        </span>
                      </div>
                      <span className="font-mono font-bold text-amber-400">
                        {formatRupiah(r.nominal)}
                      </span>
                    </div>
                  ))}
                </div>
              )}
            </div>
          );
        })}
      </div>

      {/* Modal Terima Titipan Baru */}
      {showAddRvModal && (
        <div className="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4">
          <div className="bg-[#121418] border border-slate-700 rounded-xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div className="flex items-center justify-between border-b border-slate-800 pb-3">
              <h3 className="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                <Wallet className="w-4 h-4 text-cyan-400" />
                Terima Titipan Dana (RVBW)
              </h3>
              <button
                onClick={() => setShowAddRvModal(false)}
                className="text-slate-400 hover:text-white p-1"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleSaveTitipan} className="space-y-3">
              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  Nomor Voucher RVBW *
                </label>
                <input
                  type="text"
                  required
                  value={rvForm.nomor_rvbw}
                  onChange={(e) => setRvForm({ ...rvForm, nomor_rvbw: e.target.value })}
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-mono focus:border-cyan-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    Entitas
                  </label>
                  <select
                    value={rvForm.entitas}
                    onChange={(e: any) => setRvForm({ ...rvForm, entitas: e.target.value })}
                    className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 focus:border-cyan-500"
                  >
                    <option value="BWJ">BWJ</option>
                    <option value="TLLI">TLLI</option>
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    Tanggal Terima
                  </label>
                  <input
                    type="date"
                    value={rvForm.tanggal_terima}
                    onChange={(e) => setRvForm({ ...rvForm, tanggal_terima: e.target.value })}
                    className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 focus:border-cyan-500"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  Nominal Titipan (Rp)
                </label>
                <input
                  type="number"
                  step="500000"
                  value={rvForm.nominal}
                  onChange={(e) => setRvForm({ ...rvForm, nominal: Number(e.target.value) })}
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-mono text-right focus:border-cyan-500"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  Keterangan Jaminan
                </label>
                <textarea
                  rows={2}
                  required
                  placeholder="Contoh: Titipan security deposit renovasi villa kav EM-1020"
                  value={rvForm.keterangan}
                  onChange={(e) => setRvForm({ ...rvForm, keterangan: e.target.value })}
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 focus:border-cyan-500"
                ></textarea>
              </div>

              <div className="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button
                  type="button"
                  onClick={() => setShowAddRvModal(false)}
                  className="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  className="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold"
                >
                  <Check className="w-4 h-4" />
                  <span>Simpan Titipan</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal Pengembalian Dana PVBW */}
      {selectedTitipanForPv && (
        <div className="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4">
          <div className="bg-[#121418] border border-slate-700 rounded-xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div className="flex items-center justify-between border-b border-slate-800 pb-3">
              <h3 className="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                <ArrowUpRight className="w-4 h-4 text-amber-400" />
                Voucher Pengembalian Dana (PVBW)
              </h3>
              <button
                onClick={() => setSelectedTitipanForPv(null)}
                className="text-slate-400 hover:text-white p-1"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleSavePvbw} className="space-y-3">
              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  Nomor Voucher PVBW *
                </label>
                <input
                  type="text"
                  required
                  value={pvForm.nomor_pvbw}
                  onChange={(e) => setPvForm({ ...pvForm, nomor_pvbw: e.target.value })}
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-mono focus:border-cyan-500"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  Nominal Dikembalikan (Rp)
                </label>
                <input
                  type="number"
                  step="100000"
                  value={pvForm.nominal}
                  onChange={(e) => setPvForm({ ...pvForm, nominal: Number(e.target.value) })}
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-mono text-right focus:border-cyan-500"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  Keterangan Pengembalian
                </label>
                <textarea
                  rows={2}
                  required
                  placeholder="Contoh: Pengembalian sisa jaminan renovasi setelah inspeksi kebersihan"
                  value={pvForm.keterangan}
                  onChange={(e) => setPvForm({ ...pvForm, keterangan: e.target.value })}
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 focus:border-cyan-500"
                ></textarea>
              </div>

              <div className="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button
                  type="button"
                  onClick={() => setSelectedTitipanForPv(null)}
                  className="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  className="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold"
                >
                  <Check className="w-4 h-4" />
                  <span>Simpan Pengembalian</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
