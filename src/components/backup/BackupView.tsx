import React, { useRef, useState } from 'react';
import {
  AlertTriangle,
  Archive,
  Check,
  Database,
  Download,
  FolderArchive,
  RefreshCw,
  Upload,
} from 'lucide-react';
import { useApp } from '../../context/AppContext';

export const BackupView: React.FC = () => {
  const {
    debiturs,
    invoices,
    invoiceItems,
    skpList,
    spkList,
    spkPayments,
    titipanList,
    titipanPvbwList,
    rvbwList,
    settings,
    resetToInitialData,
    exportAllDataAsJson,
    restoreDataFromJson,
  } = useApp();

  const fileInputRef = useRef<HTMLInputElement>(null);
  const [restoreMessage, setRestoreMessage] = useState<{ text: string; isError: boolean } | null>(
    null
  );

  const handleDownloadBackup = () => {
    const jsonStr = exportAllDataAsJson();
    const blob = new Blob([jsonStr], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `backup_kalicaa_estate_${new Date().toISOString().replace(/[:.]/g, '-')}.json`;
    a.click();
    URL.revokeObjectURL(url);
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (event) => {
      const content = event.target?.result as string;
      const success = restoreDataFromJson(content);
      if (success) {
        setRestoreMessage({ text: 'Data cadangan berhasil dipulihkan!', isError: false });
      } else {
        setRestoreMessage({
          text: 'Format file cadangan tidak valid atau rusak.',
          isError: true,
        });
      }
      setTimeout(() => setRestoreMessage(null), 4000);
    };
    reader.readAsText(file);
    e.target.value = '';
  };

  const handleReset = () => {
    if (
      confirm(
        'PERINGATAN: Seluruh data transaksi lokal akan direset ke kondisi awal master Kalicaa Villa. Lanjutkan?'
      )
    ) {
      resetToInitialData();
      setRestoreMessage({
        text: 'Database berhasil direset ke data sampel awal.',
        isError: false,
      });
      setTimeout(() => setRestoreMessage(null), 4000);
    }
  };

  return (
    <div className="space-y-6">
      {/* Banner */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-base font-bold text-white flex items-center gap-2">
            <Archive className="w-5 h-5 text-cyan-400" />
            Backup & Pemulihan Database Sistem
          </h2>
          <p className="text-xs text-slate-400 mt-1">
            Ekspor seluruh data master debitur, faktur tagihan, catatan kartu piutang, dan kontrak ke file cadangan.
          </p>
        </div>

        {restoreMessage && (
          <div
            className={`px-3.5 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-2 ${
              restoreMessage.isError
                ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20'
                : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
            }`}
          >
            {restoreMessage.isError ? (
              <AlertTriangle className="w-4 h-4" />
            ) : (
              <Check className="w-4 h-4" />
            )}
            <span>{restoreMessage.text}</span>
          </div>
        )}
      </div>

      {/* Database Statistics */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5">
        <h3 className="text-xs font-bold text-slate-300 uppercase tracking-wider mb-4 flex items-center gap-2">
          <Database className="w-4 h-4 text-cyan-400" />
          Statistik Objek Database Aktif
        </h3>

        <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <div className="bg-[#1a1d23] p-3 rounded-lg border border-slate-800">
            <span className="text-[10px] text-slate-500 uppercase block">Master Debitur</span>
            <span className="text-lg font-mono font-bold text-cyan-400">
              {debiturs.length} Unit
            </span>
          </div>
          <div className="bg-[#1a1d23] p-3 rounded-lg border border-slate-800">
            <span className="text-[10px] text-slate-500 uppercase block">Faktur Invoice (IVBW)</span>
            <span className="text-lg font-mono font-bold text-slate-200">
              {invoices.length} Lembar
            </span>
          </div>
          <div className="bg-[#1a1d23] p-3 rounded-lg border border-slate-800">
            <span className="text-[10px] text-slate-500 uppercase block">Rincian Item Meteran</span>
            <span className="text-lg font-mono font-bold text-slate-200">
              {invoiceItems.length} Baris
            </span>
          </div>
          <div className="bg-[#1a1d23] p-3 rounded-lg border border-slate-800">
            <span className="text-[10px] text-slate-500 uppercase block">Voucher RVBW</span>
            <span className="text-lg font-mono font-bold text-emerald-400">
              {rvbwList.length} Lembar
            </span>
          </div>
          <div className="bg-[#1a1d23] p-3 rounded-lg border border-slate-800">
            <span className="text-[10px] text-slate-500 uppercase block">Kontrak Penjualan SKP</span>
            <span className="text-lg font-mono font-bold text-amber-400">
              {skpList.length} Kontrak
            </span>
          </div>
          <div className="bg-[#1a1d23] p-3 rounded-lg border border-slate-800">
            <span className="text-[10px] text-slate-500 uppercase block">Kontrak Kerja SPK</span>
            <span className="text-lg font-mono font-bold text-cyan-400">
              {spkList.length} SPK
            </span>
          </div>
          <div className="bg-[#1a1d23] p-3 rounded-lg border border-slate-800">
            <span className="text-[10px] text-slate-500 uppercase block">Pencairan Termin SPK</span>
            <span className="text-lg font-mono font-bold text-slate-200">
              {spkPayments.length} Pembayaran
            </span>
          </div>
          <div className="bg-[#1a1d23] p-3 rounded-lg border border-slate-800">
            <span className="text-[10px] text-slate-500 uppercase block">Titipan Dana Jaminan</span>
            <span className="text-lg font-mono font-bold text-emerald-400">
              {titipanList.length} Kasus
            </span>
          </div>
        </div>
      </div>

      {/* Backup & Restore Action Grid */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {/* Export Backup */}
        <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-3 flex flex-col justify-between">
          <div className="space-y-2">
            <div className="w-10 h-10 rounded-lg bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
              <Download className="w-5 h-5" />
            </div>
            <h4 className="text-sm font-bold text-white">Unduh Cadangan Lengkap (JSON)</h4>
            <p className="text-xs text-slate-400">
              Simpan seluruh database, riwayat tagihan, dan pengaturan saat ini ke dalam satu file berkas JSON portabel.
            </p>
          </div>

          <button
            onClick={handleDownloadBackup}
            className="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold shadow-md transition-colors"
          >
            <Download className="w-4 h-4" />
            <span>Download Backup (.json)</span>
          </button>
        </div>

        {/* Restore Backup */}
        <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-3 flex flex-col justify-between">
          <div className="space-y-2">
            <div className="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
              <Upload className="w-5 h-5" />
            </div>
            <h4 className="text-sm font-bold text-white">Pulihkan Data dari Cadangan</h4>
            <p className="text-xs text-slate-400">
              Unggah file JSON backup yang pernah disimpan sebelumnya untuk memulihkan seluruh data dan histori transaksi.
            </p>
          </div>

          <div>
            <input
              type="file"
              accept=".json"
              ref={fileInputRef}
              onChange={handleFileChange}
              className="hidden"
            />
            <button
              onClick={() => fileInputRef.current?.click()}
              className="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-md transition-colors"
            >
              <Upload className="w-4 h-4" />
              <span>Pilih File Backup (.json)</span>
            </button>
          </div>
        </div>

        {/* Factory Reset */}
        <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-3 flex flex-col justify-between">
          <div className="space-y-2">
            <div className="w-10 h-10 rounded-lg bg-rose-500/10 text-rose-400 flex items-center justify-center">
              <RefreshCw className="w-5 h-5" />
            </div>
            <h4 className="text-sm font-bold text-white">Reset ke Data Standar Kalicaa</h4>
            <p className="text-xs text-slate-400">
              Menghapus rekaman transaksi yang ditambahkan dan mengembalikan database ke unit standar Kalicaa Villa.
            </p>
          </div>

          <button
            onClick={handleReset}
            className="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-slate-800 hover:bg-rose-950/40 text-rose-400 border border-rose-500/30 text-xs font-semibold transition-colors"
          >
            <RefreshCw className="w-4 h-4" />
            <span>Reset ke Data Awal</span>
          </button>
        </div>
      </div>
    </div>
  );
};
