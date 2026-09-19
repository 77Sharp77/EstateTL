<?php
/**
 * Kalicaa Villa - Database Configuration & PDO Connector
 * Mendukung MySQL (Production/Hosting) dengan auto-detect .env dan fallback SQLite portabel.
 */

namespace Config;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    /**
     * Memuat file .env sederhana jika ada (tanpa dependensi composer)
     */
    private static function loadEnv(): void {
        $envPaths = [
            __DIR__ . '/../.env',
            __DIR__ . '/../../.env',
            dirname(__DIR__) . '/.env'
        ];

        foreach ($envPaths as $path) {
            if (file_exists($path) && is_readable($path)) {
                $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '' || str_starts_with($line, '#')) {
                        continue;
                    }
                    if (strpos($line, '=') !== false) {
                        [$name, $val] = explode('=', $line, 2);
                        $name = trim($name);
                        $val = trim($val, " \t\n\r\0\x0B\"'");
                        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                            putenv("{$name}={$val}");
                            $_ENV[$name] = $val;
                            $_SERVER[$name] = $val;
                        }
                    }
                }
                break;
            }
        }
    }

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            self::loadEnv();

            // =====================================================================
            // PENGATURAN KREDENSIAL DATABASE MYSQL HOSTING
            // Anda bisa mengatur via .env ATAU ubah langsung nilai default di bawah ini:
            // =====================================================================
            $driver   = getenv('DB_DRIVER') ?: 'mysql';
            $host     = getenv('DB_HOST') ?: '195.88.211.212';
            $port     = getenv('DB_PORT') ?: '3306';
            $dbname   = getenv('DB_NAME') ?: 'docutrac_estate.tanjunglesung.com';
            $user     = getenv('DB_USER') ?: 'docutrac_Jo';
            $password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'tanjunglesung7';

            try {
                if ($driver === 'sqlite') {
                    $sqlitePath = __DIR__ . '/../database/database.sqlite';
                    self::$instance = new PDO("sqlite:" . $sqlitePath);
                    self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                } else {
                    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
                    self::$instance = new PDO($dsn, $user, $password, [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]);
                }
            } catch (PDOException $e) {
                // Jika MySQL gagal terhubung, periksa apakah fallback SQLite tersedia
                $sqlitePath = __DIR__ . '/../database/database.sqlite';
                if (file_exists($sqlitePath)) {
                    try {
                        self::$instance = new PDO("sqlite:" . $sqlitePath);
                        self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                        return self::$instance;
                    } catch (PDOException $sqliteEx) {
                        // Lanjut ke lempar error MySQL asli di bawah
                    }
                }

                // Tampilkan pesan error ramah konfigurasi jika koneksi MySQL bermasalah
                throw new PDOException(
                    "Koneksi Database Gagal: " . $e->getMessage() . 
                    ". Silakan periksa pengaturan DB_HOST, DB_NAME, DB_USER, dan DB_PASS di 'php_app/config/Database.php' atau file '.env'."
                );
            }
        }
        return self::$instance;
    }
}
