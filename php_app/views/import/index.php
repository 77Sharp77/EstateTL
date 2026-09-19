<?php
/**
 * Kalicaa Villa Estate Management
 * Halaman UI Import Data Excel/CSV ke Database MySQL dengan Pengaturan Mode SQL (Upsert vs Skip)
 */

use Services\ImportService;

$pageTitle = 'Import Data (Excel/CSV ke MySQL)';
$pageSubtitle = 'Pengaturan Mode SQL: Upsert (Insert & Update) atau Skip Duplikat';
$currentPage = 'import';

include __DIR__ . '/../layouts/header.php';
?>

<!-- SheetJS for Client-Side Excel / CSV Parsing Preview -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<div class="space-y-6 pb-12 max-w-7xl mx-auto">

  <!-- Header Banner -->
  <div class="bg-gradient-to-r from-cyan-950/60 via-[#121418] to-[#121418] border border-cyan-500/30 rounded-2xl p-5 md:p-6 shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
    <div class="space-y-1.5">
      <div class="flex items-center gap-2">
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-500/20 text-cyan-400 border border-cyan-500/40 uppercase tracking-wider flex items-center gap-1">
          <i data-lucide="database" class="w-3 h-3"></i> MySQL & SQLite Engine
        </span>
        <span class="text-xs text-slate-500">•</span>
        <span class="text-xs text-emerald-400 font-medium flex items-center gap-1">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Transaksional (ACID Rollback)
        </span>
      </div>
      <h2 class="text-xl font-bold text-white tracking-tight">
        Import Data Excel / CSV ke Database MySQL
      </h2>
      <p class="text-xs text-slate-400 max-w-2xl leading-relaxed">
        Unggah file Excel (<code class="text-cyan-300">.xlsx</code>, <code class="text-cyan-300">.xls</code>) atau <code class="text-cyan-300">.csv</code>. Pilih strategi eksekusi <strong>Mode SQL</strong> sebelum import dimulai untuk menentukan apakah baris duplikat akan diperbarui (Upsert) atau dilewati (Skip).
      </p>
    </div>

    <div class="flex items-center gap-2 shrink-0">
      <a href="?route=backup" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold border border-slate-700 transition-colors">
        <i data-lucide="archive" class="w-4 h-4"></i>
        <span>Backup & Restore</span>
      </a>
    </div>
  </div>

  <!-- Result Alert / Notification Banner (Muncul Dinamis Setelah Import Selesai) -->
  <div id="resultBanner" class="hidden rounded-2xl border p-5 space-y-4 animate-in fade-in duration-300">
    <div class="flex items-start justify-between gap-3">
      <div class="flex items-center gap-3">
        <div id="resultIconWrapper" class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0">
          <i id="resultIcon" data-lucide="check-circle" class="w-6 h-6"></i>
        </div>
        <div>
          <h3 id="resultTitle" class="text-sm font-bold text-white">Import Selesai</h3>
          <p id="resultMessage" class="text-xs text-slate-300 mt-0.5 leading-relaxed"></p>
        </div>
      </div>
      <button onclick="document.getElementById('resultBanner').classList.add('hidden')" class="text-slate-400 hover:text-white p-1">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>

    <!-- Statistik Rincian Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 pt-2">
      <!-- Inserted Card -->
      <div class="bg-emerald-950/40 border border-emerald-500/30 rounded-xl p-3.5 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-sm">
          +
        </div>
        <div>
          <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Data Baru (INSERT)</p>
          <p id="statInserted" class="text-lg font-mono font-bold text-emerald-400 leading-tight">0</p>
        </div>
      </div>

      <!-- Updated Card -->
      <div class="bg-amber-950/40 border border-amber-500/30 rounded-xl p-3.5 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-sm">
          ↻
        </div>
        <div>
          <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Diperbarui (UPDATE)</p>
          <p id="statUpdated" class="text-lg font-mono font-bold text-amber-400 leading-tight">0</p>
        </div>
      </div>

      <!-- Skipped Card -->
      <div class="bg-blue-950/40 border border-blue-500/30 rounded-xl p-3.5 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold text-sm">
          ⊘
        </div>
        <div>
          <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Dilewati (SKIP)</p>
          <p id="statSkipped" class="text-lg font-mono font-bold text-blue-400 leading-tight">0</p>
        </div>
      </div>

      <!-- Total Rows Card -->
      <div class="bg-slate-900 border border-slate-800 rounded-xl p-3.5 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-slate-800 text-slate-300 flex items-center justify-center font-bold text-sm">
          Σ
        </div>
        <div>
          <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Total Baris File</p>
          <p id="statTotal" class="text-lg font-mono font-bold text-slate-200 leading-tight">0</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Form Import & Pengaturan Mode SQL -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Kolom Kiri: Konfigurasi & Upload (1 Kolom) -->
    <div class="space-y-6 lg:col-span-1">
      
      <!-- Box 1: File Upload -->
      <div class="bg-[#121418] border border-slate-800 rounded-2xl p-5 space-y-4 shadow-lg">
        <div class="flex items-center gap-2.5 pb-2 border-b border-slate-800/80">
          <div class="p-1.5 rounded-lg bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
            <i data-lucide="upload-cloud" class="w-4 h-4"></i>
          </div>
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200">1. Unggah File Excel / CSV</h3>
        </div>

        <div class="space-y-2">
          <div 
            id="dropzone"
            class="relative border-2 border-dashed border-slate-700 hover:border-cyan-500/60 rounded-xl p-5 text-center cursor-pointer transition-colors bg-[#1a1d23]"
          >
            <input 
              type="file" 
              id="fileInput" 
              accept=".xlsx,.xls,.csv" 
              class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
            />
            <div class="flex flex-col items-center gap-2 pointer-events-none">
              <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
                <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
              </div>
              <span id="fileNameLabel" class="text-xs font-semibold text-slate-200">
                Pilih atau seret file ke sini
              </span>
              <span class="text-[10px] text-slate-500">
                Ekstensi yang didukung: .xlsx, .xls, atau .csv
              </span>
            </div>
          </div>

          <!-- Indikator File Aktif -->
          <div id="fileInfoBox" class="hidden bg-cyan-950/30 border border-cyan-500/30 rounded-xl p-3 flex items-center justify-between text-xs">
            <div class="flex items-center gap-2 min-w-0">
              <i data-lucide="check" class="w-4 h-4 text-cyan-400 shrink-0"></i>
              <span id="fileDetails" class="text-cyan-200 font-mono truncate"></span>
            </div>
            <span id="rowCountBadge" class="px-2 py-0.5 rounded bg-cyan-500/20 text-cyan-400 font-bold font-mono text-[11px] shrink-0">
              0 baris
            </span>
          </div>
        </div>
      </div>

      <!-- Box 2: Target Tabel & Kolom Unik -->
      <div class="bg-[#121418] border border-slate-800 rounded-2xl p-5 space-y-4 shadow-lg">
        <div class="flex items-center gap-2.5 pb-2 border-b border-slate-800/80">
          <div class="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
            <i data-lucide="database" class="w-4 h-4"></i>
          </div>
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200">2. Target Database & Kunci Unik</h3>
        </div>

        <div class="space-y-3.5 text-xs">
          <!-- Pilihan Tabel -->
          <div>
            <label class="block font-semibold text-slate-300 mb-1">Tabel Target Database</label>
            <select id="targetTableSelect" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-slate-200 text-xs focus:outline-none focus:border-cyan-500 font-mono">
              <option value="debiturs" data-key="kode_kav">debiturs (Master Debitur / Owner)</option>
              <option value="invoices" data-key="nomor_invoice">invoices (Tagihan Resmi IVBW)</option>
              <option value="skp_properties" data-key="nomor_skp">skp_properties (Penjualan Unit SKP)</option>
              <option value="spk_records" data-key="nomor_spk">spk_records (SPK Kontraktor)</option>
              <option value="master_coa" data-key="kode_coa">master_coa (Bagan Akun Akuntansi)</option>
              <option value="rvbw" data-key="nomor_rvbw">rvbw (Penerimaan Kas RVBW)</option>
              <option value="titipan_rvbw" data-key="nomor_rvbw">titipan_rvbw (Titipan Dana Masuk)</option>
              <option value="generic_data" data-key="kode_unik">generic_data (Tabel Umum / Bebas)</option>
            </select>
          </div>

          <!-- Kolom Unik Identifier -->
          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="block font-semibold text-slate-300">Kolom Unik (Unique Key)</label>
              <span class="text-[10px] text-slate-500">Untuk deteksi data ada/tidak</span>
            </div>
            <div class="relative">
              <input 
                type="text" 
                id="uniqueKeyInput" 
                value="kode_kav" 
                placeholder="misal: kode_kav, nomor_invoice, email, kode_unik"
                class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-slate-200 text-xs focus:outline-none focus:border-cyan-500 font-mono"
              />
              <span class="absolute right-2.5 top-2.5 text-slate-500">
                <i data-lucide="key" class="w-3.5 h-3.5"></i>
              </span>
            </div>
            <p class="text-[10px] text-slate-500 mt-1">
              Nilai kolom ini digunakan untuk mencocokkan record database secara atomik.
            </p>
          </div>
        </div>
      </div>

    </div>

    <!-- Kolom Kanan: Pilihan Mode SQL & Preview Data (2 Kolom) -->
    <div class="space-y-6 lg:col-span-2">

      <!-- Box 3: Pilihan Radio Button / Switch Mode SQL -->
      <div class="bg-[#121418] border border-slate-800 rounded-2xl p-5 space-y-4 shadow-lg">
        <div class="flex items-center justify-between pb-2 border-b border-slate-800/80">
          <div class="flex items-center gap-2.5">
            <div class="p-1.5 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20">
              <i data-lucide="sliders" class="w-4 h-4"></i>
            </div>
            <div>
              <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200">3. Pengaturan "Mode SQL" Sebelum Eksekusi</h3>
              <p class="text-[11px] text-slate-400">Pilih tindakan database ketika mendeteksi rekaman duplikat</p>
            </div>
          </div>
          <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-slate-800 text-slate-300 border border-slate-700">
            Wajib Dipilih
          </span>
        </div>

        <!-- Mode Selection Radio Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
          
          <!-- Mode 1: Upsert (Default) -->
          <label 
            id="labelUpsert"
            class="mode-card relative border-2 border-cyan-500 bg-cyan-950/20 rounded-xl p-4 cursor-pointer transition-all flex flex-col justify-between space-y-3"
          >
            <div class="flex items-start justify-between">
              <div class="flex items-center gap-3">
                <input 
                  type="radio" 
                  name="import_mode" 
                  value="upsert" 
                  checked 
                  class="mt-0.5 text-cyan-500 focus:ring-cyan-500 h-4 w-4 bg-[#1a1d23] border-slate-700"
                />
                <div>
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-white">Mode "Upsert"</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/40">
                      Default & Rekomendasi
                    </span>
                  </div>
                  <p class="text-[11px] text-cyan-300/80 font-mono mt-0.5">
                    INSERT (Baru) & UPDATE (Perbarui)
                  </p>
                </div>
              </div>
            </div>

            <p class="text-xs text-slate-300 leading-relaxed bg-black/30 p-2.5 rounded-lg border border-slate-800">
              Jika data sudah ada berdasarkan kolom unik, data lama <strong>diperbarui (UPDATE)</strong> dengan isi terbaru dari file. Jika belum ada, dimasukkan sebagai <strong>data baru (INSERT)</strong>.
            </p>

            <div class="flex items-center gap-2 text-[10px] text-cyan-400 font-semibold">
              <i data-lucide="check" class="w-3.5 h-3.5"></i>
              <span>Ideal untuk pembaruan berkala master data & status tagihan</span>
            </div>
          </label>

          <!-- Mode 2: Skip Duplikat -->
          <label 
            id="labelSkip"
            class="mode-card relative border-2 border-slate-800 bg-[#14171d] rounded-xl p-4 cursor-pointer transition-all flex flex-col justify-between space-y-3 hover:border-slate-700"
          >
            <div class="flex items-start justify-between">
              <div class="flex items-center gap-3">
                <input 
                  type="radio" 
                  name="import_mode" 
                  value="skip" 
                  class="mt-0.5 text-blue-500 focus:ring-blue-500 h-4 w-4 bg-[#1a1d23] border-slate-700"
                />
                <div>
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-white">Mode "Skip Duplikat"</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-blue-500/20 text-blue-300 border border-blue-500/40">
                      Protektif
                    </span>
                  </div>
                  <p class="text-[11px] text-blue-300/80 font-mono mt-0.5">
                    SKIP (Abaikan Jika Sudah Ada)
                  </p>
                </div>
              </div>
            </div>

            <p class="text-xs text-slate-300 leading-relaxed bg-black/30 p-2.5 rounded-lg border border-slate-800">
              Jika data sudah ada di database, baris tersebut <strong>dilewati (SKIP)</strong>. Data lama di database tetap aman dan tidak akan berubah sama sekali.
            </p>

            <div class="flex items-center gap-2 text-[10px] text-blue-400 font-semibold">
              <i data-lucide="shield" class="w-3.5 h-3.5"></i>
              <span>Cocok untuk menambah data susulan tanpa menimpa histori</span>
            </div>
          </label>

        </div>
      </div>

      <!-- Box 4: Preview Ringkas Data & Tombol Eksekusi -->
      <div class="bg-[#121418] border border-slate-800 rounded-2xl p-5 space-y-4 shadow-lg">
        <div class="flex items-center justify-between pb-2 border-b border-slate-800/80">
          <div class="flex items-center gap-2.5">
            <div class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
              <i data-lucide="eye" class="w-4 h-4"></i>
            </div>
            <div>
              <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200">4. Preview Ringkas Data yang Akan Diimpor</h3>
              <p class="text-[11px] text-slate-400">Pratinjau kolom dan baris pertama sebelum proses eksekusi dijalankan</p>
            </div>
          </div>
          <div id="previewBadge" class="hidden items-center gap-1.5 text-xs font-mono font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 rounded-lg">
            <span id="previewCountText">0 baris</span>
          </div>
        </div>

        <!-- Tabel Preview -->
        <div id="previewContainer" class="min-h-[160px] flex items-center justify-center border border-slate-800/90 rounded-xl bg-[#0e1014] p-4 overflow-x-auto">
          <div id="emptyPreviewNotice" class="text-center py-6 space-y-2 text-slate-500">
            <i data-lucide="table" class="w-8 h-8 mx-auto text-slate-600"></i>
            <p class="text-xs">Belum ada file yang dipilih. Unggah file Excel/CSV di sebelah kiri untuk melihat preview data.</p>
          </div>
          <table id="previewTable" class="hidden w-full text-left text-xs divide-y divide-slate-800 border-collapse">
            <thead id="previewThead" class="bg-slate-900/80 text-slate-400 font-mono text-[11px] uppercase"></thead>
            <tbody id="previewTbody" class="divide-y divide-slate-800/60 font-mono text-slate-300"></tbody>
          </table>
        </div>

        <!-- Action Execution Bar -->
        <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-4">
          <div class="text-xs text-slate-400 flex items-center gap-2">
            <i data-lucide="info" class="w-4 h-4 text-cyan-400 shrink-0"></i>
            <span>Proses dieksekusi secara atomik dalam <strong class="text-slate-200">DB Transaction</strong>. Jika terjadi error sistem, rollback otomatis aktif.</span>
          </div>

          <button 
            id="btnExecuteImport" 
            disabled 
            class="w-full sm:w-auto px-6 py-3 rounded-xl bg-cyan-600 hover:bg-cyan-500 disabled:bg-slate-800 disabled:text-slate-600 disabled:cursor-not-allowed text-white font-bold text-xs shadow-lg shadow-cyan-950/50 flex items-center justify-center gap-2 transition-all cursor-pointer shrink-0"
          >
            <i data-lucide="play" class="w-4 h-4"></i>
            <span id="btnText">Eksekusi Import ke MySQL</span>
          </button>
        </div>

      </div>

    </div>

  </div>

  <!-- Dokumentasi / Kode Backend Referensi PHP & SQL -->
  <div class="bg-[#121418] border border-slate-800 rounded-2xl p-5 space-y-3">
    <div class="flex items-center justify-between border-b border-slate-800/80 pb-3">
      <div class="flex items-center gap-2">
        <i data-lucide="code-2" class="w-4 h-4 text-cyan-400"></i>
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200">
          Arsitektur Backend: PHP 8.2 & PDO Prepared Statements (Bebas SQL Injection)
        </h3>
      </div>
      <button 
        onclick="document.getElementById('codeViewer').classList.toggle('hidden')" 
        class="text-xs text-cyan-400 hover:text-cyan-300 flex items-center gap-1 font-semibold"
      >
        <span>Lihat Source Code PHP Backend</span>
        <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
      </button>
    </div>

    <div id="codeViewer" class="hidden space-y-3 pt-2">
      <p class="text-xs text-slate-400 leading-relaxed">
        Implementasi backend di <code class="text-cyan-300">php_app/src/Services/ImportService.php</code> memvalidasi whitelist kolom dan mengeksekusi logika atomik:
      </p>
      <pre class="bg-black/80 border border-slate-800 p-4 rounded-xl text-[11px] font-mono text-emerald-400 overflow-x-auto leading-relaxed"><code>$importMode = $_POST['import_mode'] ?? 'upsert'; // 'upsert' atau 'skip'
$rowsData   = json_decode($_POST['data_rows'] ?? '[]', true);

$inserted = 0; $updated = 0; $skipped = 0;
$db->beginTransaction();

try {
    foreach ($rowsData as $row) {
        $uniqueCode = trim($row[$uniqueKey] ?? '');
        if (empty($uniqueCode)) { $skipped++; continue; }

        // 1. Cek record ada / tidak
        $checkStmt = $db->prepare("SELECT id FROM {$table} WHERE {$uniqueKey} = :code LIMIT 1");
        $checkStmt->execute([':code' => $uniqueCode]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            if ($importMode === 'skip') {
                $skipped++; // MODE SKIP
            } else {
                // MODE UPSERT: UPDATE RECORD
                $updateStmt = $db->prepare("UPDATE {$table} SET ... WHERE id = :id");
                $updateStmt->execute($updateParams);
                $updated++;
            }
        } else {
            // INSERT SEBAGAI BARU
            $insertStmt = $db->prepare("INSERT INTO {$table} (...) VALUES (...)");
            $insertStmt->execute($insertParams);
            $inserted++;
        }
    }
    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
}</code></pre>
    </div>
  </div>

</div>

<!-- JavaScript Logika UI: Parsing Excel/CSV, Preview, Radio Switch, dan AJAX Backend Request -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  lucide.createIcons();

  let parsedRows = [];
  let detectedColumns = [];

  const fileInput = document.getElementById('fileInput');
  const fileNameLabel = document.getElementById('fileNameLabel');
  const fileInfoBox = document.getElementById('fileInfoBox');
  const fileDetails = document.getElementById('fileDetails');
  const rowCountBadge = document.getElementById('rowCountBadge');
  const targetTableSelect = document.getElementById('targetTableSelect');
  const uniqueKeyInput = document.getElementById('uniqueKeyInput');
  const btnExecuteImport = document.getElementById('btnExecuteImport');
  const btnText = document.getElementById('btnText');

  const emptyPreviewNotice = document.getElementById('emptyPreviewNotice');
  const previewTable = document.getElementById('previewTable');
  const previewThead = document.getElementById('previewThead');
  const previewTbody = document.getElementById('previewTbody');
  const previewBadge = document.getElementById('previewBadge');
  const previewCountText = document.getElementById('previewCountText');

  const resultBanner = document.getElementById('resultBanner');
  const resultIconWrapper = document.getElementById('resultIconWrapper');
  const resultIcon = document.getElementById('resultIcon');
  const resultTitle = document.getElementById('resultTitle');
  const resultMessage = document.getElementById('resultMessage');
  const statInserted = document.getElementById('statInserted');
  const statUpdated = document.getElementById('statUpdated');
  const statSkipped = document.getElementById('statSkipped');
  const statTotal = document.getElementById('statTotal');

  // Switch highlight visual radio cards
  const modeRadios = document.querySelectorAll('input[name="import_mode"]');
  const labelUpsert = document.getElementById('labelUpsert');
  const labelSkip = document.getElementById('labelSkip');

  function updateRadioStyle() {
    const selectedMode = document.querySelector('input[name="import_mode"]:checked')?.value || 'upsert';
    if (selectedMode === 'upsert') {
      labelUpsert.className = 'mode-card relative border-2 border-cyan-500 bg-cyan-950/20 rounded-xl p-4 cursor-pointer transition-all flex flex-col justify-between space-y-3';
      labelSkip.className = 'mode-card relative border-2 border-slate-800 bg-[#14171d] rounded-xl p-4 cursor-pointer transition-all flex flex-col justify-between space-y-3 hover:border-slate-700';
    } else {
      labelSkip.className = 'mode-card relative border-2 border-blue-500 bg-blue-950/20 rounded-xl p-4 cursor-pointer transition-all flex flex-col justify-between space-y-3';
      labelUpsert.className = 'mode-card relative border-2 border-slate-800 bg-[#14171d] rounded-xl p-4 cursor-pointer transition-all flex flex-col justify-between space-y-3 hover:border-slate-700';
    }
  }

  modeRadios.forEach(r => r.addEventListener('change', updateRadioStyle));
  updateRadioStyle();

  // Otomatis ubah Unique Key berdasarkan pilihan tabel
  targetTableSelect.addEventListener('change', (e) => {
    const opt = e.target.options[e.target.selectedIndex];
    const defaultKey = opt.getAttribute('data-key');
    if (defaultKey) {
      uniqueKeyInput.value = defaultKey;
    }
  });

  // Handle File Upload & Parsing
  fileInput.addEventListener('change', async (e) => {
    const file = e.target.files?.[0];
    if (!file) return;

    fileNameLabel.textContent = file.name;
    fileDetails.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
    fileInfoBox.classList.remove('hidden');

    try {
      const data = await file.arrayBuffer();
      const workbook = XLSX.read(data, { type: 'array' });
      const firstSheetName = workbook.SheetNames[0];
      const worksheet = workbook.Sheets[firstSheetName];
      const json = XLSX.utils.sheet_to_json(worksheet, { defval: '' });

      if (!json || json.length === 0) {
        alert('File kosong atau format worksheet tidak memiliki baris data.');
        btnExecuteImport.disabled = true;
        return;
      }

      parsedRows = json;
      detectedColumns = Object.keys(json[0] || {});

      // Update UI row count
      rowCountBadge.textContent = `${parsedRows.length} baris`;
      previewCountText.textContent = `${parsedRows.length} baris terdeteksi`;
      previewBadge.classList.remove('hidden');
      previewBadge.classList.add('flex');

      // Cek apakah uniqueKey saat ini ada di kolom file, jika tidak cocok coba tebak kolom unik
      const currentKey = uniqueKeyInput.value.toLowerCase();
      const matchKey = detectedColumns.find(c => c.toLowerCase() === currentKey);
      if (!matchKey) {
        // Cari kolom yang mirip ID atau kode
        const guessed = detectedColumns.find(c => /kode|nomor|id|email|nik/i.test(c));
        if (guessed) uniqueKeyInput.value = guessed;
      }

      // Render Preview Table (Top 7 rows)
      renderPreview(detectedColumns, parsedRows.slice(0, 7));

      btnExecuteImport.disabled = false;
    } catch (err) {
      console.error(err);
      alert('Gagal memproses file: ' + err.message);
      btnExecuteImport.disabled = true;
    }
  });

  function renderPreview(columns, rows) {
    emptyPreviewNotice.classList.add('hidden');
    previewTable.classList.remove('hidden');

    // Build Header
    previewThead.innerHTML = `
      <tr>
        <th class="p-2.5 text-center border-b border-slate-800 w-12">#</th>
        ${columns.map(c => `<th class="p-2.5 border-b border-slate-800 truncate max-w-[150px]">${escapeHtml(c)}</th>`).join('')}
      </tr>
    `;

    // Build Rows
    previewTbody.innerHTML = rows.map((r, i) => `
      <tr class="hover:bg-slate-900/40 transition-colors">
        <td class="p-2 text-center text-slate-500 font-bold border-b border-slate-800/40">${i + 1}</td>
        ${columns.map(c => `<td class="p-2 truncate max-w-[150px] border-b border-slate-800/40">${escapeHtml(String(r[c] ?? ''))}</td>`).join('')}
      </tr>
    `).join('');

    if (parsedRows.length > 7) {
      previewTbody.innerHTML += `
        <tr>
          <td colspan="${columns.length + 1}" class="p-2 text-center text-slate-500 text-[11px] italic bg-slate-900/20">
            ... dan ${parsedRows.length - 7} baris data lainnya
          </td>
        </tr>
      `;
    }
  }

  function escapeHtml(str) {
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  // Handle Eksekusi Import
  btnExecuteImport.addEventListener('click', async () => {
    if (parsedRows.length === 0) {
      alert('Unggah dan periksa file data terlebih dahulu.');
      return;
    }

    const selectedMode = document.querySelector('input[name="import_mode"]:checked')?.value || 'upsert';
    const targetTable = targetTableSelect.value;
    const uniqueKey = uniqueKeyInput.value.trim();

    if (!uniqueKey) {
      alert('Kolom Unik (Unique Key) wajib diisi.');
      uniqueKeyInput.focus();
      return;
    }

    const confirmText = selectedMode === 'upsert'
      ? `Jalankan import ${parsedRows.length} baris ke tabel '${targetTable}' dengan MODE UPSERT?\n\n• Data baru akan di-INSERT.\n• Data duplikat berdasarkan '${uniqueKey}' akan di-UPDATE.`
      : `Jalankan import ${parsedRows.length} baris ke tabel '${targetTable}' dengan MODE SKIP DUPLIKAT?\n\n• Data baru akan di-INSERT.\n• Data duplikat berdasarkan '${uniqueKey}' akan di-LEWATI (SKIP).`;

    if (!confirm(confirmText)) return;

    btnExecuteImport.disabled = true;
    btnText.textContent = 'Memproses Transaksi MySQL...';

    try {
      const response = await fetch('?route=api_import_data', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          import_mode: selectedMode,
          target_table: targetTable,
          unique_key: uniqueKey,
          data_rows: parsedRows
        })
      });

      const result = await response.json();

      if (result.success) {
        showNotification(true, 'Import Database Berhasil', result.message, result.stats);
      } else {
        showNotification(false, 'Import Database Gagal', result.message || 'Terjadi kesalahan sistem.', result.stats);
      }

    } catch (err) {
      console.error(err);
      showNotification(false, 'Gagal Menghubungi Server', 'Koneksi ke backend PHP bermasalah: ' + err.message, { inserted: 0, updated: 0, skipped: parsedRows.length, total: parsedRows.length });
    } finally {
      btnExecuteImport.disabled = false;
      btnText.textContent = 'Eksekusi Import ke MySQL';
    }
  });

  function showNotification(isSuccess, title, message, stats = {}) {
    resultBanner.classList.remove('hidden');

    if (isSuccess) {
      resultBanner.className = 'rounded-2xl border border-emerald-500/40 bg-emerald-950/20 p-5 space-y-4 animate-in fade-in duration-300';
      resultIconWrapper.className = 'w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0';
      resultIcon.setAttribute('data-lucide', 'check-circle-2');
      resultTitle.className = 'text-sm font-bold text-emerald-300';
    } else {
      resultBanner.className = 'rounded-2xl border border-rose-500/40 bg-rose-950/20 p-5 space-y-4 animate-in fade-in duration-300';
      resultIconWrapper.className = 'w-10 h-10 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center shrink-0';
      resultIcon.setAttribute('data-lucide', 'alert-circle');
      resultTitle.className = 'text-sm font-bold text-rose-300';
    }

    resultTitle.textContent = title;
    resultMessage.textContent = message;

    statInserted.textContent = (stats.inserted || 0).toLocaleString();
    statUpdated.textContent = (stats.updated || 0).toLocaleString();
    statSkipped.textContent = (stats.skipped || 0).toLocaleString();
    statTotal.textContent = (stats.total || parsedRows.length || 0).toLocaleString();

    lucide.createIcons();
    resultBanner.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

});
</script>

<?php
include __DIR__ . '/../layouts/footer.php';
?>
