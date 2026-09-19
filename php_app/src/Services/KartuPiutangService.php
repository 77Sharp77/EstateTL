<?php
/**
 * Kalicaa Villa - Kartu Piutang Service
 * Mengelola kartu piutang pemilik villa dengan kalkulasi Saldo Berjalan Dinamis:
 * - Mutasi kronologis: (periode_tahun * 12 + periode_bulan) ASC, tanggal_terbit ASC, nomor_invoice ASC
 * - Saldo Berjalan = Saldo Berjalan + (Debit - Kredit)
 * - Tampilan dapat di-sort (Periode Desc/Asc) tanpa merusak kebenaran saldo berjalan riil.
 */

namespace Services;

use Config\Database;
use PDO;

class KartuPiutangService {

    /**
     * Hitung Kartu Piutang untuk seluruh debitur atau filter debitur tertentu
     */
    public static function hitungSemuaKartu(
        string $tipeFilter = 'All',
        string $search = '',
        bool $showZero = false,
        string $sortField = 'kavling',
        string $sortDir = 'asc',
        string $entrySort = 'asc'
    ): array {
        $db = Database::getConnection();

        // 1. Ambil seluruh debitur aktif
        $stmtDeb = $db->query("SELECT * FROM debiturs ORDER BY kode_kav ASC");
        $debiturs = $stmtDeb->fetchAll();

        // 2. Ambil seluruh invoice urut kronologis murni
        $stmtInv = $db->query("
            SELECT i.*
            FROM invoices i
            ORDER BY (i.periode_tahun * 12 + i.periode_bulan) ASC, i.tanggal_terbit ASC, i.nomor_invoice ASC
        ");
        $allInvoices = $stmtInv->fetchAll();

        // Ambil seluruh items
        $stmtItems = $db->query("SELECT * FROM invoice_items");
        $rawItems = $stmtItems->fetchAll();
        $itemsByInvoice = [];
        foreach ($rawItems as $it) {
            $itemsByInvoice[$it['invoice_id']][] = $it;
        }

        // Group invoice per debitur_id
        $invoicesByDebitur = [];
        foreach ($allInvoices as $inv) {
            $invoicesByDebitur[$inv['debitur_id']][] = $inv;
        }

        $hasil = [];

        foreach ($debiturs as $deb) {
            $debId = $deb['id'];
            $invList = $invoicesByDebitur[$debId] ?? [];

            $saldoBerjalan = 0.0;
            $totalDebit = 0.0;
            $totalKredit = 0.0;
            $entri = [];

            foreach ($invList as $inv) {
                // Filter tipe: All, Estate, SKP
                $hasSKP = false;
                $hasEstate = false;
                $items = $itemsByInvoice[$inv['id']] ?? [];
                
                $breakdownListrik = 0.0;
                $breakdownAir = 0.0;
                $breakdownKawasan = 0.0;
                $breakdownLain = 0.0;

                foreach ($items as $it) {
                    $desc = $it['keterangan'] ?? '';
                    $tot = (float)($it['total'] ?? 0.0);
                    $kat = $it['tagihan_bulanan_kategori'] ?? 'LAIN';

                    if (stripos($desc, 'SKP') !== false || stripos($desc, 'Cicilan') !== false || stripos($desc, 'Angsuran') !== false) {
                        $hasSKP = true;
                    } else {
                        $hasEstate = true;
                    }

                    if ($kat === 'LISTRIK' || stripos($desc, 'Listrik') !== false) {
                        $breakdownListrik += $tot;
                    } elseif ($kat === 'AIR' || stripos($desc, 'Air') !== false) {
                        $breakdownAir += $tot;
                    } elseif ($kat === 'KAWASAN' || stripos($desc, 'Kawasan') !== false || stripos($desc, 'IPL') !== false) {
                        $breakdownKawasan += $tot;
                    } else {
                        $breakdownLain += $tot;
                    }
                }

                if ($tipeFilter === 'Estate' && $hasSKP && !$hasEstate) continue;
                if ($tipeFilter === 'SKP' && !$hasSKP) continue;

                $debit = (float)$inv['grand_total'];
                $isLunas = ($inv['status_bayar'] === 'Lunas');
                $kredit = $isLunas ? $debit : 0.0;

                $saldoBerjalan += ($debit - $kredit);
                $totalDebit += $debit;
                $totalKredit += $kredit;

                $entri[] = [
                    'id'            => $inv['id'],
                    'tanggal'       => $inv['tanggal_terbit'],
                    'nomor_invoice' => $inv['nomor_invoice'],
                    'periode_bulan' => (int)$inv['periode_bulan'],
                    'periode_tahun' => (int)$inv['periode_tahun'],
                    'periode_label' => date('F Y', mktime(0, 0, 0, (int)$inv['periode_bulan'], 1, (int)$inv['periode_tahun'])),
                    'debit'         => $debit,
                    'kredit'        => $kredit,
                    'saldo'         => $saldoBerjalan,
                    'status_bayar'  => $inv['status_bayar'],
                    'tgl_bayar'     => $inv['tanggal_lunas'] ?? null,
                    'breakdown'     => [
                        'listrik' => $breakdownListrik,
                        'air'     => $breakdownAir,
                        'kawasan' => $breakdownKawasan,
                        'lain'    => $breakdownLain
                    ]
                ];
            }

            $saldoAkhir = $totalDebit - $totalKredit;

            // Jika filter hide zero balance aktif dan saldo akhir <= 0, lewati
            if (!$showZero && $saldoAkhir <= 0 && count($entri) > 0) {
                continue;
            }

            // Filter pencarian (Nama atau Kavling)
            if (!empty($search)) {
                $searchLower = strtolower($search);
                $match = str_contains(strtolower($deb['nama_owner']), $searchLower) ||
                         str_contains(strtolower($deb['kode_kav']), $searchLower);
                if (!$match) continue;
            }

            // Sort transaksi entri di dalam kartu jika diminta
            if ($entrySort === 'desc' || ($sortField === 'periode' && $sortDir === 'desc')) {
                usort($entri, function($a, $b) {
                    $keyA = $a['periode_tahun'] * 12 + $a['periode_bulan'];
                    $keyB = $b['periode_tahun'] * 12 + $b['periode_bulan'];
                    return $keyB <=> $keyA;
                });
            } else {
                usort($entri, function($a, $b) {
                    $keyA = $a['periode_tahun'] * 12 + $a['periode_bulan'];
                    $keyB = $b['periode_tahun'] * 12 + $b['periode_bulan'];
                    return $keyA <=> $keyB;
                });
            }

            $hasil[] = [
                'debitur_id'   => $deb['id'],
                'kode_kav'     => $deb['kode_kav'],
                'nama_owner'   => $deb['nama_owner'],
                'no_hp'        => $deb['no_hp'],
                'total_debit'  => $totalDebit,
                'total_kredit' => $totalKredit,
                'saldo_akhir'  => $saldoAkhir,
                'entri'        => $entri
            ];
        }

        // Sorting list kartu debitur
        usort($hasil, function($a, $b) use ($sortField, $sortDir) {
            $cmp = 0;
            if ($sortField === 'periode') {
                $maxA = 0; foreach ($a['entri'] as $e) $maxA = max($maxA, $e['periode_tahun'] * 12 + $e['periode_bulan']);
                $maxB = 0; foreach ($b['entri'] as $e) $maxB = max($maxB, $e['periode_tahun'] * 12 + $e['periode_bulan']);
                $cmp = $maxA - $maxB;
            } elseif ($sortField === 'nama') {
                $cmp = strcmp($a['nama_owner'], $b['nama_owner']);
            } elseif ($sortField === 'kavling') {
                $cmp = strcmp($a['kode_kav'], $b['kode_kav']);
            } elseif ($sortField === 'saldo') {
                $cmp = ($a['saldo_akhir'] < $b['saldo_akhir']) ? -1 : 1;
            }
            return ($sortDir === 'desc') ? -$cmp : $cmp;
        });

        return $hasil;
    }

    public static function getKartuPiutangSemua(PDO $pdo, string $search = '', string $tipe = 'All', string $sort = 'kavling_asc', bool $showZero = false, string $entrySort = 'asc'): array {
        $sortDir = 'asc';
        $sortField = $sort;
        if (str_ends_with($sort, '_desc')) {
            $sortField = substr($sort, 0, -5);
            $sortDir = 'desc';
        } elseif (str_ends_with($sort, '_asc')) {
            $sortField = substr($sort, 0, -4);
            $sortDir = 'asc';
        }

        return self::hitungSemuaKartu($tipe, $search, $showZero, $sortField, $sortDir, $entrySort);
    }
}
