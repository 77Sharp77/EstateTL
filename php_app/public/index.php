<?php
/**
 * Kalicaa Villa Estate Management - Main Front Controller
 * PHP 8.2+ Architecture
 */

declare(strict_types=1);

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Session Initialization
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'nama_lengkap' => 'Super Administrator',
        'role' => 'Super Admin',
        'username' => 'admin',
        'avatar_color' => '#06b6d4'
    ];
}

// Autoloader
spl_autoload_register(function ($class) {
    $prefixes = [
        'Config\\' => __DIR__ . '/../config/',
        'Services\\' => __DIR__ . '/../src/Services/',
        'Helpers\\' => __DIR__ . '/../src/Helpers/'
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Database Initialization
use Config\Database;
use Services\CalculationService;
use Services\KartuPiutangService;
use Services\BackupService;
use Services\ImportService;
use Helpers\Format;

$pdo = Database::getConnection();

// Ensure DB is initialized (auto-seed if empty in SQLite fallback)
try {
    $tableCheck = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='debiturs'")->fetch();
    if (!$tableCheck) {
        $schemaSql = file_get_contents(__DIR__ . '/../database/schema_sqlite.sql');
        $pdo->exec($schemaSql);

        if (file_exists(__DIR__ . '/../database/seeder_sqlite.sql')) {
            $seederSql = file_get_contents(__DIR__ . '/../database/seeder_sqlite.sql');
            $pdo->exec($seederSql);
        }
    }
} catch (\Exception $e) {
    // If on MySQL, tables are already handled by schema.sql
}

// Global Settings Helper
function getAppSettings(\PDO $pdo): array {
    $row = $pdo->query("SELECT * FROM app_settings WHERE id = 1")->fetch();
    return $row ?: [
        'nama_perusahaan' => 'Kalicaa Villa',
        'sub_nama' => 'Tanjung Lesung Beach Resort',
        'alamat' => 'Jl. Pantai Tanjung Lesung, Pandeglang, Banten',
        'telepon' => '(0253) 401-234',
        'nama_bank' => 'BCA',
        'no_rekening' => '1234567890',
        'atas_nama' => 'PT Kalicaa Management',
        'ppn_default' => 11.00,
        'tarif_pln_per_kwh' => 1699.53,
        'faktor_rekening_minimum' => 0.04,
        'persen_loses' => 5.0,
        'persen_ppj' => 5.0,
        'persen_jasa_listrik_air' => 10.0,
        'tarif_kawasan_per_m2' => 2000.0,
        'persen_fee_kawasan' => 4.0,
        'tarif_air_abodemen' => 22000.0,
        'tarif_air_tier1_batas' => 20,
        'tarif_air_tier1' => 9700.0,
        'tarif_air_tier2_batas' => 30,
        'tarif_air_tier2' => 10785.0,
        'tarif_air_tier3' => 11445.0,
    ];
}

$settings = getAppSettings($pdo);

// Request Router
$route = $_GET['route'] ?? 'dashboard';

switch ($route) {
    // ----------------------------------------------------
    // 1. DASHBOARD
    // ----------------------------------------------------
    case 'dashboard':
        $totalPiutang = (float)($pdo->query("SELECT SUM(grand_total) FROM invoices WHERE status_bayar = 'Belum Bayar'")->fetchColumn() ?: 0);
        $totalLunas = (float)($pdo->query("SELECT SUM(grand_total) FROM invoices WHERE status_bayar = 'Lunas'")->fetchColumn() ?: 0);
        $totalAll = $totalPiutang + $totalLunas;
        $collectionRate = $totalAll > 0 ? ($totalLunas / $totalAll) * 100 : 100.0;

        $currMonth = (int)date('m');
        $currYear = (int)date('Y');
        $penerimaanBulanIni = (float)($pdo->query("SELECT SUM(grand_total) FROM invoices WHERE status_bayar = 'Lunas' AND periode_bulan = $currMonth AND periode_tahun = $currYear")->fetchColumn() ?: 0);

        $totalUnit = (int)($pdo->query("SELECT COUNT(*) FROM debiturs")->fetchColumn() ?: 0);
        $totalUnitAktif = (int)($pdo->query("SELECT COUNT(*) FROM debiturs WHERE status = 'aktif'")->fetchColumn() ?: 0);

        $daftarTunggakan = $pdo->query("
            SELECT i.*, d.kode_kav, d.nama_owner as nama_debitur, d.no_hp
            FROM invoices i
            JOIN debiturs d ON i.debitur_id = d.id
            WHERE i.status_bayar = 'Belum Bayar'
            ORDER BY i.periode_tahun DESC, i.periode_bulan DESC, i.id DESC
        ")->fetchAll();

        // 6 months trend
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $ts = strtotime("-$i months");
            $m = (int)date('n', $ts);
            $y = (int)date('Y', $ts);
            $lunasNominal = (float)($pdo->query("SELECT SUM(grand_total) FROM invoices WHERE status_bayar = 'Lunas' AND periode_bulan = $m AND periode_tahun = $y")->fetchColumn() ?: 0);
            $belumNominal = (float)($pdo->query("SELECT SUM(grand_total) FROM invoices WHERE status_bayar = 'Belum Bayar' AND periode_bulan = $m AND periode_tahun = $y")->fetchColumn() ?: 0);

            $chartData[] = [
                'label' => date('M Y', $ts),
                'lunas' => $lunasNominal,
                'belum_bayar' => $belumNominal
            ];
        }

        $stats = [
            'total_piutang' => $totalPiutang,
            'total_penerimaan_bulan_ini' => $penerimaanBulanIni,
            'total_unit' => $totalUnit,
            'total_unit_aktif' => $totalUnitAktif,
            'collection_rate' => $collectionRate,
            'daftar_tunggakan' => $daftarTunggakan,
            'chart_data' => $chartData
        ];

        include __DIR__ . '/../views/dashboard/index.php';
        break;

    // ----------------------------------------------------
    // 2. KARTU PIUTANG (With Dynamic Saldo Berjalan)
    // ----------------------------------------------------
    case 'kartu-piutang':
        $search = $_GET['search'] ?? '';
        $tipeFilter = $_GET['tipe'] ?? 'All';
        $sortField = $_GET['sort'] ?? 'kavling';
        $showZero = !empty($_GET['showZero']);

        $kartuList = KartuPiutangService::getKartuPiutangSemua($pdo, $search, $tipeFilter, $sortField, $showZero);

        include __DIR__ . '/../views/kartu_piutang/index.php';
        break;

    // ----------------------------------------------------
    // 3. GENERATOR TAGIHAN BULANAN
    // ----------------------------------------------------
    case 'tagihan':
        $selectedBulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 6;
        $selectedTahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 2026;
        $selectedDebiturId = isset($_GET['debitur_id']) ? (int)$_GET['debitur_id'] : null;

        $debiturs = $pdo->query("SELECT * FROM debiturs WHERE status = 'aktif' ORDER BY kode_kav ASC")->fetchAll();
        
        $selectedDebitur = null;
        $meteranTerakhir = null;

        if ($selectedDebiturId) {
            $stmt = $pdo->prepare("SELECT * FROM debiturs WHERE id = ?");
            $stmt->execute([$selectedDebiturId]);
            $selectedDebitur = $stmt->fetch();

            if ($selectedDebitur) {
                $meteranTerakhir = CalculationService::getMeteranTerakhir($pdo, $selectedDebitur['id'], $selectedBulan, $selectedTahun);
            }
        }

        include __DIR__ . '/../views/tagihan/index.php';
        break;

    case 'tagihan_proses':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $debiturId = (int)$_POST['debitur_id'];
            $bulan = (int)$_POST['periode_bulan'];
            $tahun = (int)$_POST['periode_tahun'];
            $actionType = $_POST['action_type'] ?? 'invoice';

            $plnAwal = (float)$_POST['meteran_pln_awal'];
            $plnAkhir = (float)$_POST['meteran_pln_akhir'];
            $airAwal = (float)$_POST['meteran_air_awal'];
            $airAkhir = (float)$_POST['meteran_air_akhir'];

            $stmt = $pdo->prepare("SELECT * FROM debiturs WHERE id = ?");
            $stmt->execute([$debiturId]);
            $deb = $stmt->fetch();

            // Hitung Listrik
            $calcListrik = CalculationService::hitungTagihanListrik($plnAwal, $plnAkhir, (float)$deb['watt_listrik'], $settings);
            
            // Hitung Air
            $calcAir = CalculationService::hitungTagihanAir($airAwal, $airAkhir, $settings);

            // Hitung Kawasan
            $calcKawasan = CalculationService::hitungTagihanKawasan((float)$deb['luas_m2_kawasan'], $settings);

            // Subtotals
            $subtotalDpp = $calcListrik['subtotal_dpp'] + $calcAir['subtotal_dpp'] + $calcKawasan['subtotal_dpp']
                         + (float)$deb['landscape_flat'] + (float)$deb['kolam_flat'] + (float)$deb['internet_flat'];
            
            $ppnDefault = (float)($settings['ppn_default'] ?? 11);
            $totalPpn = $calcKawasan['ppn'];
            $grandTotal = $calcListrik['total'] + $calcAir['total'] + $calcKawasan['total']
                        + (float)$deb['landscape_flat'] + (float)$deb['kolam_flat'] + (float)$deb['internet_flat'];

            if ($actionType === 'invoice') {
                $count = (int)$pdo->query("SELECT COUNT(*) FROM invoices WHERE periode_tahun = $tahun AND periode_bulan = $bulan")->fetchColumn() + 1;
                $nomorInvoice = sprintf("IVBW/%d/%02d/%03d", $tahun, $bulan, $count);

                $stmt = $pdo->prepare("
                    INSERT INTO invoices (
                        nomor_invoice, debitur_id, tanggal_terbit, jatuh_tempo,
                        periode_bulan, periode_tahun, subtotal_dpp, total_ppn, grand_total, status_bayar
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Belum Bayar')
                ");
                $tglTerbit = date('Y-m-d');
                $jatuhTempo = date('Y-m-d', strtotime('+14 days'));
                $stmt->execute([
                    $nomorInvoice, $deb['id'], $tglTerbit, $jatuhTempo,
                    $bulan, $tahun, $subtotalDpp, $totalPpn, $grandTotal
                ]);
                $invoiceId = (int)$pdo->lastInsertId();

                // Insert Items
                $itemStmt = $pdo->prepare("
                    INSERT INTO invoice_items (
                        invoice_id, tagihan_bulanan_kategori, keterangan, kode_coa,
                        meteran_pln_awal, meteran_pln_akhir, meteran_air_awal, meteran_air_akhir,
                        pemakaian_volume, nilai_dpp, ppn_persen, total
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                // Item Listrik
                $itemStmt->execute([
                    $invoiceId, 'LISTRIK', "Pemakaian Listrik PLN ({$calcListrik['pemakaian_kwh']} kWh)", '40602.0600.0000',
                    $plnAwal, $plnAkhir, null, null,
                    $calcListrik['pemakaian_kwh'], $calcListrik['subtotal_dpp'], 0, $calcListrik['total']
                ]);

                // Item Air
                $itemStmt->execute([
                    $invoiceId, 'AIR', "Pemakaian Air Bersih PDAM ({$calcAir['pemakaian_m3']} m³)", '40602.0600.0000',
                    null, null, $airAwal, $airAkhir,
                    $calcAir['pemakaian_m3'], $calcAir['subtotal_dpp'], 0, $calcAir['total']
                ]);

                // Item Kawasan
                $itemStmt->execute([
                    $invoiceId, 'KAWASAN', "Iuran Pengelolaan Kawasan ({$deb['luas_m2_kawasan']} m²)", '40602.0100.0000',
                    null, null, null, null,
                    $deb['luas_m2_kawasan'], $calcKawasan['subtotal_dpp'], $ppnDefault, $calcKawasan['total']
                ]);

                if ($deb['internet_flat'] > 0) {
                    $itemStmt->execute([
                        $invoiceId, 'INTERNET', "Layanan Internet Kawasan Dedicated", '40602.0300.0000',
                        null, null, null, null,
                        1, $deb['internet_flat'], 0, $deb['internet_flat']
                    ]);
                }

                header("Location: ?route=invoice");
                exit;

            } else {
                // Simpan Sebagai Draft RVBW
                $count = (int)$pdo->query("SELECT COUNT(*) FROM rvbw")->fetchColumn() + 1;
                $nomorRvbw = sprintf("RVBW%02d%02d%04d", substr((string)$tahun, -2), $bulan, $count);

                $stmt = $pdo->prepare("
                    INSERT INTO rvbw (
                        nomor_rvbw, tanggal, debitur_id, periode_bulan, periode_tahun,
                        subtotal_dpp, total_ppn, grand_total, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Draft')
                ");
                $stmt->execute([
                    $nomorRvbw, date('Y-m-d'), $deb['id'], $bulan, $tahun,
                    $subtotalDpp, $totalPpn, $grandTotal
                ]);

                header("Location: ?route=rvbw");
                exit;
            }
        }
        break;

    // ----------------------------------------------------
    // 4. INVOICES (IVBW)
    // ----------------------------------------------------
    case 'invoice':
        $filterBulan = $_GET['bulan'] ?? '';
        $filterTahun = $_GET['tahun'] ?? '';
        $filterStatus = $_GET['status_bayar'] ?? '';

        $sql = "
            SELECT i.*, d.kode_kav, d.nama_owner as nama_debitur, d.no_hp
            FROM invoices i
            JOIN debiturs d ON i.debitur_id = d.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filterBulan)) {
            $sql .= " AND i.periode_bulan = ?";
            $params[] = (int)$filterBulan;
        }
        if (!empty($filterTahun)) {
            $sql .= " AND i.periode_tahun = ?";
            $params[] = (int)$filterTahun;
        }
        if (!empty($filterStatus)) {
            $sql .= " AND i.status_bayar = ?";
            $params[] = $filterStatus;
        }

        $sql .= " ORDER BY i.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $invoices = $stmt->fetchAll();

        include __DIR__ . '/../views/invoice/index.php';
        break;

    case 'invoice_toggle_status':
        $invId = (int)($_GET['id'] ?? 0);
        $curr = $pdo->query("SELECT status_bayar FROM invoices WHERE id = $invId")->fetchColumn();
        $newStatus = ($curr === 'Lunas') ? 'Belum Bayar' : 'Lunas';
        $tglLunas = ($newStatus === 'Lunas') ? date('Y-m-d') : null;

        $stmt = $pdo->prepare("UPDATE invoices SET status_bayar = ?, tanggal_lunas = ? WHERE id = ?");
        $stmt->execute([$newStatus, $tglLunas, $invId]);

        header("Location: ?route=invoice");
        exit;

    case 'invoice_print':
        $invId = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("
            SELECT i.*, d.kode_kav, d.nama_owner as nama_debitur, d.no_hp, d.alamat_unit
            FROM invoices i
            JOIN debiturs d ON i.debitur_id = d.id
            WHERE i.id = ?
        ");
        $stmt->execute([$invId]);
        $invoice = $stmt->fetch();

        if (!$invoice) {
            die("Invoice tidak ditemukan");
        }

        $stmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
        $stmt->execute([$invId]);
        $items = $stmt->fetchAll();

        include __DIR__ . '/../views/invoice/print.php';
        break;

    case 'invoice_lampiran':
        $invId = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("
            SELECT i.*, d.kode_kav, d.nama_owner as nama_debitur, d.no_hp, d.alamat_unit, d.watt_listrik, d.luas_m2_kawasan
            FROM invoices i
            JOIN debiturs d ON i.debitur_id = d.id
            WHERE i.id = ?
        ");
        $stmt->execute([$invId]);
        $invoice = $stmt->fetch();

        if (!$invoice) {
            die("Invoice tidak ditemukan");
        }

        $stmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
        $stmt->execute([$invId]);
        $items = $stmt->fetchAll();

        $debitur = [
            'kode_kav' => $invoice['kode_kav'],
            'nama_owner' => $invoice['nama_debitur'],
            'watt_listrik' => $invoice['watt_listrik'],
            'luas_m2_kawasan' => $invoice['luas_m2_kawasan'],
            'alamat_unit' => $invoice['alamat_unit']
        ];

        include __DIR__ . '/../views/invoice/lampiran.php';
        break;

    // ----------------------------------------------------
    // 5. MASTER DEBITUR
    // ----------------------------------------------------
    case 'debitur':
        $debiturs = $pdo->query("SELECT * FROM debiturs ORDER BY kode_kav ASC")->fetchAll();
        include __DIR__ . '/../views/debitur/index.php';
        break;

    case 'debitur_store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("
                INSERT INTO debiturs (
                    kode_kav, nama_owner, no_hp, tipe_unit, watt_listrik,
                    luas_m2_kawasan, landscape_flat, kolam_flat, internet_flat, alamat_unit, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'aktif')
            ");
            $stmt->execute([
                $_POST['kode_kav'], $_POST['nama_owner'], $_POST['no_hp'],
                $_POST['tipe_unit'], (float)$_POST['watt_listrik'], (float)$_POST['luas_m2_kawasan'],
                (float)$_POST['landscape_flat'], (float)$_POST['kolam_flat'], (float)$_POST['internet_flat'],
                $_POST['alamat_unit'] ?? ''
            ]);

            header("Location: ?route=debitur");
            exit;
        }
        break;

    // ----------------------------------------------------
    // 6. RVBW (RECEIVABLE VOUCHER)
    // ----------------------------------------------------
    case 'rvbw':
        $rvbwList = $pdo->query("
            SELECT r.*, d.kode_kav, d.nama_owner as nama_debitur
            FROM rvbw r
            JOIN debiturs d ON r.debitur_id = d.id
            ORDER BY r.id DESC
        ")->fetchAll();

        include __DIR__ . '/../views/rvbw/index.php';
        break;

    case 'rvbw_status':
        $rvId = (int)($_GET['id'] ?? 0);
        $newStatus = $_GET['status'] ?? 'Draft';
        $stmt = $pdo->prepare("UPDATE rvbw SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $rvId]);

        header("Location: ?route=rvbw");
        exit;

    // ----------------------------------------------------
    // 7. SKP (SURAT KETERANGAN PEMESANAN UNIT)
    // ----------------------------------------------------
    case 'skp':
        $skpList = $pdo->query("
            SELECT s.*, d.kode_kav, d.nama_owner,
                   s.uang_muka as total_dibayar,
                   (s.nilai_jual - s.uang_muka) as sisa_cicilan
            FROM skp_properties s
            JOIN debiturs d ON s.debitur_id = d.id
            ORDER BY s.id DESC
        ")->fetchAll();

        $debiturs = $pdo->query("SELECT id, kode_kav, nama_owner FROM debiturs WHERE status = 'aktif' ORDER BY kode_kav ASC")->fetchAll();

        include __DIR__ . '/../views/skp/index.php';
        break;

    case 'skp_store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nilaiJual = (float)$_POST['nilai_jual'];
            $uangMuka = (float)$_POST['uang_muka'];
            $tenor = (int)$_POST['tenor_bulan'];
            $angsuran = ($tenor > 0) ? ($nilaiJual - $uangMuka) / $tenor : 0;

            $stmt = $pdo->prepare("
                INSERT INTO skp_properties (
                    nomor_skp, debitur_id, kode_kav, nama_owner, tanggal_skp, nilai_jual, uang_muka,
                    tenor_bulan, nilai_angsuran_bulanan, tanggal_mulai_cicilan, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Berjalan')
            ");

            $debId = (int)$_POST['debitur_id'];
            $deb = $pdo->query("SELECT kode_kav, nama_owner FROM debiturs WHERE id = $debId")->fetch();

            $stmt->execute([
                $_POST['nomor_skp'], $debId, $deb['kode_kav'] ?? '', $deb['nama_owner'] ?? '', $_POST['tanggal_skp'],
                $nilaiJual, $uangMuka, $tenor, $angsuran, $_POST['tanggal_mulai_cicilan']
            ]);

            header("Location: ?route=skp");
            exit;
        }
        break;

    // ----------------------------------------------------
    // 8. SPK KONTRAKTOR
    // ----------------------------------------------------
    case 'spk':
        $spkList = $pdo->query("
            SELECT s.*,
                   COALESCE((SELECT SUM(nilai_pembayaran) FROM spk_payments WHERE spk_id = s.id), 0) as total_dibayar
            FROM spk_records s
            ORDER BY s.id DESC
        ")->fetchAll();

        include __DIR__ . '/../views/spk/index.php';
        break;

    case 'spk_store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("
                INSERT INTO spk_records (
                    nomor_spk, tanggal_spk, nama_kontraktor, pic_pekerjaan,
                    nominal_kontrak, pph_persen, keterangan, status_manual
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Belum Selesai')
            ");
            $stmt->execute([
                $_POST['nomor_spk'], $_POST['tanggal_spk'], $_POST['nama_kontraktor'],
                $_POST['pic_pekerjaan'], (float)$_POST['nominal_kontrak'], (float)$_POST['pph_persen'],
                $_POST['keterangan']
            ]);

            header("Location: ?route=spk");
            exit;
        }
        break;

    case 'spk_payment_store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("
                INSERT INTO spk_payments (
                    spk_id, tahapan, nomor_voucher, tanggal_pembayaran, nilai_pembayaran
                ) VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                (int)$_POST['spk_id'], $_POST['tahapan'], $_POST['nomor_voucher'],
                $_POST['tanggal_pembayaran'], (float)$_POST['nilai_pembayaran']
            ]);

            header("Location: ?route=spk");
            exit;
        }
        break;

    // ----------------------------------------------------
    // 9. TITIPAN DANA (RVBW / PVBW)
    // ----------------------------------------------------
    case 'titipan':
        $titipanList = $pdo->query("
            SELECT t.*,
                   t.nomor_rvbw as nomor,
                   COALESCE((SELECT SUM(nominal) FROM titipan_pvbw WHERE titipan_rvbw_id = t.id), 0) as total_dikembalikan,
                   (t.nominal - COALESCE((SELECT SUM(nominal) FROM titipan_pvbw WHERE titipan_rvbw_id = t.id), 0)) as sisa_titipan
            FROM titipan_rvbw t
            ORDER BY t.id DESC
        ")->fetchAll();

        $totalTitipanNominal = 0;
        $totalDikembalikanNominal = 0;
        $totalSisaNominal = 0;

        foreach ($titipanList as $t) {
            $totalTitipanNominal += $t['nominal'];
            $totalDikembalikanNominal += $t['total_dikembalikan'];
            $totalSisaNominal += $t['sisa_titipan'];
        }

        include __DIR__ . '/../views/titipan/index.php';
        break;

    case 'titipan_store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nomor = strtoupper(trim($_POST['nomor']));
            $entitas = str_starts_with($nomor, 'RVTL') ? 'TLLI' : 'BWJ';

            $stmt = $pdo->prepare("
                INSERT INTO titipan_rvbw (
                    nomor_rvbw, entitas, tanggal_terima, nominal, keterangan, status
                ) VALUES (?, ?, ?, ?, ?, 'Aktif')
            ");
            $stmt->execute([
                $nomor, $entitas, $_POST['tanggal_terima'],
                (float)$_POST['nominal'], $_POST['keterangan']
            ]);

            header("Location: ?route=titipan");
            exit;
        }
        break;

    case 'titipan_kembali_store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titipanId = (int)$_POST['rvbw_id'];
            $stmt = $pdo->prepare("
                INSERT INTO titipan_pvbw (
                    titipan_rvbw_id, nomor_pvbw, tanggal_kembali, nominal, keterangan
                ) VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $titipanId, strtoupper($_POST['nomor']), $_POST['tanggal_kembali'],
                (float)$_POST['nominal'], $_POST['keterangan']
            ]);

            $totTitipan = (float)$pdo->query("SELECT nominal FROM titipan_rvbw WHERE id = $titipanId")->fetchColumn();
            $totKembali = (float)$pdo->query("SELECT SUM(nominal) FROM titipan_pvbw WHERE titipan_rvbw_id = $titipanId")->fetchColumn();
            if ($totKembali >= $totTitipan) {
                $pdo->exec("UPDATE titipan_rvbw SET status = 'Selesai' WHERE id = $titipanId");
            }

            header("Location: ?route=titipan");
            exit;
        }
        break;

    // ----------------------------------------------------
    // 10. SETTINGS
    // ----------------------------------------------------
    case 'settings':
        include __DIR__ . '/../views/settings/index.php';
        break;

    case 'settings_save':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fields = [];
            $values = [];
            foreach ($_POST as $key => $val) {
                if ($key === 'route') continue;
                $fields[] = "`$key` = ?";
                $values[] = $val;
            }
            if (!empty($fields)) {
                $sql = "UPDATE app_settings SET " . implode(', ', $fields) . " WHERE id = 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($values);
            }
            header("Location: ?route=settings");
            exit;
        }
        break;

    // ----------------------------------------------------
    // 11. BACKUP & EXCEL SYNC (Super Admin Only)
    // ----------------------------------------------------
    case 'backup':
        include __DIR__ . '/../views/backup/index.php';
        break;

    case 'backup_export_excel':
        if (($_SESSION['user']['role'] ?? '') !== 'Super Admin') {
            http_response_code(403);
            die("Akses Ditolak: Hanya Super Admin yang berhak mengunduh file backup Excel.");
        }
        $xml = BackupService::generateExcelXml($pdo);
        $filename = 'KALICAA_BACKUP_ALL_MODULES_' . date('Y-m-d_His') . '.xlsx';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Content-Length: ' . strlen($xml));
        echo $xml;
        exit;

    case 'backup_import_excel':
        if (($_SESSION['user']['role'] ?? '') !== 'Super Admin') {
            http_response_code(403);
            die("Akses Ditolak: Hanya Super Admin yang berhak memulihkan database.");
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['backup_file'])) {
            $file = $_FILES['backup_file'];
            $mode = $_POST['mode'] ?? 'replace';

            if ($file['error'] === UPLOAD_ERR_OK) {
                $content = file_get_contents($file['tmp_name']);
                $parsedData = [];

                // Try parsing JSON first
                $json = json_decode($content, true);
                if (is_array($json) && (!empty($json['debiturs']) || !empty($json['invoices']))) {
                    $parsedData = $json;
                } else {
                    // Try parsing Excel XML Spreadsheet
                    try {
                        $xml = simplexml_load_string($content);
                        if ($xml && isset($xml->Worksheet)) {
                            foreach ($xml->Worksheet as $ws) {
                                $sheetName = strtolower((string)$ws['Name']);
                                $rows = [];
                                $headers = [];
                                if (isset($ws->Table->Row)) {
                                    $rowIndex = 0;
                                    foreach ($ws->Table->Row as $row) {
                                        $cellValues = [];
                                        foreach ($row->Cell as $cell) {
                                            $cellValues[] = (string)($cell->Data ?? '');
                                        }
                                        if ($rowIndex === 0) {
                                            $headers = array_map('strtolower', $cellValues);
                                        } else {
                                            if (!empty($headers) && !empty($cellValues)) {
                                                $rowData = [];
                                                foreach ($headers as $idx => $h) {
                                                    if (!empty($h)) {
                                                        $rowData[$h] = $cellValues[$idx] ?? '';
                                                    }
                                                }
                                                $rows[] = $rowData;
                                            }
                                        }
                                        $rowIndex++;
                                    }
                                }
                                $parsedData[$sheetName] = $rows;
                            }
                        }
                    } catch (\Exception $e) {
                        // XML parse failed
                    }
                }

                if (!empty($parsedData)) {
                    $result = BackupService::restoreFromData($pdo, $parsedData, $mode);
                    if ($result['success']) {
                        $details = [];
                        foreach ($result['stats'] as $k => $c) {
                            $details[] = "$c $k";
                        }
                        $_SESSION['flash_success'] = "Restore database berhasil: " . implode(', ', $details) . ".";
                    } else {
                        $_SESSION['flash_error'] = "Gagal memulihkan database: " . ($result['error'] ?? 'Terjadi kesalahan');
                    }
                } else {
                    $_SESSION['flash_error'] = "Format file backup tidak dikenali atau kosong. Harap unggah file Excel XML atau JSON hasil ekspor sistem.";
                }
            } else {
                $_SESSION['flash_error'] = "Gagal mengunggah file backup (Error code: " . $file['error'] . ").";
            }
        }
        header("Location: ?route=backup");
        exit;

    case 'api_sync':
        $rawInput = file_get_contents('php://input');
        $payload = json_decode($rawInput, true) ?: [];
        $mode = $_GET['mode'] ?? 'replace';
        $result = BackupService::restoreFromData($pdo, $payload, $mode);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;

    case 'api_export_json':
        $data = BackupService::getAllData($pdo);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;

    // ----------------------------------------------------
    // IMPORT DATA DENGAN MODE SQL (Upsert vs Skip)
    // ----------------------------------------------------
    case 'import':
        include __DIR__ . '/../views/import/index.php';
        break;

    case 'api_import_data':
        header('Content-Type: application/json; charset=utf-8');
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            $importMode  = $input['import_mode'] ?? $_POST['import_mode'] ?? 'upsert';
            $targetTable = $input['target_table'] ?? $_POST['target_table'] ?? 'debiturs';
            $uniqueKey   = $input['unique_key'] ?? $_POST['unique_key'] ?? 'kode_kav';

            if (isset($input['data_rows']) && is_array($input['data_rows'])) {
                $rowsData = $input['data_rows'];
            } elseif (isset($_POST['data_rows'])) {
                $rowsData = is_array($_POST['data_rows']) ? $_POST['data_rows'] : (json_decode($_POST['data_rows'], true) ?: []);
            } else {
                $rowsData = [];
            }

            $result = ImportService::executeImport($pdo, $targetTable, $uniqueKey, $importMode, $rowsData);
            echo json_encode($result);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Internal Server Error: ' . $e->getMessage(),
                'stats' => ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'total' => 0]
            ]);
        }
        exit;

    case 'role_switch':
        $newRole = $_GET['role'] ?? 'Super Admin';
        $_SESSION['user']['role'] = $newRole;
        $_SESSION['flash_success'] = "Role aktif sesi PHP dialihkan ke: " . htmlspecialchars($newRole);
        header("Location: ?route=backup");
        exit;

    default:
        echo "404 Halaman Tidak Ditemukan";
        break;
}
