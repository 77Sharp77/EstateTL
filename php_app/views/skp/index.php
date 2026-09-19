<?php
/**
 * Kalicaa Villa - SKP (Surat Keterangan Pemesanan / Kontrak Unit) View
 */
use Helpers\Format;

$pageTitle = 'SKP (Penjualan Unit Villa)';
$pageSubtitle = 'Manajemen Kontrak, Uang Muka, & Progres Pelunasan Cicilan';
$currentPage = 'skp';

include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6">
  <div class="bg-[#121418] border border-slate-800/80 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <span class="text-xs text-slate-400">Total <?= count($skpList) ?> Kontrak Penjualan Unit Terdaftar</span>
    <button onclick="document.getElementById('modalTambahSKP').classList.remove('hidden')" class="px-4 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold flex items-center gap-2 transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i>
      <span>Buat SKP Baru</span>
    </button>
  </div>

  <div class="bg-[#121418] border border-slate-800/80 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-[#1a1d23] text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-800">
          <tr>
            <th class="py-3 px-4">No. SKP</th>
            <th class="py-3 px-4">Kavling</th>
            <th class="py-3 px-4">Nama Pembeli</th>
            <th class="py-3 px-4 text-right">Nilai Jual</th>
            <th class="py-3 px-4 text-right">Uang Muka (DP)</th>
            <th class="py-3 px-4 text-center">Tenor</th>
            <th class="py-3 px-4 text-right">Angsuran / Bln</th>
            <th class="py-3 px-4 text-right font-bold text-emerald-400">Total Dibayar</th>
            <th class="py-3 px-4 text-right font-bold text-rose-400">Sisa Cicilan</th>
            <th class="py-3 px-4 text-center">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/60 font-mono">
          <?php foreach ($skpList as $s): ?>
            <tr class="hover:bg-slate-800/30 transition-colors">
              <td class="py-3 px-4 font-bold text-cyan-400"><?= htmlspecialchars($s['nomor_skp']) ?></td>
              <td class="py-3 px-4 font-bold text-slate-200"><?= htmlspecialchars($s['kode_kav']) ?></td>
              <td class="py-3 px-4 font-sans font-medium text-slate-300"><?= htmlspecialchars($s['nama_owner']) ?></td>
              <td class="py-3 px-4 text-right text-slate-300"><?= Format::rupiah($s['nilai_jual']) ?></td>
              <td class="py-3 px-4 text-right text-slate-400"><?= Format::rupiah($s['uang_muka']) ?></td>
              <td class="py-3 px-4 text-center font-sans"><?= $s['tenor_bulan'] ?> Bln</td>
              <td class="py-3 px-4 text-right text-slate-300"><?= Format::rupiah($s['nilai_angsuran_bulanan']) ?></td>
              <td class="py-3 px-4 text-right font-bold text-emerald-400"><?= Format::rupiah($s['total_dibayar']) ?></td>
              <td class="py-3 px-4 text-right font-bold text-rose-400"><?= Format::rupiah($s['sisa_cicilan']) ?></td>
              <td class="py-3 px-4 text-center font-sans">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold <?= $s['status'] === 'Lunas' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-cyan-500/10 text-cyan-400' ?>">
                  <?= strtoupper($s['status']) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Tambah SKP -->
<div id="modalTambahSKP" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
  <div class="bg-[#121418] border border-slate-800 rounded-2xl w-full max-w-xl overflow-hidden shadow-2xl">
    <div class="p-5 border-b border-slate-800 flex items-center justify-between">
      <h3 class="text-sm font-bold text-slate-100 uppercase tracking-wider flex items-center gap-2">
        <i data-lucide="home" class="w-4 h-4 text-cyan-400"></i>
        Buat SKP Baru (Penjualan Unit)
      </h3>
      <button onclick="document.getElementById('modalTambahSKP').classList.add('hidden')" class="text-slate-400 hover:text-slate-200">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <form method="POST" action="?route=skp_store" class="p-5 space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Nomor SKP *</label>
          <input type="text" name="nomor_skp" required placeholder="SKP/2026/EM-1010/001" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Tanggal SKP *</label>
          <input type="date" name="tanggal_skp" required value="<?= date('Y-m-d') ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-400 mb-1">Pilih Debitur / Unit *</label>
        <select name="debitur_id" required class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200">
          <?php foreach ($debiturs as $deb): ?>
            <option value="<?= $deb['id'] ?>"><?= htmlspecialchars($deb['kode_kav']) ?> - <?= htmlspecialchars($deb['nama_owner']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Nilai Jual Unit (Rp) *</label>
          <input type="number" name="nilai_jual" required placeholder="1500000000" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Uang Muka / DP (Rp) *</label>
          <input type="number" name="uang_muka" required placeholder="300000000" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Tenor Cicilan (Bulan) *</label>
          <input type="number" name="tenor_bulan" required value="24" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Mulai Cicilan *</label>
          <input type="date" name="tanggal_mulai_cicilan" required value="<?= date('Y-m-01') ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
        <button type="button" onclick="document.getElementById('modalTambahSKP').classList.add('hidden')" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
        <button type="submit" class="px-5 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold">Simpan Kontrak SKP</button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
