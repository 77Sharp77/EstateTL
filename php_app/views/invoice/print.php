<?php
/**
 * Kalicaa Villa - Official Printable Invoice A4
 */
use Helpers\Format;
use Services\CalculationService;

$terbilangText = CalculationService::terbilang($invoice['grand_total']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Invoice <?= htmlspecialchars($invoice['nomor_invoice']) ?> - Kalicaa Villa</title>
  <style>
    @page { size: A4 portrait; margin: 15mm; }
    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 11px;
      color: #111;
      line-height: 1.4;
      background: #fff;
    }
    .header-table { width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
    .company-name { font-size: 16px; font-weight: bold; text-transform: uppercase; color: #0891b2; }
    .company-sub { font-size: 12px; font-weight: bold; color: #333; }
    .title-box { text-align: center; margin-bottom: 20px; }
    .title-box h2 { font-size: 18px; margin: 0; text-decoration: underline; text-transform: uppercase; }
    .title-box p { margin: 3px 0 0 0; font-size: 12px; font-family: monospace; font-weight: bold; }
    
    .info-table { width: 100%; margin-bottom: 15px; }
    .info-table td { padding: 2px 4px; vertical-align: top; }
    
    .items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    .items-table th, .items-table td { border: 1px solid #333; padding: 6px 8px; }
    .items-table th { background-color: #f1f5f9; text-transform: uppercase; font-size: 10px; }
    
    .terbilang-box { border: 1px solid #999; background: #f8fafc; padding: 8px; font-style: italic; margin-bottom: 15px; font-weight: bold; }
    .footer-grid { width: 100%; margin-top: 20px; }
    .bank-box { border: 1px dashed #666; padding: 8px; background: #fafafa; font-size: 10px; width: 60%; }
    .sign-box { text-align: center; width: 35%; }
    .sign-space { height: 65px; }
    
    @media print {
      body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .no-print { display: none; }
    }
  </style>
</head>
<body>

  <!-- Floating Print Toolbar -->
  <div class="no-print" style="margin-bottom: 15px; text-align: right;">
    <button onclick="window.print()" style="padding: 6px 14px; background: #0891b2; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
      🖨️ Cetak / Simpan PDF
    </button>
  </div>

  <!-- Header / Kop Surat -->
  <table class="header-table">
    <tr>
      <td>
        <div class="company-name"><?= htmlspecialchars($settings['nama_perusahaan']) ?></div>
        <div class="company-sub"><?= htmlspecialchars($settings['sub_nama']) ?></div>
        <div style="font-size: 10px; color: #555;"><?= htmlspecialchars($settings['alamat']) ?> • Telp: <?= htmlspecialchars($settings['telepon']) ?></div>
      </td>
      <td style="text-align: right; vertical-align: middle;">
        <span style="border: 2px solid #0891b2; padding: 4px 10px; font-weight: bold; color: #0891b2; text-transform: uppercase; font-size: 12px;">
          INVOICE TAGIHAN
        </span>
      </td>
    </tr>
  </table>

  <!-- Judul & Nomor Invoice -->
  <div class="title-box">
    <h2>INVOICE</h2>
    <p><?= htmlspecialchars($invoice['nomor_invoice']) ?></p>
  </div>

  <!-- Data Debitur & Periode -->
  <table class="info-table">
    <tr>
      <td width="15%"><strong>Kepada Yth.</strong></td>
      <td width="45%">: <?= htmlspecialchars($invoice['nama_debitur']) ?></td>
      <td width="18%"><strong>Tanggal Terbit</strong></td>
      <td width="22%">: <?= Format::tanggalIndo($invoice['tanggal_terbit']) ?></td>
    </tr>
    <tr>
      <td><strong>Kode Kavling</strong></td>
      <td>: <strong><?= htmlspecialchars($invoice['kode_kav']) ?></strong></td>
      <td><strong>Periode Tagihan</strong></td>
      <td>: <?= date('F Y', mktime(0, 0, 0, (int)$invoice['periode_bulan'], 1, (int)$invoice['periode_tahun'])) ?></td>
    </tr>
    <tr>
      <td><strong>No. HP / WA</strong></td>
      <td>: <?= htmlspecialchars($invoice['no_hp']) ?></td>
      <td><strong>Status Pembayaran</strong></td>
      <td>: <strong><?= strtoupper($invoice['status_bayar']) ?></strong></td>
    </tr>
  </table>

  <!-- Rincian Item Tagihan -->
  <table class="items-table">
    <thead>
      <tr>
        <th width="5%">No</th>
        <th width="45%">Uraian Pekerjaan / Tagihan</th>
        <th width="15%">Kode COA</th>
        <th width="15%" style="text-align: right;">DPP (Rp)</th>
        <th width="8%" style="text-align: center;">PPN</th>
        <th width="15%" style="text-align: right;">Jumlah (Rp)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $idx => $it): ?>
        <tr>
          <td style="text-align: center;"><?= $idx + 1 ?></td>
          <td><?= htmlspecialchars($it['keterangan']) ?></td>
          <td style="font-family: monospace; font-size: 10px;"><?= htmlspecialchars($it['kode_coa']) ?></td>
          <td style="text-align: right; font-family: monospace;"><?= Format::rupiah($it['nilai_dpp']) ?></td>
          <td style="text-align: center; font-family: monospace;"><?= $it['ppn_persen'] ?>%</td>
          <td style="text-align: right; font-family: monospace; font-weight: bold;"><?= Format::rupiah($it['total']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="3" style="text-align: right; font-weight: bold;">SUBTOTAL DPP</td>
        <td style="text-align: right; font-family: monospace; font-weight: bold;"><?= Format::rupiah($invoice['subtotal_dpp']) ?></td>
        <td style="text-align: right; font-weight: bold;">PPN</td>
        <td style="text-align: right; font-family: monospace; font-weight: bold;"><?= Format::rupiah($invoice['total_ppn']) ?></td>
      </tr>
      <tr style="background-color: #f1f5f9;">
        <td colspan="5" style="text-align: right; font-weight: bold; font-size: 12px;">GRAND TOTAL</td>
        <td style="text-align: right; font-family: monospace; font-weight: bold; font-size: 12px; color: #0891b2;">
          <?= Format::rupiah($invoice['grand_total']) ?>
        </td>
      </tr>
    </tfoot>
  </table>

  <!-- Terbilang -->
  <div class="terbilang-box">
    Terbilang: # <?= $terbilangText ?> #
  </div>

  <!-- Bank & Tanda Tangan -->
  <table class="footer-grid">
    <tr>
      <td class="bank-box" style="vertical-align: top;">
        <strong>Informasi Rekening Pembayaran:</strong><br>
        Bank: <strong><?= htmlspecialchars($settings['nama_bank']) ?></strong><br>
        No. Rekening: <strong style="font-family: monospace; font-size: 12px;"><?= htmlspecialchars($settings['no_rekening']) ?></strong><br>
        Atas Nama: <strong><?= htmlspecialchars($settings['atas_nama']) ?></strong><br>
        <span style="font-size: 9px; color: #666; margin-top: 4px; display: block;">
          *Harap mencantumkan Nomor Invoice pada berita transfer atau mengirimkan bukti transfer via WhatsApp Finance.
        </span>
      </td>
      <td width="5%"></td>
      <td class="sign-box" style="vertical-align: top;">
        Tanjung Lesung, <?= Format::tanggalIndo($invoice['tanggal_terbit']) ?><br>
        <strong>PT Kalicaa Management</strong>
        <div class="sign-space"></div>
        ( <strong>Bagian Keuangan</strong> )
      </td>
    </tr>
  </table>

</body>
</html>
