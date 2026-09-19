import React, { useMemo, useState } from 'react';
import {
  CheckCircle2,
  Clock,
  Eye,
  FileSpreadsheet,
  FileText,
  Filter,
  Layers,
  Printer,
  Search,
} from 'lucide-react';
import { useApp } from '../../context/AppContext';
import { formatDateIndo, formatRupiah } from '../../utils/format';
import { LampiranModal } from './LampiranModal';
import { PrintInvoiceModal } from './PrintInvoiceModal';

export const InvoiceView: React.FC = () => {
  const {
    invoices,
    debiturs,
    toggleInvoiceStatus,
    selectedInvoiceForPrint,
    setSelectedInvoiceForPrint,
    selectedInvoiceForLampiran,
    setSelectedInvoiceForLampiran,
  } = useApp();

  const [searchTerm, setSearchTerm] = useState('');
  const [filterStatus, setFilterStatus] = useState<string>('all');
  const [filterMonth, setFilterMonth] = useState<string>('all');
  const [filterYear, setFilterYear] = useState<string>('all');

  const debiturMap = useMemo(() => {
    return new Map(debiturs.map((d) => [d.id, d]));
  }, [debiturs]);

  const filteredInvoices = useMemo(() => {
    return invoices.filter((inv) => {
      const deb = debiturMap.get(inv.debitur_id);
      const matchSearch =
        inv.nomor_invoice.toLowerCase().includes(searchTerm.toLowerCase()) ||
        (deb && deb.kode_kav.toLowerCase().includes(searchTerm.toLowerCase())) ||
        (deb && deb.nama_owner.toLowerCase().includes(searchTerm.toLowerCase()));

      const matchStatus =
        filterStatus === 'all' || inv.status_bayar === filterStatus;

      const matchMonth =
        filterMonth === 'all' || inv.periode_bulan === Number(filterMonth);

      const matchYear =
        filterYear === 'all' || inv.periode_tahun === Number(filterYear);

      return matchSearch && matchStatus && matchMonth && matchYear;
    });
  }, [invoices, debiturMap, searchTerm, filterStatus, filterMonth, filterYear]);

  const totalNominal = useMemo(() => {
    return filteredInvoices.reduce((sum, i) => sum + Number(i.grand_total), 0);
  }, [filteredInvoices]);

  const totalBelumBayar = useMemo(() => {
    return filteredInvoices
      .filter((i) => i.status_bayar === 'Belum Bayar')
      .reduce((sum, i) => sum + Number(i.grand_total), 0);
  }, [filteredInvoices]);

  return (
    <div className="space-y-6">
      {/* Top Banner Card */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-base font-bold text-white flex items-center gap-2">
            <FileText className="w-5 h-5 text-cyan-400" />
            Daftar Tagihan & Invoice Resmi (IVBW)
          </h2>
          <p className="text-xs text-slate-400 mt-1">
            Arsip faktur penagihan bulanan listrik, air, dan kawasan villa. Cetak invoice A4 resmi dan lampiran teknis meteran.
          </p>
        </div>

        <div className="flex items-center gap-3 shrink-0">
          <div className="text-right">
            <span className="text-[10px] text-slate-400 block uppercase">Total Piutang Tersaring:</span>
            <span className="text-sm font-bold font-mono text-cyan-400">
              {formatRupiah(totalNominal)}
            </span>
          </div>
        </div>
      </div>

      {/* Filter Toolbar */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-4 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <div className="relative flex-1 max-w-sm">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            placeholder="Cari no invoice, kavling, atau nama..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full bg-[#1a1d23] border border-slate-700 text-slate-200 text-xs rounded-lg pl-9 pr-4 py-2 focus:border-cyan-500"
          />
        </div>

        <div className="flex flex-wrap items-center gap-2">
          <select
            value={filterStatus}
            onChange={(e) => setFilterStatus(e.target.value)}
            className="bg-[#1a1d23] border border-slate-700 text-slate-200 text-xs rounded-lg px-2.5 py-2 focus:border-cyan-500"
          >
            <option value="all">Semua Status</option>
            <option value="Belum Bayar">Belum Bayar</option>
            <option value="Lunas">Lunas</option>
          </select>

          <select
            value={filterMonth}
            onChange={(e) => setFilterMonth(e.target.value)}
            className="bg-[#1a1d23] border border-slate-700 text-slate-200 text-xs rounded-lg px-2.5 py-2 focus:border-cyan-500"
          >
            <option value="all">Semua Bulan</option>
            {[1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12].map((m) => (
              <option key={m} value={m}>
                Bulan {m}
              </option>
            ))}
          </select>

          <select
            value={filterYear}
            onChange={(e) => setFilterYear(e.target.value)}
            className="bg-[#1a1d23] border border-slate-700 text-slate-200 text-xs rounded-lg px-2.5 py-2 focus:border-cyan-500"
          >
            <option value="all">Semua Tahun</option>
            <option value="2026">2026</option>
            <option value="2025">2025</option>
          </select>
        </div>
      </div>

      {/* Invoice Table */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl overflow-hidden shadow-sm">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-[#1a1d23] text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-800">
              <tr>
                <th className="py-3 px-4">No. Invoice</th>
                <th className="py-3 px-4">Kavling & Debitur</th>
                <th className="py-3 px-4">Periode</th>
                <th className="py-3 px-4">Tgl Terbit / Tempo</th>
                <th className="py-3 px-4 text-right">Subtotal DPP</th>
                <th className="py-3 px-4 text-right">PPN</th>
                <th className="py-3 px-4 text-right">Grand Total</th>
                <th className="py-3 px-4 text-center">Status</th>
                <th className="py-3 px-4 text-center">Aksi Dokumen</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {filteredInvoices.length === 0 ? (
                <tr>
                  <td colSpan={9} className="text-center py-12 text-slate-500">
                    Tidak ada invoice yang sesuai dengan kriteria filter.
                  </td>
                </tr>
              ) : (
                filteredInvoices.map((inv) => {
                  const deb = debiturMap.get(inv.debitur_id);
                  const isLunas = inv.status_bayar === 'Lunas';

                  return (
                    <tr key={inv.id} className="hover:bg-slate-800/30 transition-colors">
                      <td className="py-3 px-4 font-mono font-bold text-cyan-400">
                        {inv.nomor_invoice}
                      </td>
                      <td className="py-3 px-4">
                        <div className="font-bold text-slate-200">
                          {deb?.kode_kav || '-'}
                        </div>
                        <div className="text-[11px] text-slate-400">
                          {deb?.nama_owner || 'Unknown'}
                        </div>
                      </td>
                      <td className="py-3 px-4 text-slate-300">
                        Bln {inv.periode_bulan}/{inv.periode_tahun}
                      </td>
                      <td className="py-3 px-4 text-slate-400">
                        <div className="text-[11px] text-slate-300">
                          {formatDateIndo(inv.tanggal_terbit)}
                        </div>
                        <div className="text-[10px] text-slate-500">
                          Jatuh tempo: {formatDateIndo(inv.jatuh_tempo)}
                        </div>
                      </td>
                      <td className="py-3 px-4 text-right font-mono text-slate-300">
                        {formatRupiah(inv.subtotal_dpp)}
                      </td>
                      <td className="py-3 px-4 text-right font-mono text-slate-400">
                        {formatRupiah(inv.total_ppn)}
                      </td>
                      <td className="py-3 px-4 text-right font-mono font-bold text-cyan-400">
                        {formatRupiah(inv.grand_total)}
                      </td>
                      <td className="py-3 px-4 text-center">
                        <button
                          onClick={() => toggleInvoiceStatus(inv.id)}
                          className={`px-2.5 py-1 rounded text-[10px] font-semibold border transition-all inline-flex items-center gap-1 ${
                            isLunas
                              ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30 hover:bg-rose-500/20 hover:text-rose-300 hover:border-rose-500/40'
                              : 'bg-rose-500/10 text-rose-400 border-rose-500/30 hover:bg-emerald-500/20 hover:text-emerald-300 hover:border-emerald-500/40'
                          }`}
                          title={isLunas ? 'Klik untuk membatalkan status Lunas' : 'Klik untuk menandai Lunas'}
                        >
                          {isLunas ? (
                            <>
                              <CheckCircle2 className="w-3 h-3" />
                              <span>Lunas</span>
                            </>
                          ) : (
                            <>
                              <Clock className="w-3 h-3" />
                              <span>Belum Bayar</span>
                            </>
                          )}
                        </button>
                      </td>
                      <td className="py-3 px-4 text-center">
                        <div className="inline-flex items-center gap-1.5">
                          <button
                            onClick={() => setSelectedInvoiceForPrint(inv)}
                            className="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-cyan-400 hover:text-cyan-300 text-[11px] font-medium transition-colors flex items-center gap-1"
                            title="Cetak Invoice Resmi A4"
                          >
                            <Printer className="w-3.5 h-3.5" />
                            <span>Cetak</span>
                          </button>

                          <button
                            onClick={() => setSelectedInvoiceForLampiran(inv)}
                            className="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-amber-400 hover:text-amber-300 text-[11px] font-medium transition-colors flex items-center gap-1"
                            title="Lihat Rincian Perhitungan Meteran"
                          >
                            <Eye className="w-3.5 h-3.5" />
                            <span>Lampiran</span>
                          </button>
                        </div>
                      </td>
                    </tr>
                  );
                })
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modals for Print & Lampiran */}
      {selectedInvoiceForPrint && (
        <PrintInvoiceModal
          invoice={selectedInvoiceForPrint}
          onClose={() => setSelectedInvoiceForPrint(null)}
        />
      )}

      {selectedInvoiceForLampiran && (
        <LampiranModal
          invoice={selectedInvoiceForLampiran}
          onClose={() => setSelectedInvoiceForLampiran(null)}
        />
      )}
    </div>
  );
};
