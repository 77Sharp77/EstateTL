<?php
/**
 * Kalicaa Villa - Official Lampiran Perhitungan Meteran & Utility
 */
use Helpers\Format;
use Services\CalculationService;

// Hitung ulang rincian detail berdasarkan data meteran
$itemListrik = null;
$itemAir = null;
$itemKawasan = null;

foreach ($items as $it) {
    if ($it['tagihan_bulanan_kategori'] === 'LISTRIK' || stripos($it['keterangan'], 'Listrik') !== false) {
        $itemListrik = $it;
    } elseif ($it['tagihan_bulanan_kategori'] === 'AIR' || stripos($it['keterangan'], 'Air') !== false) {
        $itemAir = $it;
    } elseif ($it['tagihan_bulanan_kategori'] === 'KAWASAN' || stripos($it['keterangan'], 'Kawasan') !== false) {
        $itemKawasan = $it;
    }
}

$kalkulasiListrik = null;
if ($itemListrik) {
    $kalkulasiListrik = CalculationService::hitungTagihanListrik(
        (float)($itemListrik['meteran_pln_awal'] ?? 0),
        (float)($itemListrik['meteran_pln_akhir'] ?? 0),
        (float)($debitur['watt_listrik'] ?? 6600),
        $settings
    );
}

$kalkulasiAir = null;
if ($itemAir) {
    $kalkulasiAir = CalculationService::hitungTagihanAir(
        (float)($itemAir['meteran_air_awal'] ?? 0),
        (float)($itemAir['meteran_air_akhir'] ?? 0),
        $settings
    );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Lampiran Perhitungan Tagihan - <?= htmlspecialchars($invoice['nomor_invoice']) ?></title>
  <style>
    @page { size: A4 portrait; margin: 15mm; }
    body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #111; line-height: 1.4; background: #fff; }
    .header-table { width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
    .company-name { font-size: 16px; font-weight: bold; text-transform: uppercase; color: #0891b2; }
    .title-box { text-align: center; margin-bottom: 15px; }
    .title-box h2 { font-size: 16px; margin: 0; text-transform: uppercase; }
    .calc-box { border: 1px solid #333; margin-bottom: 15px; border-radius: 4px; overflow: hidden; }
    .calc-title { background: #f1f5f9; padding: 6px 10px; font-weight: bold; border-bottom: 1px solid #333; font-size: 11px; }
    .calc-table { width: 100%; border-collapse: collapse; }
    .calc-table td { padding: 4px 10px; border-bottom: 1px solid #e2e8f0; font-size: 10.5px; }
    .mono { font-family: monospace; }
    .right { text-align: right; }
    .bold { font-weight: bold; }
    @media print { .no-print { display: none; } }
  </style>
</head>
<body>

  <div class="no-print" style="margin-bottom: 15px; text-align: right;">
    <button onclick="window.print()" style="padding: 6px 14px; background: #0891b2; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
      🖨️ Cetak Lampiran
    </button>
  </div>

  <!-- Kop Surat -->
  <table class="header-table">
    <tr>
      <td>
        <div class="company-name"><?= htmlspecialchars($settings['nama_perusahaan']) ?></div>
        <div><?= htmlspecialchars($settings['sub_nama']) ?></div>
      </td>
      <td style="text-align: right;">
        <strong>LAMPIRAN PERHITUNGAN METERAN</strong><br>
        <span class="mono">Ref: <?= htmlspecialchars($invoice['nomor_invoice']) ?></span>
      </td>
    </tr>
  </table>

  <div class="title-box">
    <h2>RINCIAN PERHITUNGAN PEMAKAIAN LISTRIK, AIR & KAWASAN</h2>
    <p>Unit: <strong><?= htmlspecialchars($debitur['kode_kav']) ?></strong> - <?= htmlspecialchars($debitur['nama_owner']) ?> (Periode: <?= date('F Y', mktime(0, 0, 0, (int)$invoice['periode_bulan'], 1, (int)$invoice['periode_tahun'])) ?>)</p>
    <?php if (!empty($debitur['alamat_unit'])): ?>
      <p style="font-size: 10px; color: #555; margin-top: -5px;"><?= htmlspecialchars($debitur['alamat_unit']) ?></p>
    <?php endif; ?>
  </div>

  <!-- 1. Perhitungan Listrik PLN -->
  <?php if ($kalkulasiListrik): ?>
    <div class="calc-box">
      <div class="calc-title">1. PERHITUNGAN LISTRIK PLN (Daya: <?= $debitur['watt_listrik'] ?> VA)</div>
      <table class="calc-table">
        <tr>
          <td width="35%">Meteran Awal s.d. Akhir</td>
          <td width="30%" class="mono"><?= number_format($itemListrik['meteran_pln_awal'], 2) ?> s.d. <?= number_format($itemListrik['meteran_pln_akhir'], 2) ?></td>
          <td width="35%" class="right mono bold"><?= number_format($kalkulasiListrik['pemakaian_kwh'], 2) ?> kWh</td>
        </tr>
        <tr>
          <td>Ambang Rekening Minimum (RM)</td>
          <td class="mono"><?= $debitur['watt_listrik'] ?> VA x <?= $settings['faktor_rekening_minimum'] ?></td>
          <td class="right mono"><?= number_format($kalkulasiListrik['ambang_rm_kwh'], 2) ?> kWh (<?= $kalkulasiListrik['rm_terpakai'] ? 'RM Terpakai' : 'Pemakaian Normal' ?>)</td>
        </tr>
        <tr>
          <td>Tagihan Dasar (Tarif Rp <?= number_format($settings['tarif_pln_per_kwh'], 2) ?>/kWh)</td>
          <td></td>
          <td class="right mono"><?= Format::rupiah($kalkulasiListrik['tagihan_dasar']) ?></td>
        </tr>
        <tr>
          <td>Biaya Loses (<?= $settings['persen_loses'] ?>%)</td>
          <td style="font-size: 9px; color: #666;"><?= $kalkulasiListrik['rm_terpakai'] ? 'Nihil (Kena Rekening Minimum)' : 'Dikenakan atas pemakaian aktual' ?></td>
          <td class="right mono"><?= Format::rupiah($kalkulasiListrik['biaya_loses']) ?></td>
        </tr>
        <tr>
          <td>Pajak Penerangan Jalan (PPJ <?= $settings['persen_ppj'] ?>%)</td>
          <td></td>
          <td class="right mono"><?= Format::rupiah($kalkulasiListrik['biaya_ppj']) ?></td>
        </tr>
        <tr>
          <td>Jasa Pengelolaan (<?= $settings['persen_jasa_listrik_air'] ?>%)</td>
          <td></td>
          <td class="right mono"><?= Format::rupiah($kalkulasiListrik['jasa']) ?></td>
        </tr>
        <tr style="background: #f8fafc;">
          <td class="bold">TOTAL TAGIHAN LISTRIK</td>
          <td></td>
          <td class="right mono bold" style="color: #0891b2; font-size: 11px;"><?= Format::rupiah($kalkulasiListrik['total']) ?></td>
        </tr>
      </table>
    </div>
  <?php endif; ?>

  <!-- 2. Perhitungan Air PDAM -->
  <?php if ($kalkulasiAir): ?>
    <div class="calc-box">
      <div class="calc-title">2. PERHITUNGAN AIR BERSIH / PDAM</div>
      <table class="calc-table">
        <tr>
          <td width="35%">Meteran Awal s.d. Akhir</td>
          <td width="30%" class="mono"><?= number_format($itemAir['meteran_air_awal'], 2) ?> s.d. <?= number_format($itemAir['meteran_air_akhir'], 2) ?></td>
          <td width="35%" class="right mono bold"><?= number_format($kalkulasiAir['pemakaian_m3'], 2) ?> m³</td>
        </tr>
        <tr>
          <td>Biaya Abodemen Bulanan</td>
          <td>Tetap / Flat</td>
          <td class="right mono"><?= Format::rupiah($kalkulasiAir['biaya_abodemen']) ?></td>
        </tr>
        <tr>
          <td>Tier 1 (0 s.d. <?= $settings['tarif_air_tier1_batas'] ?> m³)</td>
          <td class="mono"><?= number_format($kalkulasiAir['vol_tier1'], 2) ?> m³ x Rp <?= number_format($settings['tarif_air_tier1'], 0, ',', '.') ?></td>
          <td class="right mono"><?= Format::rupiah($kalkulasiAir['biaya_tier1']) ?></td>
        </tr>
        <tr>
          <td>Tier 2 (<?= $settings['tarif_air_tier1_batas'] ?> s.d. <?= $settings['tarif_air_tier2_batas'] ?> m³)</td>
          <td class="mono"><?= number_format($kalkulasiAir['vol_tier2'], 2) ?> m³ x Rp <?= number_format($settings['tarif_air_tier2'], 0, ',', '.') ?></td>
          <td class="right mono"><?= Format::rupiah($kalkulasiAir['biaya_tier2']) ?></td>
        </tr>
        <tr>
          <td>Tier 3 (> <?= $settings['tarif_air_tier2_batas'] ?> m³)</td>
          <td class="mono"><?= number_format($kalkulasiAir['vol_tier3'], 2) ?> m³ x Rp <?= number_format($settings['tarif_air_tier3'], 0, ',', '.') ?></td>
          <td class="right mono"><?= Format::rupiah($kalkulasiAir['biaya_tier3']) ?></td>
        </tr>
        <tr>
          <td>Jasa Pengelolaan (<?= $settings['persen_jasa_listrik_air'] ?>%)</td>
          <td></td>
          <td class="right mono"><?= Format::rupiah($kalkulasiAir['jasa']) ?></td>
        </tr>
        <tr style="background: #f8fafc;">
          <td class="bold">TOTAL TAGIHAN AIR</td>
          <td></td>
          <td class="right mono bold" style="color: #0891b2; font-size: 11px;"><?= Format::rupiah($kalkulasiAir['total']) ?></td>
        </tr>
      </table>
    </div>
  <?php endif; ?>

  <div style="font-size: 9px; color: #777; text-align: center; margin-top: 20px;">
    Dokumen ini merupakan lampiran sah yang menjadi bagian tak terpisahkan dari Invoice No. <?= htmlspecialchars($invoice['nomor_invoice']) ?>.
  </div>

</body>
</html>
