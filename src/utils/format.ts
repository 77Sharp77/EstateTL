export function formatRupiah(amount: number): string {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount || 0);
}

export function formatNumber(amount: number, decimals: number = 0): string {
  return new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals,
  }).format(amount || 0);
}

export function formatDateIndo(dateStr: string): string {
  if (!dateStr) return '-';
  try {
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    const months = [
      'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
  } catch {
    return dateStr;
  }
}

export function toRomanMonth(month: number): string {
  const romans: Record<number, string> = {
    1: 'I', 2: 'II', 3: 'III', 4: 'IV',
    5: 'V', 6: 'VI', 7: 'VII', 8: 'VIII',
    9: 'IX', 10: 'X', 11: 'XI', 12: 'XII'
  };
  return romans[month] || 'I';
}

export function terbilang(number: number): string {
  const n = Math.abs(Math.floor(number));
  const huruf = [
    '', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima',
    'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'
  ];

  function convert(val: number): string {
    if (val < 12) {
      return ' ' + huruf[val];
    } else if (val < 20) {
      return convert(val - 10) + ' Belas';
    } else if (val < 100) {
      return convert(Math.floor(val / 10)) + ' Puluh' + convert(val % 10);
    } else if (val < 200) {
      return ' Seratus' + convert(val - 100);
    } else if (val < 1000) {
      return convert(Math.floor(val / 100)) + ' Ratus' + convert(val % 100);
    } else if (val < 2000) {
      return ' Seribu' + convert(val - 1000);
    } else if (val < 1000000) {
      return convert(Math.floor(val / 1000)) + ' Ribu' + convert(val % 1000);
    } else if (val < 1000000000) {
      return convert(Math.floor(val / 1000000)) + ' Juta' + convert(val % 1000000);
    } else if (val < 1000000000000) {
      return convert(Math.floor(val / 1000000000)) + ' Milyar' + convert(val % 1000000000);
    } else {
      return convert(Math.floor(val / 1000000000000)) + ' Triliun' + convert(val % 1000000000000);
    }
  }

  if (n === 0) return 'Nol Rupiah';
  return (convert(n).trim() + ' Rupiah').replace(/\s+/g, ' ');
}

export function generateInvoiceNumber(seq: number, year: number, month: number): string {
  const yy = year.toString();
  const mm = String(month).padStart(2, '0');
  const count = String(seq).padStart(3, '0');
  return `IVBW/${yy}/${mm}/${count}`;
}

export function generateRvbwNumber(seq: number, year: number, month: number, prefix: string = 'RVBW'): string {
  const yy = String(year).slice(-2);
  const mm = String(month).padStart(2, '0');
  const count = String(seq).padStart(4, '0');
  return `${prefix}${yy}${mm}${count}`;
}
