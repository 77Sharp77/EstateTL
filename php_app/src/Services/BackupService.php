<?php
/**
 * Kalicaa Villa Estate Management
 * Backup & Excel Multi-Module Service
 */

declare(strict_types=1);

namespace Services;

use PDO;
use Exception;

class BackupService {
    
    /**
     * Ambil seluruh data dari semua tabel database
     */
    public static function getAllData(PDO $pdo): array {
        $tables = [
            'app_settings',
            'master_coa',
            'debiturs',
            'skp_properties',
            'spk_records',
            'spk_payments',
            'invoices',
            'invoice_items',
            'rvbw',
            'rvbw_items',
            'titipan_rvbw',
            'titipan_pvbw',
        ];

        $result = [];
        foreach ($tables as $t) {
            try {
                $stmt = $pdo->query("SELECT * FROM `$t`");
                $result[$t] = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            } catch (Exception $e) {
                $result[$t] = [];
            }
        }
        return $result;
    }

    /**
     * Hitung ringkasan jumlah baris tiap tabel
     */
    public static function getTableStats(PDO $pdo): array {
        $tables = [
            'debiturs' => 'Master Debitur',
            'skp_properties' => 'SKP (Penjualan Unit)',
            'spk_records' => 'SPK Kontraktor',
            'spk_payments' => 'Pembayaran SPK',
            'invoices' => 'Invoice Resmi (IVBW)',
            'invoice_items' => 'Item Rincian Invoice',
            'rvbw' => 'Receivable Voucher (RVBW)',
            'rvbw_items' => 'Item Rincian RVBW',
            'titipan_rvbw' => 'Titipan Dana Masuk (RVBW)',
            'titipan_pvbw' => 'Titipan Dana Keluar (PVBW)',
            'master_coa' => 'Master COA',
            'app_settings' => 'Konfigurasi & Tarif',
        ];

        $stats = [];
        foreach ($tables as $table => $label) {
            try {
                $cnt = (int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                $stats[$table] = [
                    'label' => $label,
                    'count' => $cnt
                ];
            } catch (Exception $e) {
                $stats[$table] = [
                    'label' => $label,
                    'count' => 0
                ];
            }
        }
        return $stats;
    }

    /**
     * Generate Excel Multi-Sheet XML Spreadsheet 2003
     * Kompatibel penuh dengan Microsoft Excel, Google Sheets, LibreOffice
     */
    public static function generateExcelXml(PDO $pdo): string {
        $allData = self::getAllData($pdo);

        $sheetNames = [
            'debiturs' => 'DEBITUR',
            'skp_properties' => 'SKP_PROPERTIES',
            'spk_records' => 'SPK_RECORDS',
            'spk_payments' => 'SPK_PAYMENTS',
            'invoices' => 'INVOICES',
            'invoice_items' => 'INVOICE_ITEMS',
            'rvbw' => 'RVBW',
            'rvbw_items' => 'RVBW_ITEMS',
            'titipan_rvbw' => 'TITIPAN_RVBW',
            'titipan_pvbw' => 'TITIPAN_PVBW',
            'master_coa' => 'MASTER_COA',
            'app_settings' => 'SETTINGS',
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
        $xml .= ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
        $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";

        // Styles
        $xml .= '<Styles>' . "\n";
        $xml .= ' <Style ss:ID="Default" ss:Name="Normal">' . "\n";
        $xml .= '  <Alignment ss:Vertical="Center"/>' . "\n";
        $xml .= '  <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#000000"/>' . "\n";
        $xml .= ' </Style>' . "\n";
        $xml .= ' <Style ss:ID="HeaderStyle">' . "\n";
        $xml .= '  <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>' . "\n";
        $xml .= '  <Borders>' . "\n";
        $xml .= '   <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#0e7490"/>' . "\n";
        $xml .= '  </Borders>' . "\n";
        $xml .= '  <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#FFFFFF" ss:Bold="1"/>' . "\n";
        $xml .= '  <Interior ss:Color="#0891b2" ss:Pattern="Solid"/>' . "\n";
        $xml .= ' </Style>' . "\n";
        $xml .= ' <Style ss:ID="NumberStyle">' . "\n";
        $xml .= '  <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>' . "\n";
        $xml .= '  <NumberFormat ss:Format="#,##0"/>' . "\n";
        $xml .= ' </Style>' . "\n";
        $xml .= ' <Style ss:ID="DateStyle">' . "\n";
        $xml .= '  <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>' . "\n";
        $xml .= ' </Style>' . "\n";
        $xml .= '</Styles>' . "\n";

        // Generate each sheet
        foreach ($sheetNames as $tableKey => $sheetTitle) {
            $rows = $allData[$tableKey] ?? [];
            $xml .= ' <Worksheet ss:Name="' . htmlspecialchars($sheetTitle) . '">' . "\n";
            $xml .= '  <Table ss:DefaultRowHeight="20">' . "\n";

            if (!empty($rows)) {
                $columns = array_keys($rows[0]);

                // Header Row
                $xml .= '   <Row ss:Height="24">' . "\n";
                foreach ($columns as $col) {
                    $xml .= '    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">' . htmlspecialchars(strtoupper($col)) . '</Data></Cell>' . "\n";
                }
                $xml .= '   </Row>' . "\n";

                // Data Rows
                foreach ($rows as $row) {
                    $xml .= '   <Row>' . "\n";
                    foreach ($columns as $col) {
                        $val = $row[$col] ?? '';
                        if ($val === null) {
                            $xml .= '    <Cell><Data ss:Type="String"></Data></Cell>' . "\n";
                        } elseif (is_numeric($val) && !str_starts_with((string)$val, '0') && strlen((string)$val) < 15) {
                            $xml .= '    <Cell ss:StyleID="NumberStyle"><Data ss:Type="Number">' . (float)$val . '</Data></Cell>' . "\n";
                        } else {
                            $xml .= '    <Cell><Data ss:Type="String">' . htmlspecialchars((string)$val) . '</Data></Cell>' . "\n";
                        }
                    }
                    $xml .= '   </Row>' . "\n";
                }
            } else {
                // Empty sheet placeholder
                $xml .= '   <Row ss:Height="24">' . "\n";
                $xml .= '    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">STATUS</Data></Cell>' . "\n";
                $xml .= '   </Row>' . "\n";
                $xml .= '   <Row>' . "\n";
                $xml .= '    <Cell><Data ss:Type="String">Tidak ada data untuk modul ini</Data></Cell>' . "\n";
                $xml .= '   </Row>' . "\n";
            }

            $xml .= '  </Table>' . "\n";
            $xml .= ' </Worksheet>' . "\n";
        }

        $xml .= '</Workbook>';
        return $xml;
    }

    /**
     * Restore database dari data array (dari JSON payload atau parsed Excel)
     */
    public static function restoreFromData(PDO $pdo, array $payload, string $mode = 'replace'): array {
        $stats = [];
        $isDriverSqlite = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';

        $pdo->beginTransaction();
        try {
            if ($isDriverSqlite) {
                $pdo->exec("PRAGMA foreign_keys = OFF;");
            } else {
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
            }

            // 1. Settings
            if (!empty($payload['app_settings']) || !empty($payload['settings'])) {
                $setRows = $payload['app_settings'] ?? $payload['settings'] ?? [];
                if (isset($setRows['nama_perusahaan'])) {
                    $setRows = [$setRows];
                }
                if (!empty($setRows)) {
                    $s = $setRows[0];
                    $fields = [];
                    $values = [];
                    foreach ($s as $k => $v) {
                        if ($k === 'id' || $k === 'updated_at') continue;
                        $fields[] = "`$k` = ?";
                        $values[] = $v;
                    }
                    if (!empty($fields)) {
                        $stmt = $pdo->prepare("UPDATE app_settings SET " . implode(', ', $fields) . " WHERE id = 1");
                        $stmt->execute($values);
                        $stats['app_settings'] = 1;
                    }
                }
            }

            // 2. Master COA
            if (isset($payload['master_coa']) || isset($payload['coaList'])) {
                $coaRows = $payload['master_coa'] ?? $payload['coaList'] ?? [];
                if ($mode === 'replace') {
                    $pdo->exec("DELETE FROM master_coa");
                }
                $cnt = 0;
                $stmt = $pdo->prepare("INSERT OR REPLACE INTO master_coa (kode_coa, nama_coa, deskripsi) VALUES (?, ?, ?)");
                foreach ($coaRows as $r) {
                    if (empty($r['kode_coa'])) continue;
                    $stmt->execute([$r['kode_coa'], $r['nama_coa'] ?? '', $r['deskripsi'] ?? '']);
                    $cnt++;
                }
                $stats['master_coa'] = $cnt;
            }

            // 3. Debitur
            if (isset($payload['debiturs'])) {
                $debRows = $payload['debiturs'];
                if ($mode === 'replace') {
                    $pdo->exec("DELETE FROM debiturs");
                }
                $cnt = 0;
                $stmt = $pdo->prepare("
                    INSERT OR REPLACE INTO debiturs (
                        kode_kav, nama_owner, no_hp, email, tipe_unit, status,
                        watt_listrik, luas_m2_kawasan, landscape_flat, kolam_flat,
                        internet_flat, alamat_unit, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                foreach ($debRows as $r) {
                    if (empty($r['kode_kav'])) continue;
                    $stmt->execute([
                        $r['kode_kav'],
                        $r['nama_owner'] ?? 'Owner',
                        $r['no_hp'] ?? '-',
                        $r['email'] ?? null,
                        $r['tipe_unit'] ?? 'Villa Standard',
                        $r['status'] ?? 'aktif',
                        (int)($r['watt_listrik'] ?? 6600),
                        (float)($r['luas_m2_kawasan'] ?? 350),
                        (float)($r['landscape_flat'] ?? 0),
                        (float)($r['kolam_flat'] ?? 0),
                        (float)($r['internet_flat'] ?? 450000),
                        $r['alamat_unit'] ?? null,
                        $r['created_at'] ?? date('Y-m-d H:i:s')
                    ]);
                    $cnt++;
                }
                $stats['debiturs'] = $cnt;
            }

            // 4. SKP Properties
            if (isset($payload['skp_properties']) || isset($payload['skpList'])) {
                $skpRows = $payload['skp_properties'] ?? $payload['skpList'] ?? [];
                if ($mode === 'replace') {
                    $pdo->exec("DELETE FROM skp_properties");
                }
                $cnt = 0;
                $stmt = $pdo->prepare("
                    INSERT OR REPLACE INTO skp_properties (
                        nomor_skp, tanggal_skp, debitur_id, kode_kav, nama_owner,
                        tipe_unit_skp, nilai_jual, uang_muka, tenor_bulan,
                        tanggal_mulai_cicilan, nilai_angsuran_bulanan, status, catatan, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                foreach ($skpRows as $r) {
                    if (empty($r['nomor_skp'])) continue;
                    // Look up debitur_id by kode_kav if not numeric
                    $debId = is_numeric($r['debitur_id'] ?? null) ? (int)$r['debitur_id'] : 1;
                    if (!empty($r['kode_kav'])) {
                        $foundId = $pdo->query("SELECT id FROM debiturs WHERE kode_kav = " . $pdo->quote($r['kode_kav']))->fetchColumn();
                        if ($foundId) $debId = (int)$foundId;
                    }

                    $stmt->execute([
                        $r['nomor_skp'],
                        $r['tanggal_skp'] ?? date('Y-m-d'),
                        $debId,
                        $r['kode_kav'] ?? '',
                        $r['nama_owner'] ?? '',
                        $r['tipe_unit_skp'] ?? 'Residensi',
                        (float)($r['nilai_jual'] ?? 0),
                        (float)($r['uang_muka'] ?? 0),
                        (int)($r['tenor_bulan'] ?? 12),
                        $r['tanggal_mulai_cicilan'] ?? date('Y-m-d'),
                        (float)($r['nilai_angsuran_bulanan'] ?? 0),
                        $r['status'] ?? 'Berjalan',
                        $r['catatan'] ?? null,
                        $r['created_at'] ?? date('Y-m-d H:i:s')
                    ]);
                    $cnt++;
                }
                $stats['skp_properties'] = $cnt;
            }

            // 5. SPK Records & Payments
            if (isset($payload['spk_records']) || isset($payload['spkList'])) {
                $spkRows = $payload['spk_records'] ?? $payload['spkList'] ?? [];
                if ($mode === 'replace') {
                    $pdo->exec("DELETE FROM spk_payments");
                    $pdo->exec("DELETE FROM spk_records");
                }
                $cntSpk = 0;
                $cntPay = 0;
                $stmtSpk = $pdo->prepare("
                    INSERT OR REPLACE INTO spk_records (
                        nomor_spk, jenis_pekerjaan, nama_kontraktor, nominal_kontrak,
                        pph_persen, pic_pekerjaan, keterangan, tanggal_spk, status_manual, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmtPay = $pdo->prepare("
                    INSERT INTO spk_payments (
                        spk_id, tahapan, nilai_pembayaran, nomor_voucher, tanggal_pembayaran, catatan, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                foreach ($spkRows as $r) {
                    if (empty($r['nomor_spk'])) continue;
                    $stmtSpk->execute([
                        $r['nomor_spk'],
                        $r['jenis_pekerjaan'] ?? 'Kontraktor',
                        $r['nama_kontraktor'] ?? '-',
                        (float)($r['nominal_kontrak'] ?? 0),
                        (float)($r['pph_persen'] ?? 2),
                        $r['pic_pekerjaan'] ?? '-',
                        $r['keterangan'] ?? '',
                        $r['tanggal_spk'] ?? date('Y-m-d'),
                        $r['status_manual'] ?? ($r['status'] ?? 'Belum Selesai'),
                        $r['created_at'] ?? date('Y-m-d H:i:s')
                    ]);
                    $spkId = (int)$pdo->lastInsertId();
                    $cntSpk++;

                    // Check payments
                    $payments = $r['pembayaran'] ?? $r['payments'] ?? [];
                    if (is_array($payments)) {
                        foreach ($payments as $p) {
                            $stmtPay->execute([
                                $spkId,
                                $p['tahapan'] ?? 'DP',
                                (float)($p['nilai_pembayaran'] ?? $p['nilai'] ?? 0),
                                $p['nomor_voucher'] ?? ('PV-' . rand(1000, 9999)),
                                $p['tanggal_pembayaran'] ?? ($p['tanggal'] ?? date('Y-m-d')),
                                $p['catatan'] ?? null,
                                $p['created_at'] ?? date('Y-m-d H:i:s')
                            ]);
                            $cntPay++;
                        }
                    }
                }
                $stats['spk_records'] = $cntSpk;
                $stats['spk_payments'] = $cntPay;
            }

            // 6. Invoices & Items
            if (isset($payload['invoices']) || isset($payload['invoiceList'])) {
                $invRows = $payload['invoices'] ?? $payload['invoiceList'] ?? [];
                if ($mode === 'replace') {
                    $pdo->exec("DELETE FROM invoice_items");
                    $pdo->exec("DELETE FROM invoices");
                }
                $cntInv = 0;
                $cntItems = 0;
                $stmtInv = $pdo->prepare("
                    INSERT OR REPLACE INTO invoices (
                        nomor_invoice, debitur_id, tanggal_terbit, jatuh_tempo,
                        periode_bulan, periode_tahun, subtotal_dpp, total_ppn, grand_total,
                        status_bayar, tanggal_lunas, sumber_rvbw_id, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmtItem = $pdo->prepare("
                    INSERT INTO invoice_items (
                        invoice_id, tagihan_bulanan_kategori, keterangan, kode_coa,
                        meteran_pln_awal, meteran_pln_akhir, meteran_air_awal, meteran_air_akhir,
                        pemakaian_volume, nilai_dpp, ppn_persen, total
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                foreach ($invRows as $r) {
                    if (empty($r['nomor_invoice'])) continue;
                    $debId = 1;
                    if (!empty($r['kode_kav'])) {
                        $found = $pdo->query("SELECT id FROM debiturs WHERE kode_kav = " . $pdo->quote($r['kode_kav']))->fetchColumn();
                        if ($found) $debId = (int)$found;
                    } elseif (is_numeric($r['debitur_id'] ?? null)) {
                        $debId = (int)$r['debitur_id'];
                    }

                    $stmtInv->execute([
                        $r['nomor_invoice'],
                        $debId,
                        $r['tanggal_terbit'] ?? date('Y-m-d'),
                        $r['jatuh_tempo'] ?? date('Y-m-d', strtotime('+14 days')),
                        (int)($r['periode_bulan'] ?? date('m')),
                        (int)($r['periode_tahun'] ?? date('Y')),
                        (float)($r['subtotal_dpp'] ?? 0),
                        (float)($r['total_ppn'] ?? 0),
                        (float)($r['grand_total'] ?? 0),
                        $r['status_bayar'] ?? 'Belum Bayar',
                        $r['tanggal_lunas'] ?? ($r['tgl_bayar'] ?? null),
                        $r['sumber_rvbw_id'] ?? null,
                        $r['created_at'] ?? date('Y-m-d H:i:s')
                    ]);
                    $invId = (int)$pdo->lastInsertId();
                    $cntInv++;

                    // Items
                    $items = $r['items'] ?? [];
                    if (is_array($items)) {
                        foreach ($items as $item) {
                            $stmtItem->execute([
                                $invId,
                                $item['tagihan_bulanan_kategori'] ?? null,
                                $item['keterangan'] ?? 'Tagihan',
                                $item['kode_coa'] ?? '40602.0600.0000',
                                isset($item['meteran_pln_awal']) ? (float)$item['meteran_pln_awal'] : null,
                                isset($item['meteran_pln_akhir']) ? (float)$item['meteran_pln_akhir'] : null,
                                isset($item['meteran_air_awal']) ? (float)$item['meteran_air_awal'] : null,
                                isset($item['meteran_air_akhir']) ? (float)$item['meteran_air_akhir'] : null,
                                (float)($item['pemakaian_volume'] ?? 0),
                                (float)($item['nilai_dpp'] ?? 0),
                                (float)($item['ppn_persen'] ?? 0),
                                (float)($item['total'] ?? 0)
                            ]);
                            $cntItems++;
                        }
                    }
                }
                $stats['invoices'] = $cntInv;
                $stats['invoice_items'] = $cntItems;
            }

            // 7. RVBW & Items
            if (isset($payload['rvbw']) || isset($payload['rvbwList'])) {
                $rvRows = $payload['rvbw'] ?? $payload['rvbwList'] ?? [];
                if ($mode === 'replace') {
                    $pdo->exec("DELETE FROM rvbw_items");
                    $pdo->exec("DELETE FROM rvbw");
                }
                $cntRv = 0;
                $cntRvItems = 0;
                $stmtRv = $pdo->prepare("
                    INSERT OR REPLACE INTO rvbw (
                        nomor_rvbw, tanggal, debitur_id, periode_bulan, periode_tahun,
                        subtotal_dpp, total_ppn, grand_total, status, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmtRvItem = $pdo->prepare("
                    INSERT INTO rvbw_items (
                        rvbw_id, kategori, keterangan, kode_coa, nilai_dpp, nilai_ppn, total
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                foreach ($rvRows as $r) {
                    if (empty($r['nomor_rvbw'])) continue;
                    $debId = 1;
                    if (!empty($r['kode_kav'])) {
                        $found = $pdo->query("SELECT id FROM debiturs WHERE kode_kav = " . $pdo->quote($r['kode_kav']))->fetchColumn();
                        if ($found) $debId = (int)$found;
                    } elseif (is_numeric($r['debitur_id'] ?? null)) {
                        $debId = (int)$r['debitur_id'];
                    }

                    $stmtRv->execute([
                        $r['nomor_rvbw'],
                        $r['tanggal'] ?? date('Y-m-d'),
                        $debId,
                        (int)($r['periode_bulan'] ?? date('m')),
                        (int)($r['periode_tahun'] ?? date('Y')),
                        (float)($r['subtotal_dpp'] ?? 0),
                        (float)($r['total_ppn'] ?? 0),
                        (float)($r['grand_total'] ?? 0),
                        $r['status'] ?? 'Draft',
                        $r['created_at'] ?? date('Y-m-d H:i:s')
                    ]);
                    $rvId = (int)$pdo->lastInsertId();
                    $cntRv++;

                    $items = $r['items'] ?? [];
                    if (is_array($items)) {
                        foreach ($items as $item) {
                            $stmtRvItem->execute([
                                $rvId,
                                $item['kategori'] ?? ($item['tagihan_bulanan_kategori'] ?? 'UMUM'),
                                $item['keterangan'] ?? 'Tagihan RVBW',
                                $item['kode_coa'] ?? '40602.0600.0000',
                                (float)($item['nilai_dpp'] ?? 0),
                                (float)($item['nilai_ppn'] ?? 0),
                                (float)($item['total'] ?? 0)
                            ]);
                            $cntRvItems++;
                        }
                    }
                }
                $stats['rvbw'] = $cntRv;
                $stats['rvbw_items'] = $cntRvItems;
            }

            // 8. Titipan RVBW & PVBW
            if (isset($payload['titipan_rvbw']) || isset($payload['titipanRVBW'])) {
                $titipanRows = $payload['titipan_rvbw'] ?? $payload['titipanRVBW'] ?? [];
                if ($mode === 'replace') {
                    $pdo->exec("DELETE FROM titipan_pvbw");
                    $pdo->exec("DELETE FROM titipan_rvbw");
                }
                $cntTitipan = 0;
                $cntPvbw = 0;

                $stmtTRv = $pdo->prepare("
                    INSERT OR REPLACE INTO titipan_rvbw (
                        nomor_rvbw, entitas, tanggal_terima, nominal, keterangan, status, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmtTPv = $pdo->prepare("
                    INSERT OR REPLACE INTO titipan_pvbw (
                        titipan_rvbw_id, nomor_pvbw, tanggal_kembali, nominal, keterangan, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?)
                ");

                foreach ($titipanRows as $r) {
                    if (empty($r['nomor_rvbw'])) continue;
                    $stmtTRv->execute([
                        $r['nomor_rvbw'],
                        $r['entitas'] ?? 'BWJ',
                        $r['tanggal_terima'] ?? date('Y-m-d'),
                        (float)($r['nominal'] ?? 0),
                        $r['keterangan'] ?? '',
                        $r['status'] ?? 'Aktif',
                        $r['created_at'] ?? date('Y-m-d H:i:s')
                    ]);
                    $trvId = (int)$pdo->lastInsertId();
                    $cntTitipan++;
                }

                // If titipan_pvbw is provided separately
                $pvbwRows = $payload['titipan_pvbw'] ?? $payload['titipanPVBW'] ?? [];
                foreach ($pvbwRows as $p) {
                    if (empty($p['nomor_pvbw'])) continue;
                    $parentId = (int)($p['titipan_rvbw_id'] ?? 1);
                    if (!empty($p['nomor_rvbw'])) {
                        $found = $pdo->query("SELECT id FROM titipan_rvbw WHERE nomor_rvbw = " . $pdo->quote($p['nomor_rvbw']))->fetchColumn();
                        if ($found) $parentId = (int)$found;
                    }
                    $stmtTPv->execute([
                        $parentId,
                        $p['nomor_pvbw'],
                        $p['tanggal_kembali'] ?? date('Y-m-d'),
                        (float)($p['nominal'] ?? 0),
                        $p['keterangan'] ?? '',
                        $p['created_at'] ?? date('Y-m-d H:i:s')
                    ]);
                    $cntPvbw++;
                }

                $stats['titipan_rvbw'] = $cntTitipan;
                $stats['titipan_pvbw'] = $cntPvbw;
            }

            if ($isDriverSqlite) {
                $pdo->exec("PRAGMA foreign_keys = ON;");
            } else {
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
            }

            $pdo->commit();
            return [
                'success' => true,
                'stats' => $stats
            ];
        } catch (Exception $e) {
            $pdo->rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
