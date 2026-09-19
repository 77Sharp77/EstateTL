<?php
/**
 * Kalicaa Villa - Modul Titipan Dana (RVBW & PVBW)
 */
use Helpers\Format;

$pageTitle = 'Titipan Dana (RVBW / PVBW)';
$pageSubtitle = 'Penerimaan & Pengembalian Titipan Entitas BWJ & TLLI';
$currentPage = 'titipan';

include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6">
  <div class="bg-[#121418] border border-slate-800/80 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex items-center gap-4 text-xs font-semibold">
      <span class="text-cyan-400">Total Titipan: <?= Format::rupiah($totalTitipanNominal) ?></span>
      <span class="text-emerald-400">Total Dikembalikan: <?= Format::rupiah($totalDikembalikanNominal) ?></span>
      <span class="text-rose-400">Sisa Titipan: <?= Format::rupiah($totalSisaNominal) ?></span>
    </div>

    <button onclick="document.getElementById('modalTambahTitipan').classList.remove('hidden')" class="px-4 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold flex items-center gap-2 transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i>
      <span>Catat Titipan Baru (RVBW/RVTL)</span>
    </button>
  </div>

  <!-- Table Titipan RVBW -->
  <div class="bg-[#121418] border border-slate-800/80 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-[#1a1d23] text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-800">
          <tr>
            <th class="py-3 px-4">No. Voucher</th>
            <th class="py-3 px-4">Entitas</th>
            <th class="py-3 px-4">Tanggal Terima</th>
            <th class="py-3 px-4">Uraian / Keterangan</th>
            <th class="py-3 px-4 text-right">Nominal Titipan</th>
            <th class="py-3 px-4 text-right font-bold text-emerald-400">Telah Dikembalikan</th>
            <th class="py-3 px-4 text-right font-bold text-rose-400">Sisa Titipan</th>
            <th class="py-3 px-4 text-center">Status</th>
            <th class="py-3 px-4 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/60 font-mono">
          <?php foreach ($titipanList as $t): ?>
            <tr class="hover:bg-slate-800/30 transition-colors">
              <td class="py-3 px-4 font-bold text-cyan-400"><?= htmlspecialchars($t['nomor']) ?></td>
              <td class="py-3 px-4 font-sans">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $t['entitas'] === 'BWJ' ? 'bg-cyan-500/10 text-cyan-400' : 'bg-amber-500/10 text-amber-400' ?>">
                  <?= $t['entitas'] ?>
                </span>
              </td>
              <td class="py-3 px-4 text-slate-400"><?= htmlspecialchars($t['tanggal_terima']) ?></td>
              <td class="py-3 px-4 font-sans text-slate-300 max-w-xs truncate"><?= htmlspecialchars($t['keterangan']) ?></td>
              <td class="py-3 px-4 text-right text-slate-200 font-bold"><?= Format::rupiah($t['nominal']) ?></td>
              <td class="py-3 px-4 text-right text-emerald-400 font-bold"><?= Format::rupiah($t['total_dikembalikan']) ?></td>
              <td class="py-3 px-4 text-right text-rose-400 font-bold"><?= Format::rupiah($t['sisa_titipan']) ?></td>
              <td class="py-3 px-4 text-center font-sans">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold <?= $t['status'] === 'Selesai' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400' ?>">
                  <?= strtoupper($t['status']) ?>
                </span>
              </td>
              <td class="py-3 px-4 text-center font-sans">
                <?php if ($t['sisa_titipan'] > 0): ?>
                  <button onclick="openModalKembali(<?= $t['id'] ?>, '<?= htmlspecialchars($t['nomor']) ?>')" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-cyan-400 text-[11px] font-semibold">
                    + Pengembalian
                  </button>
                <?php else: ?>
                  <span class="text-[10px] text-slate-500">Lunas</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Tambah Titipan -->
<div id="modalTambahTitipan" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
  <div class="bg-[#121418] border border-slate-800 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl">
    <div class="p-5 border-b border-slate-800 flex items-center justify-between">
      <h3 class="text-sm font-bold text-slate-100 uppercase tracking-wider">Catat Titipan Dana</h3>
      <button onclick="document.getElementById('modalTambahTitipan').classList.add('hidden')" class="text-slate-400 hover:text-slate-200"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form method="POST" action="?route=titipan_store" class="p-5 space-y-4">
      <div>
        <label class="block text-xs font-semibold text-slate-400 mb-1">Nomor Voucher (RVBW... atau RVTL...)</label>
        <input type="text" name="nomor" required placeholder="RVBW26060001" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono uppercase">
        <span class="text-[10px] text-slate-500 mt-1 block">Prefix RVBW otomatis BWJ, RVTL otomatis TLLI</span>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-400 mb-1">Tanggal Terima</label>
        <input type="date" name="tanggal_terima" required value="<?= date('Y-m-d') ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-400 mb-1">Nominal Titipan (Rp)</label>
        <input type="number" name="nominal" required placeholder="10000000" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-400 mb-1">Keterangan Titipan</label>
        <textarea name="keterangan" rows="2" required placeholder="Titipan jaminan renovasi..." class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200"></textarea>
      </div>
      <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
        <button type="button" onclick="document.getElementById('modalTambahTitipan').classList.add('hidden')" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
        <button type="submit" class="px-5 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold">Simpan Titipan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Pengembalian Titipan -->
<div id="modalPengembalian" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
  <div class="bg-[#121418] border border-slate-800 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl">
    <div class="p-5 border-b border-slate-800 flex items-center justify-between">
      <h3 class="text-sm font-bold text-slate-100 uppercase tracking-wider">Catat Pengembalian (PVBW/PVTL)</h3>
      <button onclick="document.getElementById('modalPengembalian').classList.add('hidden')" class="text-slate-400 hover:text-slate-200"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form method="POST" action="?route=titipan_kembali_store" class="p-5 space-y-4">
      <input type="hidden" id="kembali_rvbw_id" name="rvbw_id" value="">
      <div>
        <label class="block text-xs font-semibold text-slate-400 mb-1">Nomor Voucher Pengembalian</label>
        <input type="text" name="nomor" required placeholder="PVBW26060001" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono uppercase">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-400 mb-1">Tanggal Pengembalian</label>
        <input type="date" name="tanggal_kembali" required value="<?= date('Y-m-d') ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-400 mb-1">Nominal yang Dikembalikan (Rp)</label>
        <input type="number" name="nominal" required placeholder="5000000" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-400 mb-1">Keterangan</label>
        <textarea name="keterangan" rows="2" required placeholder="Pengembalian termin 1 jaminan..." class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200"></textarea>
      </div>
      <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
        <button type="button" onclick="document.getElementById('modalPengembalian').classList.add('hidden')" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
        <button type="submit" class="px-5 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold">Simpan Pengembalian</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openModalKembali(id, nomor) {
    document.getElementById('kembali_rvbw_id').value = id;
    document.getElementById('modalPengembalian').classList.remove('hidden');
  }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
