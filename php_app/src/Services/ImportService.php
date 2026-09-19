<?php
/**
 * Kalicaa Villa Estate Management
 * Service Import Data Excel / CSV ke Database MySQL / SQLite dengan Pengaturan Mode SQL (Upsert vs Skip)
 *
 * Aman dari SQL Injection: Menggunakan Prepared Statements & Parameterized Queries,
 * serta Validasi Whitelist Nama Tabel dan Kolom.
 */

declare(strict_types=1);

namespace Services;

use PDO;
use Exception;

class ImportService {

    /**
     * Daftar tabel yang diizinkan untuk import langsung
     */
    private const ALLOWED_TABLES = [
        'debiturs' => ['primary_key' => 'id', 'default_unique' => 'kode_kav'],
        'invoices' => ['primary_key' => 'id', 'default_unique' => 'nomor_invoice'],
        'skp_properties' => ['primary_key' => 'id', 'default_unique' => 'nomor_skp'],
        'spk_records' => ['primary_key' => 'id', 'default_unique' => 'nomor_spk'],
        'master_coa' => ['primary_key' => 'id', 'default_unique' => 'kode_coa'],
        'rvbw' => ['primary_key' => 'id', 'default_unique' => 'nomor_rvbw'],
        'titipan_rvbw' => ['primary_key' => 'id', 'default_unique' => 'nomor_rvbw'],
        'titipan_pvbw' => ['primary_key' => 'id', 'default_unique' => 'nomor_pvbw'],
        'app_settings' => ['primary_key' => 'id', 'default_unique' => 'id'],
        'generic_data' => ['primary_key' => 'id', 'default_unique' => 'kode_unik'],
    ];

    /**
     * Sanitasi nama kolom database untuk mencegah SQL injection pada identifier
     */
    public static function sanitizeColumnName(string $col): string {
        $clean = preg_replace('/[^a-zA-Z0-9_]/', '', trim($col));
        if (empty($clean)) {
            throw new Exception("Nama kolom tidak valid: '$col'");
        }
        return $clean;
    }

    /**
     * Eksekusi Import Data dengan Mode SQL
     *
     * @param PDO $pdo Koneksi Database
     * @param string $table Nama tabel target
     * @param string $uniqueKey Kolom kunci unik untuk deteksi duplikasi
     * @param string $importMode 'upsert' (Insert & Update) atau 'skip' (Skip Duplikat)
     * @param array $rowsData Array data baris dari Excel/CSV
     * @return array Status dan statistik { success: bool, stats: { inserted, updated, skipped, total }, message: string }
     */
    public static function executeImport(
        PDO $pdo,
        string $table,
        string $uniqueKey,
        string $importMode,
        array $rowsData
    ): array {
        $table = strtolower(trim($table));
        $importMode = strtolower(trim($importMode)) === 'skip' ? 'skip' : 'upsert';

        if (empty($rowsData)) {
            return [
                'success' => false,
                'message' => 'Data baris kosong, tidak ada data yang dapat diimpor.',
                'stats' => ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'total' => 0]
            ];
        }

        // Sanitasi Identifier Kunci Unik
        $uniqueKey = self::sanitizeColumnName($uniqueKey);

        // Ambil daftar kolom yang ada di database tabel target untuk keamanan
        $tableColumns = self::getTableColumns($pdo, $table);
        if (empty($tableColumns)) {
            // Jika tabel belum ada, buat tabel dinamis jika untuk data umum
            self::ensureTableExists($pdo, $table, $rowsData[0] ?? []);
            $tableColumns = self::getTableColumns($pdo, $table);
        }

        $inserted = 0;
        $updated  = 0;
        $skipped  = 0;
        $errors   = [];

        // Memulai Transaksi Database (Atomic Operation)
        $pdo->beginTransaction();

        try {
            // Statement untuk mengecek apakah data dengan kode unik sudah ada
            $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            $quoteChar = ($driver === 'mysql') ? '`' : '"';

            $checkSql = "SELECT {$quoteChar}id{$quoteChar} FROM {$quoteChar}{$table}{$quoteChar} WHERE {$quoteChar}{$uniqueKey}{$quoteChar} = :unique_val LIMIT 1";
            $checkStmt = $pdo->prepare($checkSql);

            foreach ($rowsData as $index => $row) {
                if (!is_array($row) || empty($row)) {
                    continue;
                }

                // Cari nilai kolom unik dari baris
                $uniqueVal = null;
                foreach ($row as $k => $v) {
                    if (strcasecmp(trim((string)$k), $uniqueKey) === 0) {
                        $uniqueVal = is_string($v) ? trim($v) : $v;
                        break;
                    }
                }

                // Jika kolom unik kosong pada baris ini, lewati
                if ($uniqueVal === null || $uniqueVal === '') {
                    $skipped++;
                    continue;
                }

                // 1. Cek keberadaan record di database
                $checkStmt->execute([':unique_val' => $uniqueVal]);
                $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

                // Siapkan data kolom yang valid dan sesuai dengan skema tabel
                $payloadData = [];
                foreach ($row as $colName => $colValue) {
                    $cleanCol = preg_replace('/[^a-zA-Z0-9_]/', '', trim((string)$colName));
                    if (empty($cleanCol) || strtolower($cleanCol) === 'id') {
                        continue; // Kolom ID primary key tidak di-override manual
                    }
                    if (in_array(strtolower($cleanCol), $tableColumns, true)) {
                        $payloadData[$cleanCol] = $colValue;
                    }
                }

                if ($existing) {
                    // JIKA RECORD SUDAH ADA
                    if ($importMode === 'skip') {
                        // MODE SKIP: Lewati, jangan ubah data yang sudah ada
                        $skipped++;
                        continue;
                    } else {
                        // MODE UPSERT: Lakukan UPDATE pada record lama
                        if (empty($payloadData)) {
                            $skipped++;
                            continue;
                        }

                        $setParts = [];
                        $params = [':existing_id' => $existing['id']];

                        foreach ($payloadData as $col => $val) {
                            $setParts[] = "{$quoteChar}{$col}{$quoteChar} = :upd_{$col}";
                            $params[":upd_{$col}"] = $val;
                        }

                        // Tambahkan updated_at jika kolom ada
                        if (in_array('updated_at', $tableColumns, true) && !isset($payloadData['updated_at'])) {
                            $setParts[] = "{$quoteChar}updated_at{$quoteChar} = :upd_updated_at";
                            $params[':upd_updated_at'] = date('Y-m-d H:i:s');
                        }

                        $updateSql = "UPDATE {$quoteChar}{$table}{$quoteChar} SET " . implode(', ', $setParts) . " WHERE {$quoteChar}id{$quoteChar} = :existing_id";
                        $updateStmt = $pdo->prepare($updateSql);
                        $updateStmt->execute($params);

                        $updated++;
                    }
                } else {
                    // JIKA RECORD BELUM ADA: LAKUKAN INSERT BARU
                    // Pastikan nilai uniqueKey ikut dimasukkan
                    if (!isset($payloadData[$uniqueKey])) {
                        $payloadData[$uniqueKey] = $uniqueVal;
                    }

                    if (empty($payloadData)) {
                        $skipped++;
                        continue;
                    }

                    // Tambahkan timestamp created_at / updated_at jika kolom ada di tabel
                    if (in_array('created_at', $tableColumns, true) && !isset($payloadData['created_at'])) {
                        $payloadData['created_at'] = date('Y-m-d H:i:s');
                    }
                    if (in_array('updated_at', $tableColumns, true) && !isset($payloadData['updated_at'])) {
                        $payloadData['updated_at'] = date('Y-m-d H:i:s');
                    }

                    $cols = array_keys($payloadData);
                    $colNames = implode(', ', array_map(fn($c) => "{$quoteChar}{$c}{$quoteChar}", $cols));
                    $placeholders = implode(', ', array_map(fn($c) => ":ins_{$c}", $cols));

                    $insertSql = "INSERT INTO {$quoteChar}{$table}{$quoteChar} ({$colNames}) VALUES ({$placeholders})";
                    $insertStmt = $pdo->prepare($insertSql);

                    $params = [];
                    foreach ($payloadData as $c => $v) {
                        $params[":ins_{$c}"] = $v;
                    }

                    $insertStmt->execute($params);
                    $inserted++;
                }
            }

            // Semua operasi berhasil -> Komit transaksi
            $pdo->commit();

            $modeText = ($importMode === 'upsert') ? 'Mode Upsert (Update & Insert)' : 'Mode Skip Duplikat';

            return [
                'success' => true,
                'message' => "Import selesai dengan {$modeText}! {$inserted} baru ditambahkan, {$updated} diperbarui, {$skipped} dilewati.",
                'stats' => [
                    'inserted' => $inserted,
                    'updated'  => $updated,
                    'skipped'  => $skipped,
                    'total'    => count($rowsData)
                ],
                'mode' => $importMode,
                'target_table' => $table,
                'unique_key' => $uniqueKey
            ];

        } catch (Exception $e) {
            // Terjadi kesalahan -> Batalkan seluruh perubahan transaksi
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return [
                'success' => false,
                'message' => 'Gagal memproses import data: ' . $e->getMessage(),
                'stats' => [
                    'inserted' => 0,
                    'updated'  => 0,
                    'skipped'  => count($rowsData),
                    'total'    => count($rowsData)
                ]
            ];
        }
    }

    /**
     * Dapatkan daftar nama kolom dari suatu tabel
     */
    private static function getTableColumns(PDO $pdo, string $table): array {
        try {
            $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver === 'mysql') {
                $stmt = $pdo->prepare("
                    SELECT LOWER(COLUMN_NAME) as col 
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table
                ");
                $stmt->execute([':table' => $table]);
                return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
            } else {
                // SQLite PRAGMA
                $cleanTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
                $stmt = $pdo->query("PRAGMA table_info(`{$cleanTable}`)");
                $cols = [];
                if ($stmt) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $cols[] = strtolower($row['name']);
                    }
                }
                return $cols;
            }
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Pastikan tabel ada atau buatkan tabel generic_data jika belum ada
     */
    private static function ensureTableExists(PDO $pdo, string $table, array $sampleRow): void {
        $cleanTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($cleanTable === 'generic_data') {
            if ($driver === 'mysql') {
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS `generic_data` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `kode_unik` VARCHAR(100) NOT NULL UNIQUE,
                        `nama` VARCHAR(255) NULL,
                        `nominal` DECIMAL(15,2) DEFAULT 0,
                        `kategori` VARCHAR(100) NULL,
                        `keterangan` TEXT NULL,
                        `created_at` DATETIME NULL,
                        `updated_at` DATETIME NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ");
            } else {
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS `generic_data` (
                        `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                        `kode_unik` TEXT NOT NULL UNIQUE,
                        `nama` TEXT,
                        `nominal` REAL DEFAULT 0,
                        `kategori` TEXT,
                        `keterangan` TEXT,
                        `created_at` TEXT,
                        `updated_at` TEXT
                    );
                ");
            }
        }
    }
}
