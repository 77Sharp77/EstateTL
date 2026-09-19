<?php
/**
 * Kalicaa Villa - Formatting Helper
 */

namespace Helpers;

class Format {
    public static function rupiah(float|int|null $amount): string {
        $val = (float)($amount ?? 0);
        return 'Rp ' . number_format($val, 0, ',', '.');
    }

    public static function number(float|int|null $num, int $decimals = 2): string {
        $val = (float)($num ?? 0);
        return number_format($val, $decimals, ',', '.');
    }

    public static function tanggalIndo(?string $dateStr): string {
        if (!$dateStr) return '-';
        $time = strtotime($dateStr);
        if (!$time) return '-';

        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $d = date('d', $time);
        $m = (int)date('m', $time);
        $y = date('Y', $time);

        return "{$d} " . ($bulan[$m] ?? '') . " {$y}";
    }

    public static function badgeStatusBayar(string $status): string {
        if ($status === 'Lunas') {
            return '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">LUNAS</span>';
        }
        return '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">BELUM BAYAR</span>';
    }

    public static function badgeStatusRVBW(string $status): string {
        if ($status === 'Posted') {
            return '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">POSTED</span>';
        } elseif ($status === 'Checking') {
            return '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">CHECKING</span>';
        }
        return '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-700/50 text-slate-300 border border-slate-600/30">DRAFT</span>';
    }
}
