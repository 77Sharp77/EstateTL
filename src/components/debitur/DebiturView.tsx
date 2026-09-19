import React, { useMemo, useState } from 'react';
import {
  Building,
  Check,
  Filter,
  Plus,
  Search,
  Users,
  X,
  Zap,
} from 'lucide-react';
import { useApp } from '../../context/AppContext';
import { formatNumber, formatRupiah } from '../../utils/format';

export const DebiturView: React.FC = () => {
  const { debiturs, addDebitur, setActiveTab } = useApp();
  const [searchTerm, setSearchTerm] = useState('');
  const [showAddModal, setShowAddModal] = useState(false);

  // New debitur form state
  const [formData, setFormData] = useState({
    kode_kav: '',
    nama_owner: '',
    no_hp: '',
    email: '',
    tipe_unit: 'Villa Type A',
    watt_listrik: 6600,
    luas_m2_kawasan: 350,
    landscape_flat: 0,
    kolam_flat: 0,
    internet_flat: 450000,
    alamat_unit: '',
  });

  const filteredDebiturs = useMemo(() => {
    return debiturs.filter(
      (d) =>
        d.nama_owner.toLowerCase().includes(searchTerm.toLowerCase()) ||
        d.kode_kav.toLowerCase().includes(searchTerm.toLowerCase()) ||
        (d.email && d.email.toLowerCase().includes(searchTerm.toLowerCase()))
    );
  }, [debiturs, searchTerm]);

  const handleSubmitNew = (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.kode_kav || !formData.nama_owner || !formData.no_hp) {
      alert('Harap lengkapi Kode Kavling, Nama Pemilik, dan No HP');
      return;
    }

    addDebitur({
      ...formData,
      status: 'aktif',
    });

    setShowAddModal(false);
    setFormData({
      kode_kav: '',
      nama_owner: '',
      no_hp: '',
      email: '',
      tipe_unit: 'Villa Type A',
      watt_listrik: 6600,
      luas_m2_kawasan: 350,
      landscape_flat: 0,
      kolam_flat: 0,
      internet_flat: 450000,
      alamat_unit: '',
    });
  };

  return (
    <div className="space-y-6">
      {/* Top Header Card */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-base font-bold text-white flex items-center gap-2">
            <Users className="w-5 h-5 text-cyan-400" />
            Master Data Debitur & Pemilik Villa
          </h2>
          <p className="text-xs text-slate-400 mt-1">
            Daftar kepemilikan unit kavling villa Kalicaa, spesifikasi kapasitas daya listrik PLN, dan luas kawasan.
          </p>
        </div>

        <button
          onClick={() => setShowAddModal(true)}
          className="flex items-center gap-2 px-4 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold shadow-lg shadow-cyan-950/40 transition-colors shrink-0"
        >
          <Plus className="w-4 h-4" />
          <span>Tambah Debitur Baru</span>
        </button>
      </div>

      {/* Filter & Search Bar */}
      <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div className="relative flex-1 max-w-md">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            placeholder="Cari nama debitur, kode kavling (contoh: EM-1010)..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full bg-[#121418] border border-slate-800 text-slate-200 text-xs rounded-lg pl-9 pr-4 py-2.5 focus:outline-none focus:border-cyan-500 transition-colors"
          />
        </div>

        <div className="text-xs text-slate-400 flex items-center gap-2">
          <span>Menampilkan <strong className="text-slate-200">{filteredDebiturs.length}</strong> dari {debiturs.length} unit</span>
        </div>
      </div>

      {/* Debitur Table */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl overflow-hidden shadow-sm">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-[#1a1d23] text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-800">
              <tr>
                <th className="py-3 px-4">Kavling</th>
                <th className="py-3 px-4">Nama Pemilik</th>
                <th className="py-3 px-4">Kontak / HP</th>
                <th className="py-3 px-4">Tipe Unit</th>
                <th className="py-3 px-4 text-right">Daya Listrik</th>
                <th className="py-3 px-4 text-right">Luas Kawasan</th>
                <th className="py-3 px-4 text-right">Biaya Flat (Bln)</th>
                <th className="py-3 px-4 text-center">Status</th>
                <th className="py-3 px-4 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {filteredDebiturs.length === 0 ? (
                <tr>
                  <td colSpan={9} className="text-center py-10 text-slate-500">
                    Tidak ditemukan debitur sesuai kata kunci pencarian.
                  </td>
                </tr>
              ) : (
                filteredDebiturs.map((deb) => (
                  <tr key={deb.id} className="hover:bg-slate-800/30 transition-colors">
                    <td className="py-3 px-4 font-bold font-mono text-cyan-400">
                      {deb.kode_kav}
                    </td>
                    <td className="py-3 px-4 font-semibold text-slate-200">
                      {deb.nama_owner}
                      {deb.email && (
                        <div className="text-[10px] text-slate-500 font-normal">{deb.email}</div>
                      )}
                    </td>
                    <td className="py-3 px-4 font-mono text-slate-400">{deb.no_hp}</td>
                    <td className="py-3 px-4 text-slate-300">{deb.tipe_unit}</td>
                    <td className="py-3 px-4 text-right font-mono text-amber-400">
                      {formatNumber(deb.watt_listrik)} VA
                    </td>
                    <td className="py-3 px-4 text-right font-mono text-slate-300">
                      {formatNumber(deb.luas_m2_kawasan)} m²
                    </td>
                    <td className="py-3 px-4 text-right font-mono text-slate-400">
                      <div className="text-[11px] text-slate-300">
                        {formatRupiah(deb.internet_flat + deb.landscape_flat + deb.kolam_flat)}
                      </div>
                      <div className="text-[9px] text-slate-500">
                        Net: {formatRupiah(deb.internet_flat)}
                      </div>
                    </td>
                    <td className="py-3 px-4 text-center">
                      <span className="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase">
                        {deb.status}
                      </span>
                    </td>
                    <td className="py-3 px-4 text-center">
                      <button
                        onClick={() => setActiveTab('tagihan')}
                        className="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-cyan-400 hover:text-cyan-300 text-[11px] font-semibold transition-colors"
                      >
                        Tagihan
                      </button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal Tambah Debitur */}
      {showAddModal && (
        <div className="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4">
          <div className="bg-[#121418] border border-slate-700 rounded-xl max-w-xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between border-b border-slate-800 pb-3">
              <h3 className="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                <Building className="w-4 h-4 text-cyan-400" />
                Tambah Debitur Kavling Baru
              </h3>
              <button
                onClick={() => setShowAddModal(false)}
                className="text-slate-400 hover:text-white p-1"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleSubmitNew} className="space-y-4">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    Kode Kavling *
                  </label>
                  <input
                    type="text"
                    required
                    placeholder="Contoh: EM-1030"
                    value={formData.kode_kav}
                    onChange={(e) => setFormData({ ...formData, kode_kav: e.target.value })}
                    className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-mono focus:border-cyan-500"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    Nama Pemilik *
                  </label>
                  <input
                    type="text"
                    required
                    placeholder="Nama Lengkap Owner"
                    value={formData.nama_owner}
                    onChange={(e) => setFormData({ ...formData, nama_owner: e.target.value })}
                    className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 focus:border-cyan-500"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    No. Handphone / WhatsApp *
                  </label>
                  <input
                    type="text"
                    required
                    placeholder="62812345678"
                    value={formData.no_hp}
                    onChange={(e) => setFormData({ ...formData, no_hp: e.target.value })}
                    className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-mono focus:border-cyan-500"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">Email</label>
                  <input
                    type="email"
                    placeholder="owner@domain.com"
                    value={formData.email}
                    onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                    className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 focus:border-cyan-500"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    Tipe Unit Villa
                  </label>
                  <select
                    value={formData.tipe_unit}
                    onChange={(e) => setFormData({ ...formData, tipe_unit: e.target.value })}
                    className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 focus:border-cyan-500"
                  >
                    <option value="Villa Type A">Villa Type A</option>
                    <option value="Villa Type B">Villa Type B</option>
                    <option value="Villa Type Royal">Villa Type Royal</option>
                    <option value="Private Suite">Private Suite</option>
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    Daya Listrik PLN (VA)
                  </label>
                  <select
                    value={formData.watt_listrik}
                    onChange={(e) =>
                      setFormData({ ...formData, watt_listrik: Number(e.target.value) })
                    }
                    className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-mono focus:border-cyan-500"
                  >
                    <option value={4400}>4,400 VA</option>
                    <option value={6600}>6,600 VA</option>
                    <option value={10600}>10,600 VA</option>
                    <option value={13200}>13,200 VA</option>
                    <option value={16500}>16,500 VA</option>
                    <option value={23000}>23,000 VA</option>
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    Luas Kawasan (m²)
                  </label>
                  <input
                    type="number"
                    step="1"
                    value={formData.luas_m2_kawasan}
                    onChange={(e) =>
                      setFormData({ ...formData, luas_m2_kawasan: Number(e.target.value) })
                    }
                    className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-mono focus:border-cyan-500 text-right"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    Iuran Internet Flat (Rp)
                  </label>
                  <input
                    type="number"
                    step="1000"
                    value={formData.internet_flat}
                    onChange={(e) =>
                      setFormData({ ...formData, internet_flat: Number(e.target.value) })
                    }
                    className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-mono focus:border-cyan-500 text-right"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  Alamat Lengkap Unit Villa
                </label>
                <textarea
                  rows={2}
                  placeholder="Kavling No..., Cluster Kalicaa Villa..."
                  value={formData.alamat_unit}
                  onChange={(e) => setFormData({ ...formData, alamat_unit: e.target.value })}
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 focus:border-cyan-500"
                ></textarea>
              </div>

              <div className="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button
                  type="button"
                  onClick={() => setShowAddModal(false)}
                  className="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium transition-colors"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  className="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold transition-colors"
                >
                  <Check className="w-4 h-4" />
                  <span>Simpan Debitur</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
