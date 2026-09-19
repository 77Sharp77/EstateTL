<?php
/**
 * Kalicaa Villa - Master Debitur View
 */
use Helpers\Format;

$pageTitle = 'Master Debitur (Pemilik Villa)';
$pageSubtitle = 'Pengelolaan Kavling, Data Teknis Listrik & Luas Kawasan';
$currentPage = 'debitur';

include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6">
  <!-- Top Toolbar -->
  <div class="bg-[#121418] border border-slate-800/80 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex items-center gap-2">
      <span class="text-xs text-slate-400">Total <?= count($debiturs) ?> Unit Kavling Terdaftar</span>
    </div>

    <div class="flex items-center gap-2">
      <button onclick="document.getElementById('modalTambahDebitur').classList.remove('hidden')" class="px-4 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold flex items-center gap-2 transition-colors">
        <i data-lucide="user-plus" class="w-4 h-4"></i>
        <span>Tambah Debitur Baru</span>
      </button>
    </div>
  </div>

  <!-- Table Debitur -->
  <div class="bg-[#121418] border border-slate-800/80 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-[#1a1d23] text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-800">
          <tr>
            <th class="py-3 px-4">Kavling</th>
            <th class="py-3 px-4">Nama Owner</th>
            <th class="py-3 px-4">No. HP (WA)</th>
            <th class="py-3 px-4">Tipe Unit</th>
            <th class="py-3 px-4 text-right">Daya (Watt)</th>
            <th class="py-3 px-4 text-right">Luas (m²)</th>
            <th class="py-3 px-4 text-right">Landscape</th>
            <th class="py-3 px-4 text-right">Kolam</th>
            <th class="py-3 px-4 text-right">Internet</th>
            <th class="py-3 px-4 text-center">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/60 font-mono">
          <?php foreach ($debiturs as $deb): ?>
            <tr class="hover:bg-slate-800/30 transition-colors">
              <td class="py-3 px-4 font-bold text-cyan-400"><?= htmlspecialchars($deb['kode_kav']) ?></td>
              <td class="py-3 px-4 font-sans font-medium text-slate-200"><?= htmlspecialchars($deb['nama_owner']) ?></td>
              <td class="py-3 px-4 text-slate-400"><?= htmlspecialchars($deb['no_hp']) ?></td>
              <td class="py-3 px-4 font-sans text-slate-400"><?= htmlspecialchars($deb['tipe_unit']) ?></td>
              <td class="py-3 px-4 text-right text-slate-300"><?= number_format($deb['watt_listrik'], 0, ',', '.') ?> VA</td>
              <td class="py-3 px-4 text-right text-slate-300"><?= number_format($deb['luas_m2_kawasan'], 1, ',', '.') ?> m²</td>
              <td class="py-3 px-4 text-right text-slate-400"><?= Format::rupiah($deb['landscape_flat']) ?></td>
              <td class="py-3 px-4 text-right text-slate-400"><?= Format::rupiah($deb['kolam_flat']) ?></td>
              <td class="py-3 px-4 text-right text-slate-400"><?= Format::rupiah($deb['internet_flat']) ?></td>
              <td class="py-3 px-4 text-center font-sans">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold <?= $deb['status'] === 'aktif' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-700/50 text-slate-400' ?>">
                  <?= strtoupper($deb['status']) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Tambah Debitur -->
<div id="modalTambahDebitur" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
  <div class="bg-[#121418] border border-slate-800 rounded-2xl w-full max-w-xl overflow-hidden shadow-2xl">
    <div class="p-5 border-b border-slate-800 flex items-center justify-between">
      <h3 class="text-sm font-bold text-slate-100 uppercase tracking-wider flex items-center gap-2">
        <i data-lucide="user-plus" class="w-4 h-4 text-cyan-400"></i>
        Tambah Debitur / Pemilik Villa
      </h3>
      <button onclick="document.getElementById('modalTambahDebitur').classList.add('hidden')" class="text-slate-400 hover:text-slate-200">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <form method="POST" action="?route=debitur_store" class="p-5 space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Kode Kavling *</label>
          <input type="text" name="kode_kav" required placeholder="EM-1010" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 uppercase font-mono">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Nama Pemilik *</label>
          <input type="text" name="nama_owner" required placeholder="Budi Santoso" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">No. HP (WhatsApp) *</label>
          <input type="text" name="no_hp" required placeholder="6281234567890" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Tipe Unit *</label>
          <input type="text" name="tipe_unit" required placeholder="Villa Type A" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Daya Listrik (VA) *</label>
          <input type="number" name="watt_listrik" required value="6600" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Luas m² Kawasan *</label>
          <input type="number" step="0.1" name="luas_m2_kawasan" required value="350" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
      </div>

      <div class="grid grid-cols-3 gap-3">
        <div>
          <label class="block text-[11px] font-semibold text-slate-400 mb-1">Biaya Landscape</label>
          <input type="number" name="landscape_flat" value="0" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2 text-xs text-slate-200 font-mono">
        </div>
        <div>
          <label class="block text-[11px] font-semibold text-slate-400 mb-1">Biaya Kolam</label>
          <input type="number" name="kolam_flat" value="0" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2 text-xs text-slate-200 font-mono">
        </div>
        <div>
          <label class="block text-[11px] font-semibold text-slate-400 mb-1">Biaya Internet</label>
          <input type="number" name="internet_flat" value="450000" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2 text-xs text-slate-200 font-mono">
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-400 mb-1">Alamat Lengkap Unit</label>
        <textarea name="alamat_unit" rows="2" placeholder="Private Residential Kalicaa Villa R-14 B" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200"></textarea>
      </div>

      <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
        <button type="button" onclick="document.getElementById('modalTambahDebitur').classList.add('hidden')" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
        <button type="submit" class="px-5 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold">Simpan Data Debitur</button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
