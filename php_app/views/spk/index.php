<?php
/**
 * Kalicaa Villa - SPK (Surat Perintah Kerja) Kontraktor View
 */
use Helpers\Format;

$pageTitle = 'SPK Kontraktor & Pekerjaan';
$pageSubtitle = 'Manajemen SPK, Pajak PPh, & Riwayat Termin Pembayaran';
$currentPage = 'spk';

include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6">
  <div class="bg-[#121418] border border-slate-800/80 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <span class="text-xs text-slate-400">Total <?= count($spkList) ?> Surat Perintah Kerja Terdaftar</span>
    <button onclick="document.getElementById('modalTambahSPK').classList.remove('hidden')" class="px-4 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold flex items-center gap-2 transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i>
      <span>Buat SPK Baru</span>
    </button>
  </div>

  <div class="bg-[#121418] border border-slate-800/80 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-[#1a1d23] text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-800">
          <tr>
            <th class="py-3 px-4">No. SPK</th>
            <th class="py-3 px-4">Kontraktor</th>
            <th class="py-3 px-4">Keterangan Pekerjaan</th>
            <th class="py-3 px-4 text-right">Nilai Kontrak</th>
            <th class="py-3 px-4 text-center">PPh</th>
            <th class="py-3 px-4 text-right font-bold text-emerald-400">Total Dibayar</th>
            <th class="py-3 px-4 text-right font-bold text-rose-400">Sisa Kontrak</th>
            <th class="py-3 px-4 text-center">Status</th>
            <th class="py-3 px-4 text-center">Aksi Bayar</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/60 font-mono">
          <?php foreach ($spkList as $spk): ?>
            <?php 
              $sisaKontrak = max(0, $spk['nominal_kontrak'] - $spk['total_dibayar']);
            ?>
            <tr class="hover:bg-slate-800/30 transition-colors">
              <td class="py-3 px-4 font-bold text-cyan-400"><?= htmlspecialchars($spk['nomor_spk']) ?></td>
              <td class="py-3 px-4 font-sans font-medium text-slate-200"><?= htmlspecialchars($spk['nama_kontraktor']) ?></td>
              <td class="py-3 px-4 font-sans text-slate-400 max-w-xs truncate"><?= htmlspecialchars($spk['keterangan']) ?></td>
              <td class="py-3 px-4 text-right text-slate-300"><?= Format::rupiah($spk['nominal_kontrak']) ?></td>
              <td class="py-3 px-4 text-center font-sans"><?= $spk['pph_persen'] ?>%</td>
              <td class="py-3 px-4 text-right font-bold text-emerald-400"><?= Format::rupiah($spk['total_dibayar']) ?></td>
              <td class="py-3 px-4 text-right font-bold text-rose-400"><?= Format::rupiah($sisaKontrak) ?></td>
              <td class="py-3 px-4 text-center font-sans">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold <?= $sisaKontrak <= 0 ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' ?>">
                  <?= $sisaKontrak <= 0 ? 'SELESAI' : 'BERJALAN' ?>
                </span>
              </td>
              <td class="py-3 px-4 text-center font-sans">
                <button onclick="openModalBayarSPK(<?= $spk['id'] ?>, '<?= htmlspecialchars($spk['nomor_spk']) ?>')" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-cyan-400 text-[11px] font-semibold transition-colors">
                  + Bayar Termin
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Tambah SPK -->
<div id="modalTambahSPK" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
  <div class="bg-[#121418] border border-slate-800 rounded-2xl w-full max-w-xl overflow-hidden shadow-2xl">
    <div class="p-5 border-b border-slate-800 flex items-center justify-between">
      <h3 class="text-sm font-bold text-slate-100 uppercase tracking-wider flex items-center gap-2">
        <i data-lucide="hammer" class="w-4 h-4 text-cyan-400"></i>
        Buat SPK Baru
      </h3>
      <button onclick="document.getElementById('modalTambahSPK').classList.add('hidden')" class="text-slate-400 hover:text-slate-200">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <form method="POST" action="?route=spk_store" class="p-5 space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Nomor SPK *</label>
          <input type="text" name="nomor_spk" required placeholder="SPK/2026/001/MAINT" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Tanggal SPK *</label>
          <input type="date" name="tanggal_spk" required value="<?= date('Y-m-d') ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Nama Kontraktor *</label>
          <input type="text" name="nama_kontraktor" required placeholder="PT Mitra Bangun" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">PIC Pekerjaan *</label>
          <input type="text" name="pic_pekerjaan" required placeholder="Agus Hartono" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Nominal Kontrak (Rp) *</label>
          <input type="number" name="nominal_kontrak" required placeholder="85000000" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1">Tarif PPh (%) *</label>
          <input type="number" step="0.1" name="pph_persen" required value="2.0" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-400 mb-1">Uraian Lingkup Pekerjaan *</label>
        <textarea name="keterangan" rows="3" required placeholder="Perbaikan dak dan waterproofing..." class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200"></textarea>
      </div>

      <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
        <button type="button" onclick="document.getElementById('modalTambahSPK').classList.add('hidden')" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
        <button type="submit" class="px-5 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold">Simpan Data SPK</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Bayar Termin SPK -->
<div id="modalBayarSPK" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
  <div class="bg-[#121418] border border-slate-800 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl">
    <div class="p-5 border-b border-slate-800 flex items-center justify-between">
      <h3 class="text-sm font-bold text-slate-100 uppercase tracking-wider">Catat Termin SPK</h3>
      <button onclick="document.getElementById('modalBayarSPK').classList.add('hidden')" class="text-slate-400 hover:text-slate-200"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form method="POST" action="?route=spk_payment_store" class="p-5 space-y-4">
      <input type="hidden" id="bayar_spk_id" name="spk_id" value="">
      <div>
        <label class="block text-xs font-semibold text-slate-400 mb-1">Tahapan Pembayaran</label>
        <select name="tahapan" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200">
          <option value="DP">Uang Muka (DP)</option>
          <option value="Termin 1">Termin 1</option>
          <option value="Termin 2">Termin 2</option>
          <option value="Termin 3">Termin 3</option>
          <option value="Retensi">Retensi 5%</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-400 mb-1">Nomor Voucher (PVBW/PVTL)</label>
        <input type="text" name="nomor_voucher" required placeholder="PVBW26040001" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono uppercase">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-400 mb-1">Nilai Pembayaran (Rp)</label>
        <input type="number" name="nilai_pembayaran" required placeholder="25000000" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-400 mb-1">Tanggal Pembayaran</label>
        <input type="date" name="tanggal_pembayaran" required value="<?= date('Y-m-d') ?>" class="w-full bg-[#1a1d23] border border-slate-700 rounded-lg p-2.5 text-xs text-slate-200 font-mono">
      </div>
      <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
        <button type="button" onclick="document.getElementById('modalBayarSPK').classList.add('hidden')" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 text-xs font-semibold">Batal</button>
        <button type="submit" class="px-5 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold">Simpan Voucher</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openModalBayarSPK(id, noSpk) {
    document.getElementById('bayar_spk_id').value = id;
    document.getElementById('modalBayarSPK').classList.remove('hidden');
  }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
