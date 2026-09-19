<?php
/**
 * Kalicaa Villa - Dashboard View
 */
use Helpers\Format;

$pageTitle = 'Dashboard';
$pageSubtitle = 'Ringkasan Eksekutif & Monitoring Piutang';
$currentPage = 'dashboard';

include __DIR__ . '/../layouts/header.php';
?>

<!-- KPI Stat Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
  <div class="bg-[#121418] border border-slate-800/80 rounded-xl p-4 flex flex-col justify-between">
    <div class="flex items-center justify-between text-slate-400 mb-2">
      <span class="text-xs font-semibold uppercase tracking-wider">Total Piutang Belum Bayar</span>
      <div class="w-7 h-7 rounded-lg bg-rose-500/10 text-rose-400 flex items-center justify-center">
        <i data-lucide="alert-circle" class="w-4 h-4"></i>
      </div>
    </div>
    <div>
      <p class="text-2xl font-bold font-mono text-rose-400"><?= Format::rupiah($stats['total_piutang']) ?></p>
      <p class="text-[11px] text-slate-500 mt-1"><?= count($stats['daftar_tunggakan']) ?> invoice menunggak</p>
    </div>
  </div>

  <div class="bg-[#121418] border border-slate-800/80 rounded-xl p-4 flex flex-col justify-between">
    <div class="flex items-center justify-between text-slate-400 mb-2">
      <span class="text-xs font-semibold uppercase tracking-wider">Penerimaan Bulan Ini</span>
      <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
      </div>
    </div>
    <div>
      <p class="text-2xl font-bold font-mono text-emerald-400"><?= Format::rupiah($stats['total_penerimaan_bulan_ini']) ?></p>
      <p class="text-[11px] text-slate-500 mt-1">Periode <?= date('F Y') ?></p>
    </div>
  </div>

  <div class="bg-[#121418] border border-slate-800/80 rounded-xl p-4 flex flex-col justify-between">
    <div class="flex items-center justify-between text-slate-400 mb-2">
      <span class="text-xs font-semibold uppercase tracking-wider">Total Unit Villa Aktif</span>
      <div class="w-7 h-7 rounded-lg bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
        <i data-lucide="home" class="w-4 h-4"></i>
      </div>
    </div>
    <div>
      <p class="text-2xl font-bold font-mono text-cyan-400"><?= $stats['total_unit_aktif'] ?></p>
      <p class="text-[11px] text-slate-500 mt-1">Dari total <?= $stats['total_unit'] ?> kavling terdaftar</p>
    </div>
  </div>

  <div class="bg-[#121418] border border-slate-800/80 rounded-xl p-4 flex flex-col justify-between">
    <div class="flex items-center justify-between text-slate-400 mb-2">
      <span class="text-xs font-semibold uppercase tracking-wider">Collection Rate</span>
      <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
        <i data-lucide="percent" class="w-4 h-4"></i>
      </div>
    </div>
    <div>
      <p class="text-2xl font-bold font-mono text-amber-400"><?= number_format($stats['collection_rate'], 1) ?>%</p>
      <p class="text-[11px] text-slate-500 mt-1">Rasio tertagih sepanjang masa</p>
    </div>
  </div>
</div>

<!-- 6-Month Trend Chart -->
<div class="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-4">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
    <div>
      <h3 class="text-sm font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
        <i data-lucide="bar-chart-3" class="w-4 h-4 text-cyan-400"></i>
        Tren Pembayaran Tagihan Bulanan (6 Bulan Terakhir)
      </h3>
      <p class="text-xs text-slate-500 mt-0.5">Perbandingan nominal terbayar (lunas) vs belum terbayar per periode</p>
    </div>
    <div class="flex items-center gap-4 text-xs font-semibold">
      <div class="flex items-center gap-1.5">
        <span class="w-3 h-3 rounded bg-emerald-500"></span>
        <span class="text-slate-300">Terbayar</span>
      </div>
      <div class="flex items-center gap-1.5">
        <span class="w-3 h-3 rounded bg-rose-500"></span>
        <span class="text-slate-300">Belum Bayar</span>
      </div>
    </div>
  </div>

  <div class="h-64 w-full">
    <canvas id="trendChart"></canvas>
  </div>
</div>

<!-- Monitoring Tunggakan & WhatsApp Reminder Table -->
<div class="bg-[#121418] border border-slate-800/80 rounded-xl overflow-hidden">
  <div class="p-4 border-b border-slate-800/80 flex items-center justify-between">
    <div>
      <h3 class="text-sm font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
        <i data-lucide="bell-ring" class="w-4 h-4 text-amber-400"></i>
        Monitoring Tunggakan & Reminder Tagihan
      </h3>
      <p class="text-xs text-slate-500 mt-0.5">Daftar invoice yang belum dilunasi oleh pemilik villa</p>
    </div>
    <span class="px-2.5 py-1 rounded text-xs font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
      <?= count($stats['daftar_tunggakan']) ?> Tunggakan
    </span>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
      <thead class="bg-[#1a1d23] text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-800">
        <tr>
          <th class="py-3 px-4">No. Invoice</th>
          <th class="py-3 px-4">Kavling</th>
          <th class="py-3 px-4">Nama Pemilik</th>
          <th class="py-3 px-4">Periode</th>
          <th class="py-3 px-4 text-right">Nominal Tagihan</th>
          <th class="py-3 px-4 text-center">Status</th>
          <th class="py-3 px-4 text-center">Aksi Reminder</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-800/60">
        <?php if (empty($stats['daftar_tunggakan'])): ?>
          <tr>
            <td colspan="7" class="text-center py-8 text-slate-500">
              <i data-lucide="check-circle" class="w-8 h-8 mx-auto mb-2 opacity-30 text-emerald-400"></i>
              Tidak ada tunggakan saat ini. Semua tagihan telah lunas!
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($stats['daftar_tunggakan'] as $row): ?>
            <?php
              $waText = urlencode("Yth. Bapak/Ibu {$row['nama_debitur']} ({$row['kode_kav']}),\n\nKami menginformasikan bahwa Tagihan Estate Management {$row['nomor_invoice']} untuk periode {$row['periode_bulan']}/{$row['periode_tahun']} sebesar " . Format::rupiah($row['grand_total']) . " belum tercatat pembayarannya.\n\nPembayaran dapat ditransfer ke rekening:\nBCA 1234567890 a.n. PT Kalicaa Management.\n\nTerima kasih.");
              $cleanHp = preg_replace('/[^0-9]/', '', $row['no_hp']);
              if (str_starts_with($cleanHp, '0')) $cleanHp = '62' . substr($cleanHp, 1);
              $waUrl = "https://wa.me/{$cleanHp}?text={$waText}";
            ?>
            <tr class="hover:bg-slate-800/30 transition-colors">
              <td class="py-3 px-4 font-mono font-medium text-cyan-400"><?= htmlspecialchars($row['nomor_invoice']) ?></td>
              <td class="py-3 px-4 font-bold text-slate-200"><?= htmlspecialchars($row['kode_kav']) ?></td>
              <td class="py-3 px-4 font-medium text-slate-300"><?= htmlspecialchars($row['nama_debitur']) ?></td>
              <td class="py-3 px-4 text-slate-400"><?= date('F Y', mktime(0, 0, 0, (int)$row['periode_bulan'], 1, (int)$row['periode_tahun'])) ?></td>
              <td class="py-3 px-4 text-right font-mono font-bold text-rose-400"><?= Format::rupiah($row['grand_total']) ?></td>
              <td class="py-3 px-4 text-center"><?= Format::badgeStatusBayar($row['status_bayar']) ?></td>
              <td class="py-3 px-4 text-center">
                <a href="<?= $waUrl ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 rounded bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-semibold transition-colors">
                  <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                  <span>Kirim WA</span>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  // Setup Chart.js for 6-Month Trend
  const ctx = document.getElementById('trendChart').getContext('2d');
  const chartData = <?= json_encode($stats['chart_data']) ?>;

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: chartData.map(d => d.label),
      datasets: [
        {
          label: 'Terbayar (Lunas)',
          data: chartData.map(d => d.lunas),
          backgroundColor: '#10b981',
          borderRadius: 4,
        },
        {
          label: 'Belum Terbayar',
          data: chartData.map(d => d.belum_bayar),
          backgroundColor: '#f43f5e',
          borderRadius: 4,
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        x: {
          grid: { color: 'rgba(51, 65, 85, 0.2)' },
          ticks: { color: '#94a3b8', font: { family: 'Inter', size: 11 } }
        },
        y: {
          grid: { color: 'rgba(51, 65, 85, 0.2)' },
          ticks: {
            color: '#94a3b8',
            font: { family: 'JetBrains Mono', size: 10 },
            callback: function(value) {
              return 'Rp ' + (value / 1000000).toFixed(0) + ' Jt';
            }
          }
        }
      }
    }
  });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
