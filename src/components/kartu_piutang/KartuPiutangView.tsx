import React, { useMemo, useState } from 'react';
import {
  AlertCircle,
  BookOpen,
  ChevronDown,
  ChevronRight,
  Filter,
  Layers,
  Search,
  SlidersHorizontal,
  Wallet,
} from 'lucide-react';
import { useApp } from '../../context/AppContext';
import { formatDateIndo, formatNumber, formatRupiah } from '../../utils/format';

export const KartuPiutangView: React.FC = () => {
  const { debiturs, invoices, invoiceItems, skpList, setSelectedInvoiceForPrint } = useApp();

  const [searchTerm, setSearchTerm] = useState('');
  const [filterType, setFilterType] = useState<'all' | 'estate' | 'skp'>('all');
  const [hideZeroBalance, setHideZeroBalance] = useState(false);
  const [sortBy, setSortBy] = useState<'kavling' | 'saldo_desc' | 'nama'>('saldo_desc');
  const [expandedDebiturIds, setExpandedDebiturIds] = useState<Set<number>>(new Set([1, 4]));

  const toggleExpand = (id: number) => {
    setExpandedDebiturIds((prev) => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });
  };

  const expandAll = () => {
    setExpandedDebiturIds(new Set(debiturs.map((d) => d.id)));
  };

  const collapseAll = () => {
    setExpandedDebiturIds(new Set());
  };

  // Group invoices & items and calculate running balance per debitur
  const kartuDebiturList = useMemo(() => {
    return debiturs.map((deb) => {
      // Find invoices for this debitur
      const debInvoices = invoices
        .filter((i) => i.debitur_id === deb.id)
        .sort((a, b) => {
          // Sort chronologically
          const timeA = new Date(a.tanggal_terbit).getTime();
          const timeB = new Date(b.tanggal_terbit).getTime();
          return timeA - timeB;
        });

      let runningSaldo = 0;
      let totalDebit = 0;
      let totalKredit = 0;

      const entries = debInvoices.map((inv) => {
        const debit = Number(inv.grand_total);
        const kredit = inv.status_bayar === 'Lunas' ? Number(inv.grand_total) : 0;
        runningSaldo = runningSaldo + debit - kredit;
        totalDebit += debit;
        totalKredit += kredit;

        // Breakdown items
        const items = invoiceItems.filter((it) => it.invoice_id === inv.id);
        const listrik = items
          .filter((it) => it.tagihan_bulanan_kategori === 'LISTRIK' || it.keterangan.includes('Listrik'))
          .reduce((sum, it) => sum + Number(it.total), 0);
        const air = items
          .filter((it) => it.tagihan_bulanan_kategori === 'AIR' || it.keterangan.includes('Air'))
          .reduce((sum, it) => sum + Number(it.total), 0);
        const kawasan = items
          .filter((it) => it.tagihan_bulanan_kategori === 'KAWASAN' || it.keterangan.includes('Kawasan'))
          .reduce((sum, it) => sum + Number(it.total), 0);
        const lain = items
          .filter(
            (it) =>
              it.tagihan_bulanan_kategori !== 'LISTRIK' &&
              it.tagihan_bulanan_kategori !== 'AIR' &&
              it.tagihan_bulanan_kategori !== 'KAWASAN' &&
              !it.keterangan.includes('Listrik') &&
              !it.keterangan.includes('Air') &&
              !it.keterangan.includes('Kawasan')
          )
          .reduce((sum, it) => sum + Number(it.total), 0);

        return {
          id: inv.id,
          nomor_invoice: inv.nomor_invoice,
          tanggal: inv.tanggal_terbit,
          periode_bulan: inv.periode_bulan,
          periode_tahun: inv.periode_tahun,
          debit,
          kredit,
          saldo: runningSaldo,
          status_bayar: inv.status_bayar,
          tanggal_lunas: inv.tanggal_lunas,
          breakdown: { listrik, air, kawasan, lain },
          rawInvoice: inv,
        };
      });

      return {
        debitur: deb,
        entries,
        totalDebit,
        totalKredit,
        saldoAkhir: runningSaldo,
      };
    });
  }, [debiturs, invoices, invoiceItems]);

  // Filter & Sort Kartu
  const filteredKartuList = useMemo(() => {
    return kartuDebiturList
      .filter((k) => {
        const matchSearch =
          k.debitur.nama_owner.toLowerCase().includes(searchTerm.toLowerCase()) ||
          k.debitur.kode_kav.toLowerCase().includes(searchTerm.toLowerCase());

        const matchZero = hideZeroBalance ? k.saldoAkhir > 0 : true;

        return matchSearch && matchZero;
      })
      .sort((a, b) => {
        if (sortBy === 'saldo_desc') return b.saldoAkhir - a.saldoAkhir;
        if (sortBy === 'nama') return a.debitur.nama_owner.localeCompare(b.debitur.nama_owner);
        return a.debitur.kode_kav.localeCompare(b.debitur.kode_kav);
      });
  }, [kartuDebiturList, searchTerm, hideZeroBalance, sortBy]);

  const grandTotalOutstanding = useMemo(() => {
    return filteredKartuList.reduce((sum, k) => sum + k.saldoAkhir, 0);
  }, [filteredKartuList]);

  return (
    <div className="space-y-6">
      {/* Header Banner */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-base font-bold text-white flex items-center gap-2">
            <BookOpen className="w-5 h-5 text-cyan-400" />
            Kartu Piutang Debitur (Running Balance Dinamis)
          </h2>
          <p className="text-xs text-slate-400 mt-1">
            Buku besar pembantu piutang per unit kavling dengan kalkulasi saldo berjalan otomatis (Saldo = Saldo Lalu + Debit - Kredit).
          </p>
        </div>

        <div className="text-right shrink-0">
          <span className="text-[10px] text-slate-400 uppercase tracking-wider block">
            Total Saldo Piutang Berjalan:
          </span>
          <span className="text-base font-extrabold font-mono text-rose-400">
            {formatRupiah(grandTotalOutstanding)}
          </span>
        </div>
      </div>

      {/* Filter & Controls Bar */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-4 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <div className="relative flex-1 max-w-sm">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            placeholder="Cari kavling atau nama debitur..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full bg-[#1a1d23] border border-slate-700 text-slate-200 text-xs rounded-lg pl-9 pr-4 py-2 focus:border-cyan-500"
          />
        </div>

        <div className="flex flex-wrap items-center gap-2.5">
          <select
            value={sortBy}
            onChange={(e: any) => setSortBy(e.target.value)}
            className="bg-[#1a1d23] border border-slate-700 text-slate-200 text-xs rounded-lg px-3 py-2 focus:border-cyan-500"
          >
            <option value="saldo_desc">Urutkan: Saldo Terbesar</option>
            <option value="kavling">Urutkan: Kode Kavling</option>
            <option value="nama">Urutkan: Nama Pemilik</option>
          </select>

          <label className="flex items-center gap-2 bg-[#1a1d23] border border-slate-700 px-3 py-2 rounded-lg text-xs text-slate-300 cursor-pointer select-none">
            <input
              type="checkbox"
              checked={hideZeroBalance}
              onChange={(e) => setHideZeroBalance(e.target.checked)}
              className="rounded border-slate-700 text-cyan-600 focus:ring-0"
            />
            <span>Sembunyikan Saldo 0</span>
          </label>

          <div className="flex items-center gap-1 border-l border-slate-800 pl-2">
            <button
              onClick={expandAll}
              className="px-2.5 py-1.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] font-medium"
            >
              Buka Semua
            </button>
            <button
              onClick={collapseAll}
              className="px-2.5 py-1.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] font-medium"
            >
              Tutup
            </button>
          </div>
        </div>
      </div>

      {/* Kartu Piutang Cards Container */}
      <div className="space-y-4">
        {filteredKartuList.length === 0 ? (
          <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-10 text-center text-slate-500">
            Tidak ada data kartu piutang sesuai pencarian.
          </div>
        ) : (
          filteredKartuList.map((item) => {
            const isExpanded = expandedDebiturIds.has(item.debitur.id);
            const hasSaldo = item.saldoAkhir > 0;

            return (
              <div
                key={item.debitur.id}
                className="bg-[#121418] border border-slate-800/80 rounded-xl overflow-hidden transition-all shadow-sm"
              >
                {/* Debitur Summary Header Row */}
                <div
                  onClick={() => toggleExpand(item.debitur.id)}
                  className="p-4 bg-[#14171d] hover:bg-[#1a1d23] cursor-pointer flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800/60 select-none transition-colors"
                >
                  <div className="flex items-center gap-3">
                    <button className="text-slate-400 p-0.5">
                      {isExpanded ? (
                        <ChevronDown className="w-4 h-4 text-cyan-400" />
                      ) : (
                        <ChevronRight className="w-4 h-4" />
                      )}
                    </button>
                    <div>
                      <div className="flex items-center gap-2">
                        <span className="font-bold font-mono text-cyan-400 text-sm">
                          {item.debitur.kode_kav}
                        </span>
                        <span className="text-xs font-semibold text-slate-200">
                          {item.debitur.nama_owner}
                        </span>
                        <span className="text-[10px] px-2 py-0.2 rounded bg-slate-800 text-slate-400">
                          {item.debitur.tipe_unit}
                        </span>
                      </div>
                      <div className="text-[11px] text-slate-500 mt-0.5">
                        Daya: {formatNumber(item.debitur.watt_listrik)} VA • Luas: {item.debitur.luas_m2_kawasan} m² • HP: {item.debitur.no_hp}
                      </div>
                    </div>
                  </div>

                  {/* Financial Balance Summary for Debitur */}
                  <div className="flex items-center gap-6 self-end sm:self-auto text-xs">
                    <div className="text-right hidden sm:block">
                      <span className="text-[10px] text-slate-500 uppercase block">Total Tagihan (Debit):</span>
                      <span className="font-mono text-slate-300">{formatRupiah(item.totalDebit)}</span>
                    </div>
                    <div className="text-right hidden sm:block">
                      <span className="text-[10px] text-slate-500 uppercase block">Total Pembayaran (Kredit):</span>
                      <span className="font-mono text-emerald-400">{formatRupiah(item.totalKredit)}</span>
                    </div>
                    <div className="text-right pl-2 border-l border-slate-800">
                      <span className="text-[10px] text-slate-400 uppercase block font-semibold">
                        Sisa Piutang:
                      </span>
                      <span
                        className={`font-mono font-bold text-sm ${
                          hasSaldo ? 'text-rose-400' : 'text-emerald-400'
                        }`}
                      >
                        {formatRupiah(item.saldoAkhir)}
                      </span>
                    </div>
                  </div>
                </div>

                {/* Expanded Transactions Table */}
                {isExpanded && (
                  <div className="overflow-x-auto">
                    <table className="w-full text-left text-xs">
                      <thead className="bg-[#181b21] text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-800">
                        <tr>
                          <th className="py-2.5 px-4">Tgl & No. Invoice</th>
                          <th className="py-2.5 px-4">Periode</th>
                          <th className="py-2.5 px-3 text-right">Listrik PLN</th>
                          <th className="py-2.5 px-3 text-right">Air PDAM</th>
                          <th className="py-2.5 px-3 text-right">Kawasan / IPL</th>
                          <th className="py-2.5 px-4 text-right">Debit (Tagihan)</th>
                          <th className="py-2.5 px-4 text-right">Kredit (Bayar)</th>
                          <th className="py-2.5 px-4 text-right">Saldo Berjalan</th>
                          <th className="py-2.5 px-3 text-center">Status</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-slate-800/50">
                        {item.entries.length === 0 ? (
                          <tr>
                            <td colSpan={9} className="text-center py-6 text-slate-500">
                              Belum ada catatan transaksi tagihan untuk unit ini.
                            </td>
                          </tr>
                        ) : (
                          item.entries.map((entry) => (
                            <tr key={entry.id} className="hover:bg-slate-800/20">
                              <td className="py-2.5 px-4">
                                <div className="font-mono text-cyan-400 font-medium">
                                  <button
                                    onClick={() => setSelectedInvoiceForPrint(entry.rawInvoice)}
                                    className="hover:underline text-left"
                                  >
                                    {entry.nomor_invoice}
                                  </button>
                                </div>
                                <div className="text-[10px] text-slate-500">
                                  {formatDateIndo(entry.tanggal)}
                                </div>
                              </td>
                              <td className="py-2.5 px-4 text-slate-300 font-medium">
                                Bulan {entry.periode_bulan}/{entry.periode_tahun}
                              </td>
                              <td className="py-2.5 px-3 text-right font-mono text-slate-400">
                                {formatRupiah(entry.breakdown.listrik)}
                              </td>
                              <td className="py-2.5 px-3 text-right font-mono text-slate-400">
                                {formatRupiah(entry.breakdown.air)}
                              </td>
                              <td className="py-2.5 px-3 text-right font-mono text-slate-400">
                                {formatRupiah(entry.breakdown.kawasan)}
                              </td>
                              <td className="py-2.5 px-4 text-right font-mono font-semibold text-slate-200">
                                {formatRupiah(entry.debit)}
                              </td>
                              <td className="py-2.5 px-4 text-right font-mono font-semibold text-emerald-400">
                                {entry.kredit > 0 ? formatRupiah(entry.kredit) : '-'}
                              </td>
                              <td className="py-2.5 px-4 text-right font-mono font-bold text-amber-400">
                                {formatRupiah(entry.saldo)}
                              </td>
                              <td className="py-2.5 px-3 text-center">
                                <span
                                  className={`px-2 py-0.5 rounded text-[10px] font-semibold ${
                                    entry.status_bayar === 'Lunas'
                                      ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                                      : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'
                                  }`}
                                >
                                  {entry.status_bayar}
                                </span>
                              </td>
                            </tr>
                          ))
                        )}
                      </tbody>
                    </table>
                  </div>
                )}
              </div>
            );
          })
        )}
      </div>
    </div>
  );
};
