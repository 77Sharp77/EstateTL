import React, { useMemo, useState } from 'react';
import {
  CheckCircle,
  Clock,
  FileCheck2,
  Filter,
  Plus,
  Receipt,
  Search,
  Send,
} from 'lucide-react';
import { useApp } from '../../context/AppContext';
import { formatDateIndo, formatRupiah } from '../../utils/format';

export const RvbwView: React.FC = () => {
  const { rvbwList, debiturs, updateRvbwStatus, setActiveTab } = useApp();
  const [searchTerm, setSearchTerm] = useState('');
  const [filterStatus, setFilterStatus] = useState('all');

  const debiturMap = useMemo(() => {
    return new Map(debiturs.map((d) => [d.id, d]));
  }, [debiturs]);

  const filteredList = useMemo(() => {
    return rvbwList.filter((r) => {
      const deb = debiturMap.get(r.debitur_id);
      const matchSearch =
        r.nomor_rvbw.toLowerCase().includes(searchTerm.toLowerCase()) ||
        (deb && deb.kode_kav.toLowerCase().includes(searchTerm.toLowerCase())) ||
        (deb && deb.nama_owner.toLowerCase().includes(searchTerm.toLowerCase()));

      const matchStatus = filterStatus === 'all' || r.status === filterStatus;
      return matchSearch && matchStatus;
    });
  }, [rvbwList, debiturMap, searchTerm, filterStatus]);

  const totalNominal = useMemo(() => {
    return filteredList.reduce((sum, r) => sum + Number(r.grand_total), 0);
  }, [filteredList]);

  return (
    <div className="space-y-6">
      {/* Top Banner */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-base font-bold text-white flex items-center gap-2">
            <Receipt className="w-5 h-5 text-cyan-400" />
            Penerimaan Voucher RVBW
          </h2>
          <p className="text-xs text-slate-400 mt-1">
            Daftar voucher penerimaan kas & bank atas penagihan tagihan utility villa sebelum di-posting ke buku besar.
          </p>
        </div>

        <div className="flex items-center gap-3 shrink-0">
          <button
            onClick={() => setActiveTab('tagihan')}
            className="flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold shadow-md transition-colors"
          >
            <Plus className="w-4 h-4" />
            <span>Generate dari Tagihan</span>
          </button>
        </div>
      </div>

      {/* Filter Bar */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div className="relative flex-1 max-w-sm">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            placeholder="Cari nomor RVBW atau debitur..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full bg-[#1a1d23] border border-slate-700 text-slate-200 text-xs rounded-lg pl-9 pr-4 py-2 focus:border-cyan-500"
          />
        </div>

        <div className="flex items-center gap-3">
          <select
            value={filterStatus}
            onChange={(e) => setFilterStatus(e.target.value)}
            className="bg-[#1a1d23] border border-slate-700 text-slate-200 text-xs rounded-lg px-3 py-2 focus:border-cyan-500"
          >
            <option value="all">Semua Status</option>
            <option value="Draft">Draft</option>
            <option value="Disetujui">Disetujui</option>
            <option value="Diposting">Diposting</option>
          </select>
        </div>
      </div>

      {/* RVBW Table */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl overflow-hidden shadow-sm">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-[#1a1d23] text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-800">
              <tr>
                <th className="py-3 px-4">No. RVBW</th>
                <th className="py-3 px-4">Tanggal Voucher</th>
                <th className="py-3 px-4">Kavling & Debitur</th>
                <th className="py-3 px-4">Periode</th>
                <th className="py-3 px-4 text-right">Subtotal DPP</th>
                <th className="py-3 px-4 text-right">PPN</th>
                <th className="py-3 px-4 text-right">Grand Total</th>
                <th className="py-3 px-4 text-center">Status</th>
                <th className="py-3 px-4 text-center">Aksi Status</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {filteredList.length === 0 ? (
                <tr>
                  <td colSpan={9} className="text-center py-10 text-slate-500">
                    Tidak ada voucher RVBW sesuai filter.
                  </td>
                </tr>
              ) : (
                filteredList.map((rv) => {
                  const deb = debiturMap.get(rv.debitur_id);
                  return (
                    <tr key={rv.id} className="hover:bg-slate-800/30 transition-colors">
                      <td className="py-3 px-4 font-mono font-bold text-cyan-400">
                        {rv.nomor_rvbw}
                      </td>
                      <td className="py-3 px-4 text-slate-300">
                        {formatDateIndo(rv.tanggal)}
                      </td>
                      <td className="py-3 px-4">
                        <div className="font-bold text-slate-200">{deb?.kode_kav}</div>
                        <div className="text-[11px] text-slate-400">{deb?.nama_owner}</div>
                      </td>
                      <td className="py-3 px-4 text-slate-300">
                        Bulan {rv.periode_bulan}/{rv.periode_tahun}
                      </td>
                      <td className="py-3 px-4 text-right font-mono text-slate-300">
                        {formatRupiah(rv.subtotal_dpp)}
                      </td>
                      <td className="py-3 px-4 text-right font-mono text-slate-400">
                        {formatRupiah(rv.total_ppn)}
                      </td>
                      <td className="py-3 px-4 text-right font-mono font-bold text-cyan-400">
                        {formatRupiah(rv.grand_total)}
                      </td>
                      <td className="py-3 px-4 text-center">
                        <span
                          className={`px-2.5 py-0.5 rounded text-[10px] font-semibold uppercase ${
                            rv.status === 'Diposting'
                              ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                              : rv.status === 'Disetujui'
                              ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20'
                              : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
                          }`}
                        >
                          {rv.status}
                        </span>
                      </td>
                      <td className="py-3 px-4 text-center">
                        {rv.status === 'Draft' ? (
                          <button
                            onClick={() => updateRvbwStatus(rv.id, 'Disetujui')}
                            className="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-cyan-400 text-[11px] font-medium"
                          >
                            Setujui
                          </button>
                        ) : rv.status === 'Disetujui' ? (
                          <button
                            onClick={() => updateRvbwStatus(rv.id, 'Diposting')}
                            className="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-emerald-400 text-[11px] font-medium"
                          >
                            Posting
                          </button>
                        ) : (
                          <span className="text-[10px] text-slate-500 flex items-center justify-center gap-1">
                            <CheckCircle className="w-3 h-3 text-emerald-400" />
                            Final
                          </span>
                        )}
                      </td>
                    </tr>
                  );
                })
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};
