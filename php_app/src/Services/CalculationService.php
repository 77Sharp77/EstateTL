<?php
/**
 * Kalicaa Villa - Calculation Service
 * Menampung seluruh formula matematis resmi Estate Management:
 * Listrik (Rekening Minimum vs Aktual, Loses, PPJ, Jasa),
 * Air PDAM (Abodemen, Tier 1, Tier 2, Tier 3, Jasa),
 * Kawasan (Luas m2, Fee), Format Penomoran, dan Terbilang.
 */

namespace Services;

class CalculationService {

    /**
     * Hitung tagihan listrik PLN
     * Rumus resmi sheet "Lampiran":
     * - RM dikenakan jika pemakaian <= ambang_rm_kwh (watt * faktor_rm)
     * - Loses HANYA dikenakan jika pemakaian melebihi ambang RM (aktual). Jika RM terpakai, loses = 0.
     * - PPJ = (tagihan_dasar + loses) * % PPJ
     * - Jasa = Subtotal * % Jasa
     */
    public static function hitungTagihanListrik(float $meteranAwal, float $meteranAkhir, float $wattListrik, array $settings): array {
        $pemakaian_kwh = max(0.0, $meteranAkhir - $meteranAwal);
        $faktor_rm = (float)($settings['faktor_rekening_minimum'] ?? 0.04);
        $tarif_kwh = (float)($settings['tarif_pln_per_kwh'] ?? 1699.53);
        $persen_loses = (float)($settings['persen_loses'] ?? 5.0);
        $persen_ppj = (float)($settings['persen_ppj'] ?? 5.0);
        $persen_jasa = (float)($settings['persen_jasa_listrik_air'] ?? 10.0);

        $ambang_rm_kwh = $wattListrik * $faktor_rm;
        $rm_terpakai = $pemakaian_kwh <= $ambang_rm_kwh;

        $tagihan_dasar = $rm_terpakai 
            ? ($ambang_rm_kwh * $tarif_kwh)
            : ($pemakaian_kwh * $tarif_kwh);

        $biaya_loses = $rm_terpakai ? 0.0 : ($tagihan_dasar * ($persen_loses / 100.0));
        $biaya_ppj   = ($tagihan_dasar + $biaya_loses) * ($persen_ppj / 100.0);
        $subtotal    = $tagihan_dasar + $biaya_loses + $biaya_ppj;
        $jasa        = $subtotal * ($persen_jasa / 100.0);
        $total       = $subtotal + $jasa;

        return [
            'pemakaian_kwh' => round($pemakaian_kwh, 2),
            'ambang_rm_kwh' => round($ambang_rm_kwh, 2),
            'rm_terpakai'   => $rm_terpakai,
            'tagihan_dasar' => round($tagihan_dasar, 2),
            'biaya_loses'   => round($biaya_loses, 2),
            'biaya_ppj'     => round($biaya_ppj, 2),
            'subtotal'      => round($subtotal, 2),
            'jasa'          => round($jasa, 2),
            'total'         => round($total, 2)
        ];
    }

    /**
     * Hitung tagihan air PDAM berjenjang (tiering)
     * Tier 1: 0 s.d. batas tier 1 (default 20 m3)
     * Tier 2: batas tier 1 s.d. batas tier 2 (default 21-30 m3)
     * Tier 3: di atas batas tier 2 (> 30 m3)
     */
    public static function hitungTagihanAir(float $meteranAwal, float $meteranAkhir, array $settings): array {
        $pemakaian_m3 = max(0.0, $meteranAkhir - $meteranAwal);
        $b1 = (float)($settings['tarif_air_tier1_batas'] ?? 20);
        $b2 = (float)($settings['tarif_air_tier2_batas'] ?? 30);

        $vol_tier1 = min($pemakaian_m3, $b1);
        $vol_tier2 = min(max($pemakaian_m3 - $b1, 0.0), $b2 - $b1);
        $vol_tier3 = max($pemakaian_m3 - $b2, 0.0);

        $biaya_abodemen = (float)($settings['tarif_air_abodemen'] ?? 22000.0);
        $biaya_tier1    = $vol_tier1 * (float)($settings['tarif_air_tier1'] ?? 9700.0);
        $biaya_tier2    = $vol_tier2 * (float)($settings['tarif_air_tier2'] ?? 10785.0);
        $biaya_tier3    = $vol_tier3 * (float)($settings['tarif_air_tier3'] ?? 11445.0);

        $subtotal = $biaya_abodemen + $biaya_tier1 + $biaya_tier2 + $biaya_tier3;
        $jasa     = $subtotal * ((float)($settings['persen_jasa_listrik_air'] ?? 10.0) / 100.0);
        $total    = $subtotal + $jasa;

        return [
            'pemakaian_m3'   => round($pemakaian_m3, 2),
            'vol_tier1'      => round($vol_tier1, 2),
            'vol_tier2'      => round($vol_tier2, 2),
            'vol_tier3'      => round($vol_tier3, 2),
            'biaya_abodemen' => round($biaya_abodemen, 2),
            'biaya_tier1'    => round($biaya_tier1, 2),
            'biaya_tier2'    => round($biaya_tier2, 2),
            'biaya_tier3'    => round($biaya_tier3, 2),
            'subtotal'       => round($subtotal, 2),
            'jasa'           => round($jasa, 2),
            'total'          => round($total, 2)
        ];
    }

    /**
     * Hitung tagihan kawasan
     */
    public static function hitungTagihanKawasan(float $luasM2, array $settings): array {
        $tarif = (float)($settings['tarif_kawasan_per_m2'] ?? 2000.0);
        $feePct = (float)($settings['persen_fee_kawasan'] ?? 4.0);

        $total_sebelum_fee = $luasM2 * $tarif;
        $fee = $total_sebelum_fee * ($feePct / 100.0);
        $total = $total_sebelum_fee + $fee;

        return [
            'luas_m2'           => round($luasM2, 2),
            'total_sebelum_fee' => round($total_sebelum_fee, 2),
            'fee'               => round($fee, 2),
            'total'             => round($total, 2)
        ];
    }

    /**
     * Konversi angka bulan 1-12 ke Romawi
     */
    public static function toRomanMonth(int $bulan): string {
        $romans = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        return $romans[$bulan] ?? 'I';
    }

    /**
     * Generate format nomor invoice: 001/EM-1010/V/2026
     */
    public static function generateNomorInvoice(int $counter, string $kodeKav, int $bulan, int $tahun): string {
        $counterStr = str_pad((string)$counter, 3, '0', STR_PAD_LEFT);
        $cleanKav = strtoupper(str_starts_with(strtoupper($kodeKav), 'EM-') ? $kodeKav : "EM-{$kodeKav}");
        $bulanRomawi = self::toRomanMonth($bulan);
        return "{$counterStr}/{$cleanKav}/{$bulanRomawi}/{$tahun}";
    }

    /**
     * Generate format nomor direct invoice IVBW: IVBW26050001
     */
    public static function generateNomorIVBW(int $nextSeq, ?\DateTime $date = null): string {
        $date = $date ?? new \DateTime();
        $yy = $date->format('y');
        $mm = $date->format('m');
        $seq = str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);
        return "IVBW{$yy}{$mm}{$seq}";
    }

    /**
     * Generate format nomor voucher RVBW: RVBW2605001
     */
    public static function generateNomorRVBW(int $nextSeq, string $prefix = 'RVBW', ?\DateTime $date = null): string {
        $date = $date ?? new \DateTime();
        $yy = $date->format('y');
        $mm = $date->format('m');
        $seq = str_pad((string)$nextSeq, 3, '0', STR_PAD_LEFT);
        return "{$prefix}{$yy}{$mm}{$seq}";
    }

    /**
     * Fungsi Terbilang Rupiah
     */
    public static function terbilang(float $number): string {
        $number = abs($number);
        $huruf = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
        $temp = "";

        if ($number < 12) {
            $temp = " " . $huruf[(int)$number];
        } else if ($number < 20) {
            $temp = self::terbilang($number - 10) . " Belas";
        } else if ($number < 100) {
            $temp = self::terbilang(floor($number / 10)) . " Puluh" . self::terbilang($number % 10);
        } else if ($number < 200) {
            $temp = " Seratus" . self::terbilang($number - 100);
        } else if ($number < 1000) {
            $temp = self::terbilang(floor($number / 100)) . " Ratus" . self::terbilang($number % 100);
        } else if ($number < 2000) {
            $temp = " Seribu" . self::terbilang($number - 1000);
        } else if ($number < 1000000) {
            $temp = self::terbilang(floor($number / 1000)) . " Ribu" . self::terbilang($number % 1000);
        } else if ($number < 1000000000) {
            $temp = self::terbilang(floor($number / 1000000)) . " Juta" . self::terbilang($number % 1000000);
        } else if ($number < 1000000000000) {
            $temp = self::terbilang(floor($number / 1000000000)) . " Milyar" . self::terbilang(fmod($number, 1000000000));
        } else {
            $temp = self::terbilang(floor($number / 1000000000000)) . " Triliun" . self::terbilang(fmod($number, 1000000000000));
        }

        return trim($temp) . " Rupiah";
    }
}
