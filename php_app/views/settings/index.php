<?php
/**
 * Kalicaa Villa - Settings & Master Tariff View
 */
use Helpers\Format;

$pageTitle = 'Pengaturan Sistem & Master Tarif';
$pageSubtitle = 'Konfigurasi Rekening Bank, Master Tarif PLN, PDAM, & Kawasan';
$currentPage = 'settings';

include __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-4xl space-y-6">
  <form method="POST" action="?route=settings_save" class="space-y-6">
    
    <!-- 1. Identitas Perusahaan & Bank -->
    <div class="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-4">
      <h3 class="text-sm font-bold text-slate-100 uppercase tracking-wider flex items-center gap-2">
        <i data-lucide="building" class="w-4 h-4 text-cyan-400"></i>
        Identitas Perusahaan & Rekening Pembayaran
      </h3>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Nama Perusahaan</label>
          <input type="text" name="nama_perusahaan" value="<?= htmlspecialchars($settings['nama_perusahaan']) ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Sub Judul</label>
          <input type="text" name="sub_nama" value="<?= htmlspecialchars($settings['sub_nama']) ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200">
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-400 mb-1">Alamat Kantor / Resort</label>
        <textarea name="alamat" rows="2" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200"><?= htmlspecialchars($settings['alamat']) ?></textarea>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Nama Bank</label>
          <input type="text" name="nama_bank" value="<?= htmlspecialchars($settings['nama_bank']) ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Nomor Rekening</label>
          <input type="text" name="no_rekening" value="<?= htmlspecialchars($settings['no_rekening']) ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Atas Nama</label>
          <input type="text" name="atas_nama" value="<?= htmlspecialchars($settings['atas_nama']) ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200">
        </div>
      </div>
    </div>

    <!-- 2. Master Tarif Listrik PLN -->
    <div class="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-4">
      <h3 class="text-sm font-bold text-slate-100 uppercase tracking-wider flex items-center gap-2">
        <i data-lucide="zap" class="w-4 h-4 text-amber-400"></i>
        Master Tarif Listrik PLN & Parameter Rekening Minimum
      </h3>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Tarif Listrik per kWh (Rp)</label>
          <input type="number" step="0.01" name="tarif_pln_per_kwh" value="<?= $settings['tarif_pln_per_kwh'] ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Faktor Rekening Minimum</label>
          <input type="number" step="0.0001" name="faktor_rekening_minimum" value="<?= $settings['faktor_rekening_minimum'] ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
          <span class="text-[10px] text-slate-500 mt-1 block">Contoh: 0.04 (40 jam nyala x Daya VA / 1000)</span>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Biaya Loses (%)</label>
          <input type="number" step="0.1" name="persen_loses" value="<?= $settings['persen_loses'] ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
          <span class="text-[10px] text-slate-500 mt-1 block">Hanya jika pemakaian > ambang RM</span>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Pajak Penerangan Jalan / PPJ (%)</label>
          <input type="number" step="0.1" name="persen_ppj" value="<?= $settings['persen_ppj'] ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Jasa Pengelolaan Listrik & Air (%)</label>
          <input type="number" step="0.1" name="persen_jasa_listrik_air" value="<?= $settings['persen_jasa_listrik_air'] ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
      </div>
    </div>

    <!-- 3. Master Tarif Air Bersih / PDAM -->
    <div class="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-4">
      <h3 class="text-sm font-bold text-slate-100 uppercase tracking-wider flex items-center gap-2">
        <i data-lucide="droplets" class="w-4 h-4 text-cyan-400"></i>
        Master Tarif Air PDAM (3-Tier Berjenjang)
      </h3>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Biaya Abodemen Bulanan (Rp)</label>
          <input type="number" name="tarif_air_abodemen" value="<?= $settings['tarif_air_abodemen'] ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Batas Atas Tier 1 (m³)</label>
          <input type="number" name="tarif_air_tier1_batas" value="<?= $settings['tarif_air_tier1_batas'] ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Tarif Tier 1 (0 s.d. Batas 1) (Rp)</label>
          <input type="number" name="tarif_air_tier1" value="<?= $settings['tarif_air_tier1'] ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Batas Atas Tier 2 (m³)</label>
          <input type="number" name="tarif_air_tier2_batas" value="<?= $settings['tarif_air_tier2_batas'] ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Tarif Tier 2 (Rp)</label>
          <input type="number" name="tarif_air_tier2" value="<?= $settings['tarif_air_tier2'] ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Tarif Tier 3 (> Batas 2) (Rp)</label>
          <input type="number" name="tarif_air_tier3" value="<?= $settings['tarif_air_tier3'] ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
      </div>
    </div>

    <!-- 4. Tarif Kawasan -->
    <div class="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-4">
      <h3 class="text-sm font-bold text-slate-100 uppercase tracking-wider flex items-center gap-2">
        <i data-lucide="trees" class="w-4 h-4 text-emerald-400"></i>
        Master Tarif Kawasan (IPL) & PPN
      </h3>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Tarif Kawasan per m² (Rp)</label>
          <input type="number" name="tarif_kawasan_per_m2" value="<?= $settings['tarif_kawasan_per_m2'] ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Biaya Fee Kawasan (%)</label>
          <input type="number" step="0.1" name="persen_fee_kawasan" value="<?= $settings['persen_fee_kawasan'] ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">PPN Standar (%)</label>
          <input type="number" step="0.1" name="ppn_default" value="<?= $settings['ppn_default'] ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
      </div>
    </div>

    <div class="flex items-center justify-end">
      <button type="submit" class="px-6 py-2.5 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold shadow-lg shadow-cyan-600/20 transition-all flex items-center gap-2">
        <i data-lucide="save" class="w-4 h-4"></i>
        <span>Simpan Seluruh Pengaturan</span>
      </button>
    </div>
  </form>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
