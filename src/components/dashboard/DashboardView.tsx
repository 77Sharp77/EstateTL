import React, { useMemo } from 'react';
import {
  AlertCircle,
  ArrowUpRight,
  BarChart3,
  BellRing,
  CheckCircle2,
  Home,
  MessageCircle,
  Percent,
} from 'lucide-react';
import {
  Bar,
  BarChart,
  CartesianGrid,
  Legend,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';
import { useApp } from '../../context/AppContext';
import { formatNumber, formatRupiah } from '../../utils/format';

export const DashboardView: React.FC = () => {
  const { invoices, debiturs, setActiveTab, setSelectedInvoiceForPrint } = useApp();

  const debiturMap = useMemo(() => {
    return new Map(debiturs.map((d) => [d.id, d]));
  }, [debiturs]);

  // Total Outstanding Piutang
  const totalPiutang = useMemo(() => {
    return invoices
      .filter((i) => i.status_bayar === 'Belum Bayar')
      .reduce((sum, i) => sum + Number(i.grand_total), 0);
  }, [invoices]);

  // Total Lunas
  const totalLunas = useMemo(() => {
    return invoices
      .filter((i) => i.status_bayar === 'Lunas')
      .reduce((sum, i) => sum + Number(i.grand_total), 0);
  }, [invoices]);

  const totalAll = totalPiutang + totalLunas;
  const collectionRate = totalAll > 0 ? (totalLunas / totalAll) * 100 : 100.0;

  // Penerimaan Bulan Ini (current month/year based on June 2026 dataset or current)
  const currentPeriod = useMemo(() => {
    const dates = invoices.map((i) => ({ month: i.periode_bulan, year: i.periode_tahun }));
    if (dates.length > 0) {
      // Find latest period
      return dates.reduce(
        (max, cur) =>
          cur.year * 12 + cur.month > max.year * 12 + max.month ? cur : max,
        dates[0]
      );
    }
    return { month: 6, year: 2026 };
  }, [invoices]);

  const penerimaanBulanIni = useMemo(() => {
    return invoices
      .filter(
        (i) =>
          i.status_bayar === 'Lunas' &&
          i.periode_bulan === currentPeriod.month &&
          i.periode_tahun === currentPeriod.year
      )
      .reduce((sum, i) => sum + Number(i.grand_total), 0);
  }, [invoices, currentPeriod]);

  const totalUnitAktif = debiturs.filter((d) => d.status === 'aktif').length;

  // Tunggakan Invoices list
  const daftarTunggakan = useMemo(() => {
    return invoices
      .filter((i) => i.status_bayar === 'Belum Bayar')
      .map((inv) => {
        const deb = debiturMap.get(inv.debitur_id);
        return {
          ...inv,
          kode_kav: deb?.kode_kav || 'EM-Unknown',
          nama_debitur: deb?.nama_owner || 'Unknown',
          no_hp: deb?.no_hp || '',
        };
      })
      .sort((a, b) => b.periode_tahun * 12 + b.periode_bulan - (a.periode_tahun * 12 + a.periode_bulan));
  }, [invoices, debiturMap]);

  // 6-Month Trend Chart Data
  const chartData = useMemo(() => {
    const months = [
      { label: 'Jan', m: 1, y: 2026 },
      { label: 'Feb', m: 2, y: 2026 },
      { label: 'Mar', m: 3, y: 2026 },
      { label: 'Apr', m: 4, y: 2026 },
      { label: 'Mei', m: 5, y: 2026 },
      { label: 'Jun', m: 6, y: 2026 },
    ];

    return months.map(({ label, m, y }) => {
      const invs = invoices.filter((i) => i.periode_bulan === m && i.periode_tahun === y);
      const lunas = invs
        .filter((i) => i.status_bayar === 'Lunas')
        .reduce((sum, i) => sum + Number(i.grand_total), 0);
      const belum = invs
        .filter((i) => i.status_bayar === 'Belum Bayar')
        .reduce((sum, i) => sum + Number(i.grand_total), 0);

      return {
        periode: `${label} ${y}`,
        Lunas: lunas,
        'Belum Bayar': belum,
      };
    });
  }, [invoices]);

  const getWaReminderUrl = (tunggakan: any) => {
    const text = encodeURIComponent(
      `Yth. Bapak/Ibu ${tunggakan.nama_debitur} (${tunggakan.kode_kav}),\n\nKami menginformasikan bahwa Tagihan Estate Management ${tunggakan.nomor_invoice} periode ${tunggakan.periode_bulan}/${tunggakan.periode_tahun} sebesar ${formatRupiah(tunggakan.grand_total)} belum tercatat pembayarannya.\n\nPembayaran dapat ditransfer ke rekening:\nBCA 1234567890 a.n. PT Kalicaa Management.\n\nTerima kasih.`
    );
    let cleanHp = (tunggakan.no_hp || '').replace(/[^0-9]/g, '');
    if (cleanHp.startsWith('0')) cleanHp = '62' + cleanHp.slice(1);
    return `https://wa.me/${cleanHp}?text=${text}`;
  };

  return (
    <div className="space-y-6">
      {/* Top Banner with Quick Actions */}
      <div className="bg-gradient-to-r from-cyan-950/40 via-[#121418] to-slate-900 border border-cyan-500/20 rounded-xl p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-base font-bold text-white flex items-center gap-2">
            Selamat Datang di Sistem Manajemen Estate & Tagihan
          </h2>
          <p className="text-xs text-slate-400 mt-1">
            Monitoring utilisasi daya listrik, meteran PDAM, iuran kawasan villa, dan saldo kartu piutang.
          </p>
        </div>
        <div className="flex items-center gap-2 shrink-0">
          <button
            onClick={() => setActiveTab('tagihan')}
            className="flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold shadow-lg shadow-cyan-950/40 transition-colors"
          >
            <span>Buat Tagihan Baru</span>
            <ArrowUpRight className="w-4 h-4" />
          </button>
          <button
            onClick={() => setActiveTab('kartu-piutang')}
            className="flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#1a1d23] hover:bg-slate-800 text-slate-200 border border-slate-700 text-xs font-medium transition-colors"
          >
            <span>Kartu Piutang</span>
          </button>
        </div>
      </div>

      {/* KPI Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-4 flex flex-col justify-between">
          <div className="flex items-center justify-between text-slate-400 mb-2">
            <span className="text-xs font-semibold uppercase tracking-wider">
              Total Piutang Belum Bayar
            </span>
            <div className="w-7 h-7 rounded-lg bg-rose-500/10 text-rose-400 flex items-center justify-center">
              <AlertCircle className="w-4 h-4" />
            </div>
          </div>
          <div>
            <p className="text-2xl font-bold font-mono text-rose-400">
              {formatRupiah(totalPiutang)}
            </p>
            <p className="text-[11px] text-slate-500 mt-1">
              {daftarTunggakan.length} invoice menunggak
            </p>
          </div>
        </div>

        <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-4 flex flex-col justify-between">
          <div className="flex items-center justify-between text-slate-400 mb-2">
            <span className="text-xs font-semibold uppercase tracking-wider">
              Penerimaan Bulan Ini
            </span>
            <div className="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
              <CheckCircle2 className="w-4 h-4" />
            </div>
          </div>
          <div>
            <p className="text-2xl font-bold font-mono text-emerald-400">
              {formatRupiah(penerimaanBulanIni)}
            </p>
            <p className="text-[11px] text-slate-500 mt-1">
              Periode {currentPeriod.month}/{currentPeriod.year}
            </p>
          </div>
        </div>

        <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-4 flex flex-col justify-between">
          <div className="flex items-center justify-between text-slate-400 mb-2">
            <span className="text-xs font-semibold uppercase tracking-wider">
              Total Unit Villa Aktif
            </span>
            <div className="w-7 h-7 rounded-lg bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
              <Home className="w-4 h-4" />
            </div>
          </div>
          <div>
            <p className="text-2xl font-bold font-mono text-cyan-400">{totalUnitAktif}</p>
            <p className="text-[11px] text-slate-500 mt-1">
              Dari {debiturs.length} unit kavling terdaftar
            </p>
          </div>
        </div>

        <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-4 flex flex-col justify-between">
          <div className="flex items-center justify-between text-slate-400 mb-2">
            <span className="text-xs font-semibold uppercase tracking-wider">
              Collection Rate
            </span>
            <div className="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
              <Percent className="w-4 h-4" />
            </div>
          </div>
          <div>
            <p className="text-2xl font-bold font-mono text-amber-400">
              {formatNumber(collectionRate, 1)}%
            </p>
            <p className="text-[11px] text-slate-500 mt-1">Rasio tertagih sepanjang masa</p>
          </div>
        </div>
      </div>

      {/* 6-Month Trend Chart */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-4">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
          <div>
            <h3 className="text-sm font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
              <BarChart3 className="w-4 h-4 text-cyan-400" />
              Tren Pembayaran Tagihan Bulanan (6 Bulan Terakhir)
            </h3>
            <p className="text-xs text-slate-500 mt-0.5">
              Perbandingan nominal invoice terbayar (lunas) vs belum terbayar per periode
            </p>
          </div>
        </div>

        <div className="h-64 w-full">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={chartData} margin={{ top: 10, right: 10, left: 20, bottom: 0 }}>
              <CartesianGrid strokeDasharray="3 3" stroke="#262a33" />
              <XAxis dataKey="periode" stroke="#94a3b8" fontSize={11} />
              <YAxis
                stroke="#94a3b8"
                fontSize={10}
                tickFormatter={(val) => `Rp ${(val / 1000000).toFixed(0)} Jt`}
              />
              <Tooltip
                contentStyle={{
                  backgroundColor: '#1a1d23',
                  borderColor: '#334155',
                  borderRadius: '8px',
                  color: '#f8fafc',
                  fontSize: '12px',
                }}
                formatter={(value: any) => [formatRupiah(Number(value)), '']}
              />
              <Legend wrapperStyle={{ fontSize: '11px', color: '#cbd5e1' }} />
              <Bar dataKey="Lunas" fill="#10b981" radius={[4, 4, 0, 0]} />
              <Bar dataKey="Belum Bayar" fill="#f43f5e" radius={[4, 4, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* Monitoring Tunggakan & WhatsApp Reminder Table */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl overflow-hidden">
        <div className="p-4 border-b border-slate-800/80 flex items-center justify-between">
          <div>
            <h3 className="text-sm font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
              <BellRing className="w-4 h-4 text-amber-400" />
              Monitoring Tunggakan & Reminder Tagihan
            </h3>
            <p className="text-xs text-slate-500 mt-0.5">
              Daftar invoice yang belum dilunasi oleh pemilik villa
            </p>
          </div>
          <span className="px-2.5 py-1 rounded text-xs font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
            {daftarTunggakan.length} Tunggakan
          </span>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-[#1a1d23] text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-800">
              <tr>
                <th className="py-3 px-4">No. Invoice</th>
                <th className="py-3 px-4">Kavling</th>
                <th className="py-3 px-4">Nama Pemilik</th>
                <th className="py-3 px-4">Periode</th>
                <th className="py-3 px-4 text-right">Nominal Tagihan</th>
                <th className="py-3 px-4 text-center">Status</th>
                <th className="py-3 px-4 text-center">Aksi Reminder</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {daftarTunggakan.length === 0 ? (
                <tr>
                  <td colSpan={7} className="text-center py-8 text-slate-500">
                    <CheckCircle2 className="w-8 h-8 mx-auto mb-2 opacity-30 text-emerald-400" />
                    Tidak ada tunggakan saat ini. Semua tagihan telah lunas!
                  </td>
                </tr>
              ) : (
                daftarTunggakan.map((row) => (
                  <tr key={row.id} className="hover:bg-slate-800/30 transition-colors">
                    <td className="py-3 px-4 font-mono font-medium text-cyan-400">
                      <button
                        onClick={() => setSelectedInvoiceForPrint(row)}
                        className="hover:underline text-left"
                        title="Klik untuk melihat / mencetak invoice"
                      >
                        {row.nomor_invoice}
                      </button>
                    </td>
                    <td className="py-3 px-4 font-bold text-slate-200">{row.kode_kav}</td>
                    <td className="py-3 px-4 font-medium text-slate-300">{row.nama_debitur}</td>
                    <td className="py-3 px-4 text-slate-400">
                      Bulan {row.periode_bulan}/{row.periode_tahun}
                    </td>
                    <td className="py-3 px-4 text-right font-mono font-bold text-rose-400">
                      {formatRupiah(row.grand_total)}
                    </td>
                    <td className="py-3 px-4 text-center">
                      <span className="px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                        {row.status_bayar}
                      </span>
                    </td>
                    <td className="py-3 px-4 text-center">
                      <a
                        href={getWaReminderUrl(row)}
                        target="_blank"
                        rel="noreferrer"
                        className="inline-flex items-center gap-1.5 px-3 py-1 rounded bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-semibold transition-colors"
                      >
                        <MessageCircle className="w-3.5 h-3.5" />
                        <span>Kirim WA</span>
                      </a>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};
