<?php
/**
 * Kalicaa Villa - RVBW List View
 */
use Helpers\Format;

$pageTitle = 'Penerimaan Piutang (RVBW)';
$pageSubtitle = 'Voucher Penerimaan Kas/Bank & Posting Status Pelunasan';
$currentPage = 'rvbw';

include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6">
  <div class="bg-[#121418] border border-slate-800/80 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="text-xs text-slate-400">
      <span>Daftar Voucher RVBW Periode 2018 s.d. 2030</span>
    </div>
    <div class="flex items-center gap-2">
      <a href="?route=tagihan" class="px-4 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold flex items-center gap-2 transition-colors">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Buat RVBW dari Tagihan</span>
      </a>
    </div>
  </div>

  <div class="bg-[#121418] border border-slate-800/80 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-[#1a1d23] text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-800">
          <tr>
            <th class="py-3 px-4">No. RVBW</th>
            <th class="py-3 px-4">Tanggal</th>
            <th class="py-3 px-4">Kavling</th>
            <th class="py-3 px-4">Nama Debitur</th>
            <th class="py-3 px-4">Periode</th>
            <th class="py-3 px-4 text-right">Subtotal DPP</th>
            <th class="py-3 px-4 text-right">Total PPN</th>
            <th class="py-3 px-4 text-right font-bold text-slate-200">Grand Total</th>
            <th class="py-3 px-4 text-center">Status</th>
            <th class="py-3 px-4 text-center">Aksi Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/60 font-mono">
          <?php if (empty($rvbwList)): ?>
            <tr>
              <td colspan="10" class="text-center py-10 text-slate-500 font-sans">
                <i data-lucide="receipt" class="w-8 h-8 mx-auto mb-2 opacity-30"></i>
                Belum ada voucher RVBW yang tercatat.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($rvbwList as $rv): ?>
              <tr class="hover:bg-slate-800/30 transition-colors">
                <td class="py-3 px-4 font-bold text-cyan-400"><?= htmlspecialchars($rv['nomor_rvbw']) ?></td>
                <td class="py-3 px-4 text-slate-400"><?= htmlspecialchars($rv['tanggal']) ?></td>
                <td class="py-3 px-4 font-bold text-slate-200"><?= htmlspecialchars($rv['kode_kav']) ?></td>
                <td class="py-3 px-4 font-sans font-medium text-slate-300"><?= htmlspecialchars($rv['nama_debitur']) ?></td>
                <td class="py-3 px-4 font-sans text-slate-400"><?= date('F Y', mktime(0, 0, 0, (int)$rv['periode_bulan'], 1, (int)$rv['periode_tahun'])) ?></td>
                <td class="py-3 px-4 text-right text-slate-400"><?= Format::rupiah($rv['subtotal_dpp']) ?></td>
                <td class="py-3 px-4 text-right text-slate-400"><?= Format::rupiah($rv['total_ppn']) ?></td>
                <td class="py-3 px-4 text-right font-bold text-slate-100"><?= Format::rupiah($rv['grand_total']) ?></td>
                <td class="py-3 px-4 text-center font-sans"><?= Format::badgeStatusRVBW($rv['status']) ?></td>
                <td class="py-3 px-4 text-center font-sans">
                  <?php if ($rv['status'] === 'Draft'): ?>
                    <a href="?route=rvbw_status&id=<?= $rv['id'] ?>&status=Checking" class="px-2.5 py-1 rounded bg-amber-500/20 text-amber-300 text-[10px] font-semibold hover:bg-amber-500/30">Set Checking</a>
                  <?php elseif ($rv['status'] === 'Checking'): ?>
                    <a href="?route=rvbw_status&id=<?= $rv['id'] ?>&status=Posted" class="px-2.5 py-1 rounded bg-emerald-500/20 text-emerald-300 text-[10px] font-semibold hover:bg-emerald-500/30">Post Voucher</a>
                  <?php else: ?>
                    <span class="text-[10px] text-slate-500">Terkunci</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
