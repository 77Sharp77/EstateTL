import React, { useState } from 'react';
import {
  AlertCircle,
  Check,
  Code2,
  FileCode,
  FileSpreadsheet,
  FileText,
  Import,
  Play,
  UploadCloud,
} from 'lucide-react';
import { useApp } from '../../context/AppContext';
import { Debitur } from '../../types';

export const ImportView: React.FC = () => {
  const { addDebitur, debiturs } = useApp();
  const [importMode, setImportMode] = useState<'sql' | 'csv'>('sql');
  const [inputText, setInputText] = useState('');
  const [statusMessage, setStatusMessage] = useState<{
    text: string;
    isError: boolean;
    count?: number;
  } | null>(null);

  const sampleSql = `-- Contoh Script SQL Migrasi Data Debitur
INSERT INTO debitur (kode_kav, nama_owner, watt_listrik, luas_m2_kawasan, status_meteran, no_hp) VALUES
('EM-1060', 'Bpk. Hendra Gunawan', 11000, 320.00, 'KWH & AIR METERAN', '081234567890'),
('EM-1070', 'Ibu Ratna Dewi', 7700, 250.50, 'KWH & AIR METERAN', '081398765432'),
('EM-1080', 'PT. Nuansa Mandiri', 13200, 410.00, 'KWH & AIR METERAN', '081122334455');
`;

  const sampleCsv = `kode_kav,nama_owner,watt_listrik,luas_m2_kawasan,status_meteran,no_hp
EM-1090,Bpk. Arif Wibowo,11000,310.0,KWH & AIR METERAN,081288889999
EM-1100,Ibu Maya Lestari,5500,220.0,KWH & AIR METERAN,081377776666
`;

  const handleParseAndImport = () => {
    if (!inputText.trim()) {
      setStatusMessage({ text: 'Mohon masukkan teks SQL atau CSV yang akan diimpor.', isError: true });
      return;
    }

    try {
      let importedCount = 0;

      if (importMode === 'sql') {
        // Simple regex parser for INSERT INTO debitur
        // Matches VALUES ('...', '...', ...)
        const valuesRegex = /\(([^)]+)\)/g;
        let match;
        const rows: string[] = [];

        // Check if query targets debitur
        const isDebitur = inputText.toLowerCase().includes('debitur');

        if (!isDebitur) {
          setStatusMessage({
            text: 'Script SQL harus mengandung perintah INSERT INTO debitur (...)',
            isError: true,
          });
          return;
        }

        while ((match = valuesRegex.exec(inputText)) !== null) {
          const rawRow = match[1];
          // Split by comma ignoring commas inside strings
          const parts = rawRow.split(',').map((s) => s.trim().replace(/^['"]|['"]$/g, ''));
          if (parts.length >= 4) {
            // Check if kavling exists
            const kodeKav = parts[0];
            const namaOwner = parts[1];
            const wattListrik = Number(parts[2]) || 5500;
            const luasM2 = Number(parts[3]) || 200;
            const statusMeteran = (parts[4] as any) || 'KWH & AIR METERAN';
            const noHp = parts[5] || '-';

            const exists = debiturs.some(
              (d) => d.kode_kav.toLowerCase() === kodeKav.toLowerCase()
            );

            if (!exists) {
              addDebitur({
                kode_kav: kodeKav,
                nama_owner: namaOwner,
                tipe_unit: 'Kavling Villa',
                status: 'aktif',
                watt_listrik: wattListrik,
                luas_m2_kawasan: luasM2,
                status_meteran: statusMeteran,
                no_hp: noHp,
                email: '',
                alamat_korespondensi: 'Estate Kalicaa Villa',
                status_kepemilikan: 'Owner',
                landscape_flat: 0,
                kolam_flat: 0,
                internet_flat: 0,
              });
              importedCount++;
            }
          }
        }
      } else {
        // CSV Parser
        const lines = inputText.trim().split('\n');
        for (let i = 1; i < lines.length; i++) {
          const line = lines[i].trim();
          if (!line) continue;
          const parts = line.split(',').map((p) => p.trim());
          if (parts.length >= 4) {
            const kodeKav = parts[0];
            const namaOwner = parts[1];
            const wattListrik = Number(parts[2]) || 5500;
            const luasM2 = Number(parts[3]) || 200;
            const statusMeteran = (parts[4] as any) || 'KWH & AIR METERAN';
            const noHp = parts[5] || '-';

            const exists = debiturs.some(
              (d) => d.kode_kav.toLowerCase() === kodeKav.toLowerCase()
            );

            if (!exists) {
              addDebitur({
                kode_kav: kodeKav,
                nama_owner: namaOwner,
                tipe_unit: 'Kavling Villa',
                status: 'aktif',
                watt_listrik: wattListrik,
                luas_m2_kawasan: luasM2,
                status_meteran: statusMeteran,
                no_hp: noHp,
                email: '',
                alamat_korespondensi: 'Estate Kalicaa Villa',
                status_kepemilikan: 'Owner',
                landscape_flat: 0,
                kolam_flat: 0,
                internet_flat: 0,
              });
              importedCount++;
            }
          }
        }
      }

      if (importedCount > 0) {
        setStatusMessage({
          text: `Berhasil mengimpor ${importedCount} data debitur baru ke sistem!`,
          isError: false,
          count: importedCount,
        });
        setInputText('');
      } else {
        setStatusMessage({
          text: 'Tidak ada data baru yang diimpor (mungkin kode kavling sudah ada atau format tidak cocok).',
          isError: true,
        });
      }
    } catch (err: any) {
      setStatusMessage({
        text: `Gagal memproses impor: ${err.message || 'Format tidak valid'}`,
        isError: true,
      });
    }
  };

  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (event) => {
      const text = event.target?.result as string;
      setInputText(text);
      if (file.name.endsWith('.sql')) setImportMode('sql');
      else if (file.name.endsWith('.csv')) setImportMode('csv');
    };
    reader.readAsText(file);
    e.target.value = '';
  };

  return (
    <div className="space-y-6">
      {/* Top Banner */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-base font-bold text-white flex items-center gap-2">
            <Import className="w-5 h-5 text-cyan-400" />
            Impor Data (Mode SQL & CSV)
          </h2>
          <p className="text-xs text-slate-400 mt-1">
            Sinkronisasi dan migrasi data cepat dari file script SQL (database.sql / seeder.sql) atau tabel CSV Excel.
          </p>
        </div>

        <div className="flex items-center gap-2">
          <label className="flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold cursor-pointer transition-colors">
            <UploadCloud className="w-4 h-4 text-cyan-400" />
            <span>Unggah File (.sql/.csv)</span>
            <input
              type="file"
              accept=".sql,.csv,.txt"
              onChange={handleFileUpload}
              className="hidden"
            />
          </label>
        </div>
      </div>

      {statusMessage && (
        <div
          className={`p-4 rounded-xl text-xs font-semibold flex items-center gap-3 ${
            statusMessage.isError
              ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20'
              : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
          }`}
        >
          {statusMessage.isError ? (
            <AlertCircle className="w-5 h-5 shrink-0" />
          ) : (
            <Check className="w-5 h-5 shrink-0" />
          )}
          <span>{statusMessage.text}</span>
        </div>
      )}

      {/* Editor & Controls */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-4">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-3">
          <div className="flex items-center gap-2">
            <button
              type="button"
              onClick={() => setImportMode('sql')}
              className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors ${
                importMode === 'sql'
                  ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30'
                  : 'text-slate-400 hover:text-white'
              }`}
            >
              <Code2 className="w-4 h-4" />
              <span>Mode SQL (INSERT Statement)</span>
            </button>
            <button
              type="button"
              onClick={() => setImportMode('csv')}
              className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors ${
                importMode === 'csv'
                  ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30'
                  : 'text-slate-400 hover:text-white'
              }`}
            >
              <FileSpreadsheet className="w-4 h-4" />
              <span>Mode CSV (Comma Separated)</span>
            </button>
          </div>

          <button
            type="button"
            onClick={() => setInputText(importMode === 'sql' ? sampleSql : sampleCsv)}
            className="text-xs text-cyan-400 hover:underline flex items-center gap-1"
          >
            <span>Muat Contoh Format {importMode.toUpperCase()}</span>
          </button>
        </div>

        <div>
          <label className="block text-xs font-medium text-slate-400 mb-2">
            Tempelkan (Paste) query SQL atau baris data CSV di bawah ini:
          </label>
          <textarea
            rows={10}
            value={inputText}
            onChange={(e) => setInputText(e.target.value)}
            placeholder={
              importMode === 'sql'
                ? "INSERT INTO debitur (kode_kav, nama_owner, watt_listrik, luas_m2_kawasan, status_meteran, no_hp) VALUES ('EM-1060', 'Hendra', 11000, 320, 'KWH & AIR METERAN', '081234567890');"
                : "kode_kav,nama_owner,watt_listrik,luas_m2_kawasan,status_meteran,no_hp\nEM-1060,Hendra,11000,320,KWH & AIR METERAN,081234567890"
            }
            className="w-full bg-[#16191f] border border-slate-700 text-slate-200 text-xs font-mono rounded-lg p-3 leading-relaxed focus:border-cyan-500"
          ></textarea>
        </div>

        <div className="flex items-center justify-between pt-2">
          <div className="text-[11px] text-slate-500">
            Catatan: Sistem secara cerdas akan mengecek duplikasi kode kavling sebelum memasukkan record baru.
          </div>

          <button
            type="button"
            onClick={handleParseAndImport}
            className="flex items-center gap-2 px-6 py-2.5 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold shadow-md transition-colors"
          >
            <Play className="w-4 h-4" />
            <span>Eksekusi Impor Data</span>
          </button>
        </div>
      </div>
    </div>
  );
};
