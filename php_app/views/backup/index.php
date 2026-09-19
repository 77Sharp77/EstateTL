<?php
/**
 * Kalicaa Villa Estate Management - Backup & Excel Module
 * Hanya dapat diakses oleh Super Admin
 */

use Helpers\Format;
use Services\BackupService;

$pageTitle = 'Backup & Restore Database (Excel)';
$pageSubtitle = 'Sinkronisasi Multi-Modul Excel antara PHP & React';
$currentPage = 'backup';

$user = $_SESSION['user'] ?? [
    'nama_lengkap' => 'Super Administrator',
    'role' => 'Super Admin',
    'username' => 'admin',
    'avatar_color' => '#06b6d4'
];

// Security Check: Role Super Admin Only
if (($user['role'] ?? '') !== 'Super Admin') {
    http_response_code(403);
    include __DIR__ . '/../layouts/header.php';
    ?>
    <div class="max-w-2xl mx-auto py-16 text-center space-y-4">
      <div class="w-16 h-16 rounded-full bg-rose-500/10 border border-rose-500/30 text-rose-400 flex items-center justify-center mx-auto">
        <i data-lucide="shield-alert" class="w-8 h-8"></i>
      </div>
      <h2 class="text-xl font-bold text-slate-100">Akses Ditolak: Khusus Super Admin</h2>
      <p class="text-sm text-slate-400 max-w-md mx-auto leading-relaxed">
        Modul Backup & Restore Database serta Bulk Export-Import Excel hanya dapat diakses oleh pengguna dengan kewenangan <strong class="text-rose-400">Super Administrator</strong>.
      </p>
      <div class="pt-4 flex justify-center gap-3">
        <a href="?route=dashboard" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-200 transition-colors">
          Kembali ke Dashboard
        </a>
        <a href="?route=role_switch&role=Super+Admin" class="px-4 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-xs font-semibold text-white transition-colors">
          Beralih ke Super Admin (Mode Uji)
        </a>
      </div>
    </div>
    <?php
    include __DIR__ . '/../layouts/footer.php';
    exit;
}

$stats = BackupService::getTableStats($pdo);
$totalRows = array_sum(array_column($stats, 'count'));

include __DIR__ . '/../layouts/header.php';
?>

<!-- Alert Feedback -->
<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="p-4 rounded-xl bg-emerald-950/60 border border-emerald-500/40 text-emerald-300 text-xs flex items-center justify-between">
    <div class="flex items-center gap-2.5">
      <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 text-emerald-400"></i>
      <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
    </div>
    <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">&times;</button>
  </div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="p-4 rounded-xl bg-rose-950/60 border border-rose-500/40 text-rose-300 text-xs flex items-center justify-between">
    <div class="flex items-center gap-2.5">
      <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-rose-400"></i>
      <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
    </div>
    <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-white">&times;</button>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="space-y-6">

  <!-- Header Banner Super Admin -->
  <div class="bg-gradient-to-r from-cyan-950/50 via-[#121418] to-[#121418] border border-cyan-500/30 rounded-2xl p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
    <div class="space-y-1">
      <div class="flex items-center gap-2">
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-500/20 text-cyan-400 border border-cyan-500/40 uppercase tracking-wide flex items-center gap-1">
          <i data-lucide="shield-check" class="w-3 h-3"></i> Hak Akses Super Admin
        </span>
        <span class="text-xs text-slate-500">•</span>
        <span class="text-xs text-slate-400">Database Engine: <strong class="text-slate-200"><?= htmlspecialchars(strtoupper($pdo->getAttribute(PDO::ATTR_DRIVER_NAME))) ?></strong></span>
      </div>
      <h2 class="text-base font-bold text-white tracking-wide">Pusat Backup & Restore Multi-Modul (Excel Multi-Sheet)</h2>
      <p class="text-xs text-slate-400 max-w-2xl leading-relaxed">
        Modul ini mengelola sinkronisasi menyeluruh antara backend PHP/MySQL dengan aplikasi React. Seluruh master data, transaksi billing, voucher RVBW, SPK kontraktor, dan titipan dana dapat diexport & diimport dalam 1 file Excel komprehensif.
      </p>
    </div>

    <!-- Quick Export Button -->
    <div class="flex items-center gap-3 shrink-0">
      <a href="?route=backup_export_excel" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-950/40 transition-all cursor-pointer">
        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
        <span>Download Backup Excel (.xlsx)</span>
      </a>
      <a href="/#/backup" class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-cyan-400 font-semibold text-xs border border-slate-700 transition-colors">
        <i data-lucide="layout" class="w-4 h-4"></i>
        <span>Buka di React</span>
      </a>
    </div>
  </div>

  <!-- Ringkasan Data per Modul (12 Modul) -->
  <div class="space-y-3">
    <div class="flex items-center justify-between">
      <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
        <i data-lucide="database" class="w-3.5 h-3.5 text-cyan-400"></i>
        Cakupan Data Seluruh Modul (Total: <?= number_format($totalRows, 0, ',', '.') ?> Rekaman)
      </h3>
      <span class="text-[11px] text-slate-500">12 Sheet Otomatis di File Excel</span>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
      <?php foreach ($stats as $key => $item): ?>
        <div class="bg-[#121418] border border-slate-800 rounded-xl p-3 flex flex-col justify-between">
          <div class="flex items-start justify-between">
            <span class="text-[11px] font-medium text-slate-400 leading-tight"><?= htmlspecialchars($item['label']) ?></span>
            <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-slate-800/80 text-slate-400 border border-slate-700/50"><?= htmlspecialchars($key) ?></span>
          </div>
          <div class="mt-3 flex items-baseline justify-between">
            <span class="text-lg font-bold font-mono text-slate-100"><?= number_format($item['count'], 0, ',', '.') ?></span>
            <span class="text-[10px] text-emerald-400 font-medium">Aktif</span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Aksi Utama: Export & Import Panel -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Panel 1: Ekspor Excel -->
    <div class="bg-[#121418] border border-slate-800 rounded-2xl p-5 space-y-4 flex flex-col justify-between">
      <div class="space-y-3">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center justify-center">
            <i data-lucide="download" class="w-4 h-4"></i>
          </div>
          <div>
            <h3 class="text-sm font-bold text-slate-100">Ekspor Lengkap ke Excel (.xlsx)</h3>
            <p class="text-[11px] text-slate-400">Unduh seluruh snapshot database dalam format multi-sheet</p>
          </div>
        </div>

        <p class="text-xs text-slate-300 leading-relaxed">
          File Excel yang dihasilkan memuat sheet terpisah untuk setiap modul:
          <strong class="text-emerald-400">DEBITUR</strong>, 
          <strong class="text-emerald-400">SKP_PROPERTIES</strong>, 
          <strong class="text-emerald-400">SPK_RECORDS</strong>, 
          <strong class="text-emerald-400">SPK_PAYMENTS</strong>, 
          <strong class="text-emerald-400">INVOICES</strong>, 
          <strong class="text-emerald-400">INVOICE_ITEMS</strong>, 
          <strong class="text-emerald-400">RVBW</strong>, 
          <strong class="text-emerald-400">RVBW_ITEMS</strong>, 
          <strong class="text-emerald-400">TITIPAN_RVBW</strong>, 
          <strong class="text-emerald-400">TITIPAN_PVBW</strong>, 
          <strong class="text-emerald-400">MASTER_COA</strong>, dan 
          <strong class="text-emerald-400">SETTINGS</strong>.
        </p>

        <div class="p-3 bg-slate-900/50 rounded-lg border border-slate-800/80 text-[11px] text-slate-400 space-y-1">
          <div class="flex items-center justify-between">
            <span>Standar Format:</span>
            <span class="font-semibold text-slate-200">Microsoft Excel XML Workbook</span>
          </div>
          <div class="flex items-center justify-between">
            <span>Kompatibilitas:</span>
            <span class="font-semibold text-slate-200">Excel 2007–365, Google Sheets, LibreOffice</span>
          </div>
          <div class="flex items-center justify-between">
            <span>Keamanan:</span>
            <span class="font-semibold text-cyan-400">Otentikasi Super Admin Terverifikasi</span>
          </div>
        </div>
      </div>

      <a href="?route=backup_export_excel" class="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-colors shadow-lg shadow-emerald-950/50">
        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
        <span>Download File Excel Sekarang</span>
      </a>
    </div>

    <!-- Panel 2: Impor & Restore Excel -->
    <div class="bg-[#121418] border border-slate-800 rounded-2xl p-5 space-y-4 flex flex-col justify-between">
      <div class="space-y-3">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-400 flex items-center justify-center">
            <i data-lucide="upload" class="w-4 h-4"></i>
          </div>
          <div>
            <h3 class="text-sm font-bold text-slate-100">Impor & Restore Data Excel</h3>
            <p class="text-[11px] text-slate-400">Pulihkan atau perbarui data database dari file backup</p>
          </div>
        </div>

        <form method="POST" action="?route=backup_import_excel" enctype="multipart/form-data" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1.5">Pilih File Excel / JSON Backup</label>
            <input type="file" name="backup_file" required accept=".xlsx,.xls,.xml,.json" class="w-full bg-[#1a1d23] border border-slate-700 rounded-xl p-2 text-xs text-slate-300 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-cyan-900/50 file:text-cyan-400 hover:file:bg-cyan-800/50 cursor-pointer">
            <p class="text-[10px] text-slate-500 mt-1">Mendukung format .xlsx, .xls, .xml (Excel Spreadsheet), atau file JSON ekspor sistem.</p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1.5">Mode Pemulihan Data</label>
            <div class="grid grid-cols-2 gap-2">
              <label class="flex items-start gap-2 p-2.5 rounded-lg bg-[#1a1d23] border border-slate-800 cursor-pointer hover:border-slate-700">
                <input type="radio" name="mode" value="replace" checked class="mt-0.5 text-cyan-500 focus:ring-0">
                <div>
                  <span class="block text-xs font-bold text-slate-200">Timpa Bersih</span>
                  <span class="block text-[10px] text-slate-400">Gantikan semua rekaman tabel</span>
                </div>
              </label>
              <label class="flex items-start gap-2 p-2.5 rounded-lg bg-[#1a1d23] border border-slate-800 cursor-pointer hover:border-slate-700">
                <input type="radio" name="mode" value="merge" class="mt-0.5 text-cyan-500 focus:ring-0">
                <div>
                  <span class="block text-xs font-bold text-slate-200">Gabungkan (Merge)</span>
                  <span class="block text-[10px] text-slate-400">Update baris yang cocok, tambahkan yang baru</span>
                </div>
              </label>
            </div>
          </div>

          <button type="submit" onclick="return confirm('PENTING: Apakah Anda yakin ingin memulihkan database dari file ini? Pastikan Anda telah mengunduh backup terkini sebelumnya.')" class="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs transition-colors shadow-lg shadow-amber-950/50 cursor-pointer">
            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
            <span>Jalankan Restore Database</span>
          </button>
        </form>
      </div>
    </div>

  </div>

  <!-- Panel 3: Simulasi Pengujian Role Super Admin -->
  <div class="bg-[#121418] border border-slate-800 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
    <div class="flex items-center gap-2">
      <i data-lucide="user-check" class="w-4 h-4 text-cyan-400 shrink-0"></i>
      <span>Akun Saat Ini: <strong class="text-slate-200"><?= htmlspecialchars($user['nama_lengkap']) ?></strong> (Role: <strong class="text-cyan-400"><?= htmlspecialchars($user['role']) ?></strong>)</span>
    </div>
    <div class="flex items-center gap-2">
      <span class="text-[11px] text-slate-500">Uji Proteksi Hak Akses:</span>
      <a href="?route=role_switch&role=Finance+Admin" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] font-semibold border border-slate-700 transition-colors">
        Simulasi Finance Admin (Ditolak)
      </a>
      <a href="?route=role_switch&role=Super+Admin" class="px-2.5 py-1 rounded-lg bg-cyan-950/80 hover:bg-cyan-900 text-cyan-400 text-[10px] font-semibold border border-cyan-800/60 transition-colors">
        Reset Super Admin (Diterima)
      </a>
    </div>
  </div>

</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
