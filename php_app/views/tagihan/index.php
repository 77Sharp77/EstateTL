<?php
/**
 * Kalicaa Villa - Generator Tagihan Bulanan View
 */
use Helpers\Format;

$pageTitle = 'Generator Tagihan Bulanan';
$pageSubtitle = 'Kalkulasi Otomatis Pemakaian Listrik, Air PDAM, & Kawasan';
$currentPage = 'tagihan';

include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6" id="tagihanApp">
  <!-- Period & Debitur Selector Card -->
  <div class="bg-[#121418] border border-slate-800/80 rounded-xl p-5">
    <h3 class="text-sm font-bold text-slate-200 uppercase tracking-wider mb-4 flex items-center gap-2">
      <i data-lucide="sliders" class="w-4 h-4 text-cyan-400"></i>
      Pilih Periode & Pemilik Villa
    </h3>

    <form method="GET" action="" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <input type="hidden" name="route" value="tagihan">
      
      <div>
        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Bulan Periode</label>
        <select name="bulan" onchange="this.form.submit()" class="w-full bg-[#1a1d23] border border-slate-700 text-slate-200 text-xs rounded-lg px-3 py-2 focus:ring-1 focus:ring-cyan-500">
          <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>" <?= $selectedBulan === $m ? 'selected' : '' ?>>
              <?= date('F', mktime(0, 0, 0, $m, 1)) ?> (<?= $m ?>)
            </option>
          <?php endfor; ?>
        </select>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Tahun Periode</label>
        <select name="tahun" onchange="this.form.submit()" class="w-full bg-[#1a1d23] border border-slate-700 text-slate-200 text-xs rounded-lg px-3 py-2 focus:ring-1 focus:ring-cyan-500 font-mono">
          <?php for ($y = 2018; $y <= 2030; $y++): ?>
            <option value="<?= $y ?>" <?= $selectedTahun === $y ? 'selected' : '' ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Pilih Unit Villa / Debitur</label>
        <select name="debitur_id" onchange="this.form.submit()" class="w-full bg-[#1a1d23] border border-slate-700 text-slate-200 text-xs rounded-lg px-3 py-2 focus:ring-1 focus:ring-cyan-500">
          <option value="">-- Pilih Unit Villa --</option>
          <?php foreach ($debiturs as $deb): ?>
            <option value="<?= $deb['id'] ?>" <?= $selectedDebitur && $selectedDebitur['id'] == $deb['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($deb['kode_kav']) ?> - <?= htmlspecialchars($deb['nama_owner']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>
  </div>

  <?php if ($selectedDebitur): ?>
    <!-- Form Input Meteran & Live Calculation -->
    <form method="POST" action="?route=tagihan_proses" class="space-y-6">
      <input type="hidden" name="debitur_id" value="<?= $selectedDebitur['id'] ?>">
      <input type="hidden" name="periode_bulan" value="<?= $selectedBulan ?>">
      <input type="hidden" name="periode_tahun" value="<?= $selectedTahun ?>">

      <!-- Grid 3 Kolom: Listrik, Air, Kawasan -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        
        <!-- 1. KARTU LISTRIK PLN -->
        <div class="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
              <i data-lucide="zap" class="w-4 h-4 text-amber-400"></i>
              1. Listrik PLN (<?= $selectedDebitur['watt_listrik'] ?> VA)
            </h4>
            <span class="text-[10px] text-slate-500">Tarif Rp <?= number_format($settings['tarif_pln_per_kwh'], 2) ?></span>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-slate-400 mb-1">Meteran Awal</label>
              <input type="number" step="0.01" id="meteran_pln_awal" name="meteran_pln_awal" value="<?= $meteranTerakhir['meteran_pln_akhir'] ?? 0 ?>"
                     class="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 font-mono text-xs rounded-lg p-2 text-right">
              <?php if (!empty($meteranTerakhir['sumber_periode'])): ?>
                <span class="text-[9px] text-cyan-400 mt-0.5 block">Carry over dari <?= $meteranTerakhir['sumber_periode'] ?></span>
              <?php endif; ?>
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-400 mb-1">Meteran Akhir</label>
              <input type="number" step="0.01" id="meteran_pln_akhir" name="meteran_pln_akhir" value="<?= ($meteranTerakhir['meteran_pln_akhir'] ?? 0) + 350 ?>"
                     class="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 font-mono text-xs rounded-lg p-2 text-right">
            </div>
          </div>

          <!-- Rincian Hasil Listrik -->
          <div class="bg-[#1a1d23] rounded-lg p-3 text-[11px] space-y-1.5 border border-slate-800/80">
            <div class="flex justify-between text-slate-400">
              <span>Pemakaian Aktual</span>
              <span id="txt_pln_pemakaian" class="font-mono font-semibold text-slate-200">0 kWh</span>
            </div>
            <div class="flex justify-between text-slate-400">
              <span>Ambang Rek. Minimum</span>
              <span id="txt_pln_ambang" class="font-mono text-slate-300">0 kWh</span>
            </div>
            <div class="flex justify-between text-slate-400">
              <span>Tagihan Dasar</span>
              <span id="txt_pln_dasar" class="font-mono font-semibold text-slate-200">Rp 0</span>
            </div>
            <div class="flex justify-between text-slate-400">
              <span>Biaya Loses (<?= $settings['persen_loses'] ?>%)</span>
              <span id="txt_pln_loses" class="font-mono text-slate-300">Rp 0</span>
            </div>
            <div class="flex justify-between text-slate-400">
              <span>Biaya PPJ (<?= $settings['persen_ppj'] ?>%)</span>
              <span id="txt_pln_ppj" class="font-mono text-slate-300">Rp 0</span>
            </div>
            <div class="flex justify-between text-slate-400">
              <span>Jasa Pengelolaan (<?= $settings['persen_jasa_listrik_air'] ?>%)</span>
              <span id="txt_pln_jasa" class="font-mono text-slate-300">Rp 0</span>
            </div>
            <div class="pt-2 border-t border-slate-700/60 flex justify-between font-bold text-amber-400">
              <span>Total Listrik</span>
              <span id="txt_pln_total" class="font-mono">Rp 0</span>
            </div>
          </div>
        </div>

        <!-- 2. KARTU AIR PDAM -->
        <div class="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
              <i data-lucide="droplets" class="w-4 h-4 text-cyan-400"></i>
              2. Air Bersih / PDAM
            </h4>
            <span class="text-[10px] text-slate-500">Tarif 3-Tiering</span>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-slate-400 mb-1">Meteran Awal</label>
              <input type="number" step="0.01" id="meteran_air_awal" name="meteran_air_awal" value="<?= $meteranTerakhir['meteran_air_akhir'] ?? 0 ?>"
                     class="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 font-mono text-xs rounded-lg p-2 text-right">
              <?php if (!empty($meteranTerakhir['sumber_periode'])): ?>
                <span class="text-[9px] text-cyan-400 mt-0.5 block">Carry over dari <?= $meteranTerakhir['sumber_periode'] ?></span>
              <?php endif; ?>
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-400 mb-1">Meteran Akhir</label>
              <input type="number" step="0.01" id="meteran_air_akhir" name="meteran_air_akhir" value="<?= ($meteranTerakhir['meteran_air_akhir'] ?? 0) + 25 ?>"
                     class="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 font-mono text-xs rounded-lg p-2 text-right">
            </div>
          </div>

          <!-- Rincian Hasil Air -->
          <div class="bg-[#1a1d23] rounded-lg p-3 text-[11px] space-y-1.5 border border-slate-800/80">
            <div class="flex justify-between text-slate-400">
              <span>Pemakaian m³</span>
              <span id="txt_air_pemakaian" class="font-mono font-semibold text-slate-200">0 m³</span>
            </div>
            <div class="flex justify-between text-slate-400">
              <span>Abodemen Bulanan</span>
              <span class="font-mono text-slate-300">Rp <?= number_format($settings['tarif_air_abodemen'], 0, ',', '.') ?></span>
            </div>
            <div class="flex justify-between text-slate-400">
              <span>Tier 1 (0-<?= $settings['tarif_air_tier1_batas'] ?> m³)</span>
              <span id="txt_air_tier1" class="font-mono text-slate-300">Rp 0</span>
            </div>
            <div class="flex justify-between text-slate-400">
              <span>Tier 2 (<?= $settings['tarif_air_tier1_batas'] ?>-<?= $settings['tarif_air_tier2_batas'] ?> m³)</span>
              <span id="txt_air_tier2" class="font-mono text-slate-300">Rp 0</span>
            </div>
            <div class="flex justify-between text-slate-400">
              <span>Tier 3 (> <?= $settings['tarif_air_tier2_batas'] ?> m³)</span>
              <span id="txt_air_tier3" class="font-mono text-slate-300">Rp 0</span>
            </div>
            <div class="flex justify-between text-slate-400">
              <span>Jasa Pengelolaan (<?= $settings['persen_jasa_listrik_air'] ?>%)</span>
              <span id="txt_air_jasa" class="font-mono text-slate-300">Rp 0</span>
            </div>
            <div class="pt-2 border-t border-slate-700/60 flex justify-between font-bold text-cyan-400">
              <span>Total Air</span>
              <span id="txt_air_total" class="font-mono">Rp 0</span>
            </div>
          </div>
        </div>

        <!-- 3. KARTU KAWASAN & FASILITAS FLAT -->
        <div class="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
              <i data-lucide="trees" class="w-4 h-4 text-emerald-400"></i>
              3. Kawasan & Layanan Tetap
            </h4>
            <span class="text-[10px] text-slate-500"><?= $selectedDebitur['luas_m2_kawasan'] ?> m²</span>
          </div>

          <div class="bg-[#1a1d23] rounded-lg p-3 text-[11px] space-y-2 border border-slate-800/80">
            <div class="flex justify-between text-slate-400">
              <span>Kawasan (<?= $selectedDebitur['luas_m2_kawasan'] ?> m² x <?= Format::rupiah($settings['tarif_kawasan_per_m2']) ?> + Fee 4%)</span>
              <span id="txt_kawasan_total" class="font-mono text-slate-200 font-semibold">Rp 0</span>
            </div>
            <div class="flex justify-between text-slate-400">
              <span>Landscape & Taman</span>
              <span class="font-mono text-slate-300"><?= Format::rupiah($selectedDebitur['landscape_flat']) ?></span>
            </div>
            <div class="flex justify-between text-slate-400">
              <span>Perawatan Kolam Renang</span>
              <span class="font-mono text-slate-300"><?= Format::rupiah($selectedDebitur['kolam_flat']) ?></span>
            </div>
            <div class="flex justify-between text-slate-400">
              <span>Internet & Wi-Fi Kawasan</span>
              <span class="font-mono text-slate-300"><?= Format::rupiah($selectedDebitur['internet_flat']) ?></span>
            </div>
            <div class="pt-2 border-t border-slate-700/60 flex justify-between font-bold text-emerald-400">
              <span>Total Kawasan & Layanan</span>
              <span id="txt_fasilitas_total" class="font-mono">Rp 0</span>
            </div>
          </div>

          <!-- GRAND TOTAL ESTIMATE -->
          <div class="p-4 rounded-xl bg-gradient-to-br from-cyan-950/40 to-slate-900 border border-cyan-500/20 text-center space-y-1">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Estimasi Grand Total Tagihan</span>
            <p id="txt_grand_total" class="text-2xl font-bold font-mono text-cyan-300">Rp 0</p>
            <span class="text-[10px] text-slate-500">Termasuk PPN & Jasa Pengelolaan</span>
          </div>
        </div>

      </div>

      <!-- Action Buttons -->
      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
        <button type="submit" name="action_type" value="rvbw" class="px-5 py-2.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition-colors flex items-center gap-2">
          <i data-lucide="file-plus" class="w-4 h-4"></i>
          <span>Simpan Sebagai Draft RVBW</span>
        </button>
        <button type="submit" name="action_type" value="invoice" class="px-5 py-2.5 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold shadow-lg shadow-cyan-600/20 transition-all flex items-center gap-2">
          <i data-lucide="file-check" class="w-4 h-4"></i>
          <span>Terbitkan Invoice Langsung (IVBW)</span>
        </button>
      </div>
    </form>

    <script>
      // Settings & Debitur Data inject to JavaScript for Live Realtime Calculation
      const settings = <?= json_encode($settings) ?>;
      const debitur = <?= json_encode($selectedDebitur) ?>;

      function updateCalculations() {
        const plnAwal = parseFloat(document.getElementById('meteran_pln_awal').value) || 0;
        const plnAkhir = parseFloat(document.getElementById('meteran_pln_akhir').value) || 0;
        const plnWatt = parseFloat(debitur.watt_listrik) || 6600;

        // 1. Listrik calculation
        const pemakaianKwh = Math.max(0, plnAkhir - plnAwal);
        const ambangRm = plnWatt * (parseFloat(settings.faktor_rekening_minimum) || 0.04);
        const rmTerpakai = pemakaianKwh <= ambangRm;
        const tarifPln = parseFloat(settings.tarif_pln_per_kwh) || 1699.53;
        const tagihanDasar = rmTerpakai ? (ambangRm * tarifPln) : (pemakaianKwh * tarifPln);
        const losesPct = rmTerpakai ? 0 : (parseFloat(settings.persen_loses) || 5);
        const biayaLoses = tagihanDasar * (losesPct / 100);
        const ppjPct = parseFloat(settings.persen_ppj) || 5;
        const biayaPpj = (tagihanDasar + biayaLoses) * (ppjPct / 100);
        const subtotalPln = tagihanDasar + biayaLoses + biayaPpj;
        const jasaPlnPct = parseFloat(settings.persen_jasa_listrik_air) || 10;
        const jasaPln = subtotalPln * (jasaPlnPct / 100);
        const totalPln = subtotalPln + jasaPln;

        document.getElementById('txt_pln_pemakaian').textContent = pemakaianKwh.toLocaleString('id-ID') + ' kWh';
        document.getElementById('txt_pln_ambang').textContent = ambangRm.toLocaleString('id-ID') + ' kWh ' + (rmTerpakai ? '(RM Aktif)' : '(Normal)');
        document.getElementById('txt_pln_dasar').textContent = 'Rp ' + Math.round(tagihanDasar).toLocaleString('id-ID');
        document.getElementById('txt_pln_loses').textContent = 'Rp ' + Math.round(biayaLoses).toLocaleString('id-ID');
        document.getElementById('txt_pln_ppj').textContent = 'Rp ' + Math.round(biayaPpj).toLocaleString('id-ID');
        document.getElementById('txt_pln_jasa').textContent = 'Rp ' + Math.round(jasaPln).toLocaleString('id-ID');
        document.getElementById('txt_pln_total').textContent = 'Rp ' + Math.round(totalPln).toLocaleString('id-ID');

        // 2. Air calculation
        const airAwal = parseFloat(document.getElementById('meteran_air_awal').value) || 0;
        const airAkhir = parseFloat(document.getElementById('meteran_air_akhir').value) || 0;
        const pemakaianM3 = Math.max(0, airAkhir - airAwal);
        const b1 = parseFloat(settings.tarif_air_tier1_batas) || 20;
        const b2 = parseFloat(settings.tarif_air_tier2_batas) || 30;

        const vol1 = Math.min(pemakaianM3, b1);
        const vol2 = Math.min(Math.max(pemakaianM3 - b1, 0), b2 - b1);
        const vol3 = Math.max(pemakaianM3 - b2, 0);

        const abodemen = parseFloat(settings.tarif_air_abodemen) || 22000;
        const cost1 = vol1 * (parseFloat(settings.tarif_air_tier1) || 9700);
        const cost2 = vol2 * (parseFloat(settings.tarif_air_tier2) || 10785);
        const cost3 = vol3 * (parseFloat(settings.tarif_air_tier3) || 11445);
        const subtotalAir = abodemen + cost1 + cost2 + cost3;
        const jasaAir = subtotalAir * (jasaPlnPct / 100);
        const totalAir = subtotalAir + jasaAir;

        document.getElementById('txt_air_pemakaian').textContent = pemakaianM3.toLocaleString('id-ID') + ' m³';
        document.getElementById('txt_air_tier1').textContent = 'Rp ' + Math.round(cost1).toLocaleString('id-ID');
        document.getElementById('txt_air_tier2').textContent = 'Rp ' + Math.round(cost2).toLocaleString('id-ID');
        document.getElementById('txt_air_tier3').textContent = 'Rp ' + Math.round(cost3).toLocaleString('id-ID');
        document.getElementById('txt_air_jasa').textContent = 'Rp ' + Math.round(jasaAir).toLocaleString('id-ID');
        document.getElementById('txt_air_total').textContent = 'Rp ' + Math.round(totalAir).toLocaleString('id-ID');

        // 3. Kawasan & Flat
        const luas = parseFloat(debitur.luas_m2_kawasan) || 0;
        const tarifKawasan = parseFloat(settings.tarif_kawasan_per_m2) || 2000;
        const feeKawasanPct = parseFloat(settings.persen_fee_kawasan) || 4;
        const baseKawasan = luas * tarifKawasan;
        const feeKawasan = baseKawasan * (feeKawasanPct / 100);
        const totalKawasanOnly = baseKawasan + feeKawasan;

        const landscape = parseFloat(debitur.landscape_flat) || 0;
        const kolam = parseFloat(debitur.kolam_flat) || 0;
        const internet = parseFloat(debitur.internet_flat) || 0;
        const totalFasilitas = totalKawasanOnly + landscape + kolam + internet;

        document.getElementById('txt_kawasan_total').textContent = 'Rp ' + Math.round(totalKawasanOnly).toLocaleString('id-ID');
        document.getElementById('txt_fasilitas_total').textContent = 'Rp ' + Math.round(totalFasilitas).toLocaleString('id-ID');

        // Grand Total
        const grandTotal = totalPln + totalAir + totalFasilitas;
        document.getElementById('txt_grand_total').textContent = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');
      }

      ['meteran_pln_awal', 'meteran_pln_akhir', 'meteran_air_awal', 'meteran_air_akhir'].forEach(id => {
        document.getElementById(id).addEventListener('input', updateCalculations);
      });
      updateCalculations();
    </script>
  <?php else: ?>
    <div class="bg-[#121418] border border-slate-800/80 rounded-xl p-12 text-center text-slate-500">
      <i data-lucide="building" class="w-12 h-12 mx-auto mb-3 opacity-30 text-cyan-400"></i>
      <p class="text-sm">Silakan pilih salah satu unit villa di atas untuk memulai pembuatan tagihan bulanan.</p>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
