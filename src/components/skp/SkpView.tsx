import React, { useMemo, useState } from 'react';
import {
  Check,
  Home,
  Layers,
  Plus,
  Search,
  Wallet,
  X,
} from 'lucide-react';
import { useApp } from '../../context/AppContext';
import { formatDateIndo, formatNumber, formatRupiah } from '../../utils/format';

export const SkpView: React.FC = () => {
  const { skpList, debiturs, addSkp } = useApp();
  const [searchTerm, setSearchTerm] = useState('');
  const [showAddModal, setShowAddModal] = useState(false);

  // New SKP form
  const [formData, setFormData] = useState({
    nomor_skp: `SKP/2026/EM-${Math.floor(Math.random() * 900) + 100}/001`,
    tanggal_skp: new Date().toISOString().split('T')[0],
    debitur_id: debiturs.length > 0 ? debiturs[0].id : 1,
    tipe_unit_skp: 'Residensi Villa',
    nilai_jual: 1500000000,
    uang_muka: 300000000,
    tenor_bulan: 24,
    tanggal_mulai_cicilan: new Date().toISOString().split('T')[0],
    catatan: '',
  });

  const angsuranBulanan = useMemo(() => {
    const sisa = Math.max(0, formData.nilai_jual - formData.uang_muka);
    const tenor = Math.max(1, formData.tenor_bulan);
    return Math.round(sisa / tenor);
  }, [formData.nilai_jual, formData.uang_muka, formData.tenor_bulan]);

  const filteredSkp = useMemo(() => {
    return skpList.filter(
      (s) =>
        s.nomor_skp.toLowerCase().includes(searchTerm.toLowerCase()) ||
        s.kode_kav.toLowerCase().includes(searchTerm.toLowerCase()) ||
        s.nama_owner.toLowerCase().includes(searchTerm.toLowerCase())
    );
  }, [skpList, searchTerm]);

  const handleSaveSkp = (e: React.FormEvent) => {
    e.preventDefault();
    const deb = debiturs.find((d) => d.id === formData.debitur_id);
    if (!deb) return;

    addSkp({
      ...formData,
      kode_kav: deb.kode_kav,
      nama_owner: deb.nama_owner,
      nilai_angsuran_bulanan: angsuranBulanan,
      status: 'Berjalan',
    });

    setShowAddModal(false);
  };

  return (
    <div className="space-y-6">
      {/* Banner */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-base font-bold text-white flex items-center gap-2">
            <Home className="w-5 h-5 text-cyan-400" />
            SKP (Surat Kesepakatan Penjualan Unit Villa)
          </h2>
          <p className="text-xs text-slate-400 mt-1">
            Pengelolaan kontrak pembelian properti, skema uang muka, dan jadwal angsuran cicilan unit villa.
          </p>
        </div>

        <button
          onClick={() => setShowAddModal(true)}
          className="flex items-center gap-2 px-4 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold shadow-md transition-colors shrink-0"
        >
          <Plus className="w-4 h-4" />
          <span>Buat Kontrak SKP</span>
        </button>
      </div>

      {/* Search & Filter */}
      <div className="flex items-center justify-between gap-3">
        <div className="relative flex-1 max-w-sm">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            placeholder="Cari no. SKP, kavling, atau nama..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full bg-[#121418] border border-slate-800 text-slate-200 text-xs rounded-lg pl-9 pr-4 py-2.5 focus:border-cyan-500"
          />
        </div>

        <div className="text-xs text-slate-400">
          Total: <strong className="text-slate-200">{filteredSkp.length}</strong> Kontrak
        </div>
      </div>

      {/* SKP Table */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl overflow-hidden shadow-sm">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-[#1a1d23] text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-800">
              <tr>
                <th className="py-3 px-4">No. SKP</th>
                <th className="py-3 px-4">Tanggal Kontrak</th>
                <th className="py-3 px-4">Kavling & Pembeli</th>
                <th className="py-3 px-4 text-right">Nilai Jual</th>
                <th className="py-3 px-4 text-right">Uang Muka (DP)</th>
                <th className="py-3 px-4 text-center">Tenor</th>
                <th className="py-3 px-4 text-right">Angsuran / Bln</th>
                <th className="py-3 px-4 text-center">Status</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {filteredSkp.length === 0 ? (
                <tr>
                  <td colSpan={8} className="text-center py-10 text-slate-500">
                    Tidak ditemukan data SKP unit properti.
                  </td>
                </tr>
              ) : (
                filteredSkp.map((skp) => (
                  <tr key={skp.id} className="hover:bg-slate-800/30 transition-colors">
                    <td className="py-3 px-4 font-mono font-bold text-cyan-400">
                      {skp.nomor_skp}
                    </td>
                    <td className="py-3 px-4 text-slate-300">
                      {formatDateIndo(skp.tanggal_skp)}
                    </td>
                    <td className="py-3 px-4">
                      <div className="font-bold text-slate-200">{skp.kode_kav}</div>
                      <div className="text-[11px] text-slate-400">{skp.nama_owner}</div>
                    </td>
                    <td className="py-3 px-4 text-right font-mono font-semibold text-slate-200">
                      {formatRupiah(skp.nilai_jual)}
                    </td>
                    <td className="py-3 px-4 text-right font-mono text-emerald-400">
                      {formatRupiah(skp.uang_muka)}
                    </td>
                    <td className="py-3 px-4 text-center font-mono text-slate-300">
                      {skp.tenor_bulan} Bulan
                    </td>
                    <td className="py-3 px-4 text-right font-mono font-bold text-amber-400">
                      {formatRupiah(skp.nilai_angsuran_bulanan)}
                    </td>
                    <td className="py-3 px-4 text-center">
                      <span className="px-2.5 py-0.5 rounded text-[10px] font-semibold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 uppercase">
                        {skp.status}
                      </span>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal Tambah SKP */}
      {showAddModal && (
        <div className="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4">
          <div className="bg-[#121418] border border-slate-700 rounded-xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between border-b border-slate-800 pb-3">
              <h3 className="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                <Home className="w-4 h-4 text-cyan-400" />
                Buat Kontrak SKP Penjualan Unit Baru
              </h3>
              <button
                onClick={() => setShowAddModal(false)}
                className="text-slate-400 hover:text-white p-1"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleSaveSkp} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  Nomor Kontrak SKP *
                </label>
                <input
                  type="text"
                  required
                  value={formData.nomor_skp}
                  onChange={(e) => setFormData({ ...formData, nomor_skp: e.target.value })}
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-mono focus:border-cyan-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    Debitur / Pemilik Unit *
                  </label>
                  <select
                    value={formData.debitur_id}
                    onChange={(e) =>
                      setFormData({ ...formData, debitur_id: Number(e.target.value) })
                    }
                    className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 focus:border-cyan-500"
                  >
                    {debiturs.map((d) => (
                      <option key={d.id} value={d.id}>
                        {d.kode_kav} - {d.nama_owner}
                      </option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    Tanggal Kontrak
                  </label>
                  <input
                    type="date"
                    value={formData.tanggal_skp}
                    onChange={(e) => setFormData({ ...formData, tanggal_skp: e.target.value })}
                    className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 focus:border-cyan-500"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    Nilai Jual Unit (Rp) *
                  </label>
                  <input
                    type="number"
                    step="1000000"
                    value={formData.nilai_jual}
                    onChange={(e) =>
                      setFormData({ ...formData, nilai_jual: Number(e.target.value) })
                    }
                    className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-mono text-right focus:border-cyan-500"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    Uang Muka / DP (Rp) *
                  </label>
                  <input
                    type="number"
                    step="1000000"
                    value={formData.uang_muka}
                    onChange={(e) =>
                      setFormData({ ...formData, uang_muka: Number(e.target.value) })
                    }
                    className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-mono text-right focus:border-cyan-500"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    Tenor Cicilan (Bulan)
                  </label>
                  <input
                    type="number"
                    min="1"
                    max="120"
                    value={formData.tenor_bulan}
                    onChange={(e) =>
                      setFormData({ ...formData, tenor_bulan: Number(e.target.value) })
                    }
                    className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-mono text-right focus:border-cyan-500"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    Perkiraan Angsuran / Bln
                  </label>
                  <div className="w-full bg-[#14171d] border border-slate-800 text-amber-400 font-mono font-bold text-xs rounded-lg p-2.5 text-right">
                    {formatRupiah(angsuranBulanan)}
                  </div>
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">Catatan</label>
                <textarea
                  rows={2}
                  value={formData.catatan}
                  onChange={(e) => setFormData({ ...formData, catatan: e.target.value })}
                  placeholder="Keterangan nomor akta, skema pembayaran, dll."
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 focus:border-cyan-500"
                ></textarea>
              </div>

              <div className="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button
                  type="button"
                  onClick={() => setShowAddModal(false)}
                  className="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  className="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold"
                >
                  <Check className="w-4 h-4" />
                  <span>Simpan Kontrak</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
