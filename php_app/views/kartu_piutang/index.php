<?php
/**
 * Kalicaa Villa - Kartu Piutang View
 */
use Helpers\Format;

$pageTitle = 'Kartu Piutang Pemilik Villa';
$pageSubtitle = 'Buku Pembantu Piutang & Rekonsiliasi Saldo Berjalan';
$currentPage = 'kartu-piutang';

include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6">
  <!-- Filter & Controls Toolbar -->
  <div class="bg-[#121418] border border-slate-800/80 rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <form method="GET" action="" class="flex flex-wrap items-center gap-3">
      <input type="hidden" name="route" value="kartu-piutang">

      <!-- Search Input -->
      <div class="relative">
        <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3 top-2.5"></i>
        <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Cari nama / kavling..."
               class="bg-[#1a1d23] border border-slate-700 text-slate-200 text-xs rounded-lg pl-9 pr-3 py-2 focus:ring-1 focus:ring-cyan-500 w-48 sm:w-60">
      </div>

      <!-- Filter Kategori -->
      <select name="tipe" onchange="this.form.submit()" class="bg-[#1a1d23] border border-slate-700 text-slate-200 text-xs rounded-lg px-3 py-2">
        <option value="All" <?= ($tipeFilter ?? 'All') === 'All' ? 'selected' : '' ?>>Semua Transaksi</option>
        <option value="Estate" <?= ($tipeFilter ?? '') === 'Estate' ? 'selected' : '' ?>>Estate Management Saja</option>
        <option value="SKP" <?= ($tipeFilter ?? '') === 'SKP' ? 'selected' : '' ?>>Cicilan SKP Unit Saja</option>
      </select>

      <!-- Sort By -->
      <select name="sort" onchange="this.form.submit()" class="bg-[#1a1d23] border border-slate-700 text-slate-200 text-xs rounded-lg px-3 py-2">
        <option value="kavling_asc" <?= in_array($sortField ?? '', ['kavling', 'kavling_asc']) ? 'selected' : '' ?>>Urutkan: Kode Kavling (A-Z)</option>
        <option value="periode_desc" <?= ($sortField ?? '') === 'periode_desc' ? 'selected' : '' ?>>Urutkan: Periode Terbaru (Desc)</option>
        <option value="periode_asc" <?= ($sortField ?? '') === 'periode_asc' ? 'selected' : '' ?>>Urutkan: Periode Terlama (Asc)</option>
        <option value="kavling_desc" <?= ($sortField ?? '') === 'kavling_desc' ? 'selected' : '' ?>>Urutkan: Kode Kavling (Z-A)</option>
        <option value="nama_asc" <?= in_array($sortField ?? '', ['nama', 'nama_asc']) ? 'selected' : '' ?>>Urutkan: Nama Owner (A-Z)</option>
        <option value="saldo_desc" <?= in_array($sortField ?? '', ['saldo', 'saldo_desc']) ? 'selected' : '' ?>>Urutkan: Saldo Terbesar</option>
        <option value="saldo_asc" <?= ($sortField ?? '') === 'saldo_asc' ? 'selected' : '' ?>>Urutkan: Saldo Terkecil</option>
      </select>

      <!-- Checkbox Hide Zero -->
      <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer select-none">
        <input type="checkbox" name="showZero" value="1" <?= !empty($showZero) ? 'checked' : '' ?> onchange="this.form.submit()"
               class="rounded bg-slate-800 border-slate-700 text-cyan-600 focus:ring-0">
        <span>Tampilkan Saldo Nol (Lunas)</span>
      </label>
    </form>

    <div class="flex items-center gap-2">
      <button onclick="window.print()" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold flex items-center gap-2 transition-colors">
        <i data-lucide="printer" class="w-4 h-4"></i>
        <span>Cetak Rekap</span>
      </button>
    </div>
  </div>

  <!-- Summary Cards Container -->
  <?php if (empty($kartuList)): ?>
    <div class="bg-[#121418] border border-slate-800/80 rounded-xl p-12 text-center text-slate-500">
      <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 opacity-30 text-cyan-400"></i>
      <p class="text-sm">Tidak ditemukan data kartu piutang yang sesuai kriteria.</p>
    </div>
  <?php else: ?>
    <div class="space-y-4">
      <?php foreach ($kartuList as $idx => $kartu): ?>
        <div class="bg-[#121418] border border-slate-800/80 rounded-xl overflow-hidden">
          
          <!-- Header Bar Debitur -->
          <div class="p-4 bg-[#16191f] border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 cursor-pointer" onclick="toggleDetails('card-<?= $idx ?>')">
            <div class="flex items-center gap-3">
              <span class="w-8 h-8 rounded-lg bg-cyan-500/10 text-cyan-400 font-mono font-bold text-xs flex items-center justify-center border border-cyan-500/20">
                <?= htmlspecialchars($kartu['kode_kav']) ?>
              </span>
              <div>
                <h4 class="text-sm font-bold text-slate-100 flex items-center gap-2">
                  <?= htmlspecialchars($kartu['nama_owner']) ?>
                  <span class="text-[10px] text-slate-400 font-mono font-normal"><?= htmlspecialchars($kartu['no_hp']) ?></span>
                </h4>
                <p class="text-[11px] text-slate-500 mt-0.5"><?= count($kartu['entri']) ?> mutasi transaksi tercatat</p>
              </div>
            </div>

            <div class="flex items-center gap-6">
              <div class="text-right">
                <span class="text-[10px] text-slate-500 block uppercase">Total Tagihan</span>
                <span class="text-xs font-bold font-mono text-slate-300"><?= Format::rupiah($kartu['total_debit']) ?></span>
              </div>
              <div class="text-right">
                <span class="text-[10px] text-slate-500 block uppercase">Total Dibayar</span>
                <span class="text-xs font-bold font-mono text-emerald-400"><?= Format::rupiah($kartu['total_kredit']) ?></span>
              </div>
              <div class="text-right pl-3 border-l border-slate-800">
                <span class="text-[10px] text-slate-500 block uppercase">Sisa Saldo Piutang</span>
                <span class="text-sm font-bold font-mono <?= $kartu['saldo_akhir'] > 0 ? 'text-rose-400' : 'text-emerald-400' ?>">
                  <?= Format::rupiah($kartu['saldo_akhir']) ?>
                </span>
              </div>
              <i data-lucide="chevron-down" id="icon-card-<?= $idx ?>" class="w-4 h-4 text-slate-400 transition-transform"></i>
            </div>
          </div>

          <!-- Transaction Table (Collapsible) -->
          <div id="card-<?= $idx ?>" class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-[#1a1d23] text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-800">
                <tr>
                  <th class="py-2.5 px-4">Tgl Terbit</th>
                  <th class="py-2.5 px-4">No. Invoice</th>
                  <th class="py-2.5 px-4">Periode</th>
                  <th class="py-2.5 px-4 text-right">Listrik</th>
                  <th class="py-2.5 px-4 text-right">Air</th>
                  <th class="py-2.5 px-4 text-right">Kawasan</th>
                  <th class="py-2.5 px-4 text-right">Lainnya</th>
                  <th class="py-2.5 px-4 text-right font-bold text-slate-300">Debit (Tagihan)</th>
                  <th class="py-2.5 px-4 text-right font-bold text-emerald-400">Kredit (Bayar)</th>
                  <th class="py-2.5 px-4 text-right font-bold text-cyan-400">Saldo Berjalan</th>
                  <th class="py-2.5 px-4 text-center">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/60 font-mono">
                <?php foreach ($kartu['entri'] as $e): ?>
                  <tr class="hover:bg-slate-800/30 transition-colors text-[11px]">
                    <td class="py-2.5 px-4 text-slate-400"><?= htmlspecialchars($e['tanggal']) ?></td>
                    <td class="py-2.5 px-4 font-bold text-cyan-400">
                      <a href="?route=invoice_print&id=<?= $e['id'] ?>" target="_blank" class="hover:underline">
                        <?= htmlspecialchars($e['nomor_invoice']) ?>
                      </a>
                    </td>
                    <td class="py-2.5 px-4 text-slate-300 font-sans"><?= $e['periode_label'] ?></td>
                    <td class="py-2.5 px-4 text-right text-slate-400"><?= Format::rupiah($e['breakdown']['listrik']) ?></td>
                    <td class="py-2.5 px-4 text-right text-slate-400"><?= Format::rupiah($e['breakdown']['air']) ?></td>
                    <td class="py-2.5 px-4 text-right text-slate-400"><?= Format::rupiah($e['breakdown']['kawasan']) ?></td>
                    <td class="py-2.5 px-4 text-right text-slate-400"><?= Format::rupiah($e['breakdown']['lain']) ?></td>
                    <td class="py-2.5 px-4 text-right font-bold text-slate-200"><?= Format::rupiah($e['debit']) ?></td>
                    <td class="py-2.5 px-4 text-right font-bold text-emerald-400"><?= Format::rupiah($e['kredit']) ?></td>
                    <td class="py-2.5 px-4 text-right font-bold <?= $e['saldo'] > 0 ? 'text-rose-400' : 'text-emerald-400' ?>">
                      <?= Format::rupiah($e['saldo']) ?>
                    </td>
                    <td class="py-2.5 px-4 text-center font-sans">
                      <?= Format::badgeStatusBayar($e['status_bayar']) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script>
  function toggleDetails(id) {
    const el = document.getElementById(id);
    const icon = document.getElementById('icon-' + id);
    if (el.classList.contains('hidden')) {
      el.classList.remove('hidden');
      icon.classList.add('rotate-180');
    } else {
      el.classList.add('hidden');
      icon.classList.remove('rotate-180');
    }
  }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
