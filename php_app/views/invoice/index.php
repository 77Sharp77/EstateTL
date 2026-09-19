<?php
/**
 * Kalicaa Villa - Invoice List View
 */
use Helpers\Format;

$pageTitle = 'Daftar Invoice (IVBW)';
$pageSubtitle = 'Manajemen Penagihan, Cetak PDF, & Pengiriman WhatsApp';
$currentPage = 'invoice';

include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6">
  <!-- Top Action & Filter Toolbar -->
  <div class="bg-[#121418] border border-slate-800/80 rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <form method="GET" action="" class="flex flex-wrap items-center gap-3">
      <input type="hidden" name="route" value="invoice">

      <!-- Periode Bulan & Tahun -->
      <select name="bulan" onchange="this.form.submit()" class="bg-[#1a1d23] border border-slate-700 text-slate-200 text-xs rounded-lg px-3 py-2">
        <option value="">Semua Bulan</option>
        <?php for ($m = 1; $m <= 12; $m++): ?>
          <option value="<?= $m ?>" <?= ($filterBulan ?? '') == $m ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
        <?php endfor; ?>
      </select>

      <select name="tahun" onchange="this.form.submit()" class="bg-[#1a1d23] border border-slate-700 text-slate-200 text-xs rounded-lg px-3 py-2 font-mono">
        <option value="">Semua Tahun</option>
        <?php for ($y = 2018; $y <= 2030; $y++): ?>
          <option value="<?= $y ?>" <?= ($filterTahun ?? '') == $y ? 'selected' : '' ?>><?= $y ?></option>
        <?php endfor; ?>
      </select>

      <!-- Status Bayar -->
      <select name="status_bayar" onchange="this.form.submit()" class="bg-[#1a1d23] border border-slate-700 text-slate-200 text-xs rounded-lg px-3 py-2">
        <option value="">Semua Status</option>
        <option value="Belum Bayar" <?= ($filterStatus ?? '') === 'Belum Bayar' ? 'selected' : '' ?>>Belum Bayar</option>
        <option value="Lunas" <?= ($filterStatus ?? '') === 'Lunas' ? 'selected' : '' ?>>Lunas</option>
      </select>
    </form>

    <div>
      <a href="?route=tagihan" class="px-4 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold flex items-center gap-2 transition-colors">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Buat Tagihan Baru</span>
      </a>
    </div>
  </div>

  <!-- Table Invoices -->
  <div class="bg-[#121418] border border-slate-800/80 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-[#1a1d23] text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-800">
          <tr>
            <th class="py-3 px-4">No. Invoice</th>
            <th class="py-3 px-4">Kavling</th>
            <th class="py-3 px-4">Nama Owner</th>
            <th class="py-3 px-4">Periode</th>
            <th class="py-3 px-4 text-right">DPP</th>
            <th class="py-3 px-4 text-right">PPN</th>
            <th class="py-3 px-4 text-right font-bold text-slate-200">Grand Total</th>
            <th class="py-3 px-4 text-center">Status</th>
            <th class="py-3 px-4 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/60">
          <?php if (empty($invoices)): ?>
            <tr>
              <td colspan="9" class="text-center py-10 text-slate-500">
                <i data-lucide="file-x" class="w-8 h-8 mx-auto mb-2 opacity-30"></i>
                Belum ada invoice yang terdaftar.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($invoices as $inv): ?>
              <?php
                $cleanHp = preg_replace('/[^0-9]/', '', $inv['no_hp']);
                if (str_starts_with($cleanHp, '0')) $cleanHp = '62' . substr($cleanHp, 1);
                $waText = urlencode("Yth. Bapak/Ibu {$inv['nama_debitur']} ({$inv['kode_kav']}),\n\nBerikut terlampir Invoice Tagihan {$inv['nomor_invoice']} periode {$inv['periode_bulan']}/{$inv['periode_tahun']} dengan total " . Format::rupiah($inv['grand_total']) . ".\n\nPembayaran dapat ditransfer ke BCA 1234567890 a.n. PT Kalicaa Management.\n\nTerima kasih.");
                $waUrl = "https://wa.me/{$cleanHp}?text={$waText}";
              ?>
              <tr class="hover:bg-slate-800/30 transition-colors">
                <td class="py-3 px-4 font-mono font-bold text-cyan-400">
                  <?= htmlspecialchars($inv['nomor_invoice']) ?>
                </td>
                <td class="py-3 px-4 font-bold text-slate-200"><?= htmlspecialchars($inv['kode_kav']) ?></td>
                <td class="py-3 px-4 font-medium text-slate-300"><?= htmlspecialchars($inv['nama_debitur']) ?></td>
                <td class="py-3 px-4 text-slate-400"><?= date('F Y', mktime(0, 0, 0, (int)$inv['periode_bulan'], 1, (int)$inv['periode_tahun'])) ?></td>
                <td class="py-3 px-4 text-right font-mono text-slate-400"><?= Format::rupiah($inv['subtotal_dpp']) ?></td>
                <td class="py-3 px-4 text-right font-mono text-slate-400"><?= Format::rupiah($inv['total_ppn']) ?></td>
                <td class="py-3 px-4 text-right font-mono font-bold text-slate-100"><?= Format::rupiah($inv['grand_total']) ?></td>
                <td class="py-3 px-4 text-center">
                  <a href="?route=invoice_toggle_status&id=<?= $inv['id'] ?>" class="hover:opacity-80 transition-opacity" title="Klik untuk mengubah status bayar">
                    <?= Format::badgeStatusBayar($inv['status_bayar']) ?>
                  </a>
                </td>
                <td class="py-3 px-4 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <!-- Cetak Invoice A4 -->
                    <a href="?route=invoice_print&id=<?= $inv['id'] ?>" target="_blank" class="p-1.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors" title="Cetak Invoice PDF/A4">
                      <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    </a>
                    <!-- Cetak Lampiran Meteran -->
                    <a href="?route=invoice_lampiran&id=<?= $inv['id'] ?>" target="_blank" class="p-1.5 rounded bg-cyan-950/60 hover:bg-cyan-900 text-cyan-400 transition-colors" title="Cetak Lampiran Perhitungan">
                      <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                    </a>
                    <!-- Kirim WhatsApp -->
                    <a href="<?= $waUrl ?>" target="_blank" class="p-1.5 rounded bg-emerald-950/60 hover:bg-emerald-900 text-emerald-400 transition-colors" title="Kirim via WhatsApp">
                      <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                    </a>
                  </div>
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
