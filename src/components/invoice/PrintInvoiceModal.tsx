import React, { useMemo } from 'react';
import { Check, Printer, X } from 'lucide-react';
import { useApp } from '../../context/AppContext';
import { Invoice } from '../../types';
import { formatDateIndo, formatRupiah, terbilang } from '../../utils/format';

interface PrintInvoiceModalProps {
  invoice: Invoice;
  onClose: () => void;
}

export const PrintInvoiceModal: React.FC<PrintInvoiceModalProps> = ({ invoice, onClose }) => {
  const { debiturs, invoiceItems, settings } = useApp();

  const debitur = useMemo(() => {
    return debiturs.find((d) => d.id === invoice.debitur_id);
  }, [debiturs, invoice.debitur_id]);

  const items = useMemo(() => {
    return invoiceItems.filter((it) => it.invoice_id === invoice.id);
  }, [invoiceItems, invoice.id]);

  const terbilangText = useMemo(() => {
    return terbilang(invoice.grand_total);
  }, [invoice.grand_total]);

  const handlePrint = () => {
    window.print();
  };

  const getMonthName = (m: number, y: number) => {
    const months = [
      'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    return `${months[m - 1] || m} ${y}`;
  };

  return (
    <div className="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-2 sm:p-4 overflow-y-auto">
      <div className="bg-white text-slate-900 rounded-xl max-w-4xl w-full p-6 sm:p-8 shadow-2xl relative my-auto max-h-[95vh] overflow-y-auto print:max-w-none print:m-0 print:p-6 print:shadow-none print:rounded-none">
        {/* Print & Close Toolbar */}
        <div className="no-print flex items-center justify-between pb-4 mb-4 border-b border-slate-200">
          <div className="text-xs text-slate-500 font-semibold uppercase tracking-wider">
            Dokumen Resmi Invoice A4 (Siap Cetak / Export PDF)
          </div>
          <div className="flex items-center gap-2">
            <button
              onClick={handlePrint}
              className="flex items-center gap-1.5 px-4 py-2 bg-cyan-700 hover:bg-cyan-800 text-white rounded-lg text-xs font-bold shadow-md transition-colors"
            >
              <Printer className="w-4 h-4" />
              <span>Cetak / PDF</span>
            </button>
            <button
              onClick={onClose}
              className="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors"
            >
              <X className="w-5 h-5" />
            </button>
          </div>
        </div>

        {/* --- PRINTABLE INVOICE CONTENT --- */}
        <div className="text-[11px] leading-normal font-sans">
          {/* Header Kop Surat */}
          <table className="w-full border-b-2 border-black pb-2 mb-4">
            <tbody>
              <tr>
                <td className="align-top">
                  <div className="text-base font-bold text-cyan-800 uppercase tracking-wide">
                    {settings.nama_perusahaan}
                  </div>
                  <div className="text-xs font-bold text-slate-700">{settings.sub_nama}</div>
                  <div className="text-[10px] text-slate-500 mt-0.5">
                    {settings.alamat} • Telp: {settings.telepon}
                  </div>
                </td>
                <td className="text-right align-middle">
                  <span className="border-2 border-cyan-800 px-3 py-1 font-bold text-cyan-800 text-xs uppercase tracking-wider">
                    INVOICE TAGIHAN
                  </span>
                </td>
              </tr>
            </tbody>
          </table>

          {/* Title & Nomor Invoice */}
          <div className="text-center my-3">
            <h2 className="text-base font-bold uppercase tracking-wide underline">INVOICE</h2>
            <p className="font-mono text-xs font-bold text-slate-800 mt-0.5">
              {invoice.nomor_invoice}
            </p>
          </div>

          {/* Debitur & Period Info */}
          <table className="w-full mb-4 text-[11px]">
            <tbody>
              <tr>
                <td className="w-24 font-bold py-0.5">Kepada Yth.</td>
                <td className="py-0.5">: {debitur?.nama_owner || 'Debitur'}</td>
                <td className="w-32 font-bold py-0.5">Tanggal Terbit</td>
                <td className="py-0.5">: {formatDateIndo(invoice.tanggal_terbit)}</td>
              </tr>
              <tr>
                <td className="font-bold py-0.5">Kode Kavling</td>
                <td className="py-0.5 font-bold font-mono">
                  : {debitur?.kode_kav || '-'}
                </td>
                <td className="font-bold py-0.5">Periode Tagihan</td>
                <td className="py-0.5">
                  : {getMonthName(invoice.periode_bulan, invoice.periode_tahun)}
                </td>
              </tr>
              <tr>
                <td className="font-bold py-0.5">No. HP / WA</td>
                <td className="py-0.5 font-mono">: {debitur?.no_hp || '-'}</td>
                <td className="font-bold py-0.5">Jatuh Tempo</td>
                <td className="py-0.5">: {formatDateIndo(invoice.jatuh_tempo)}</td>
              </tr>
              <tr>
                <td className="font-bold py-0.5">Alamat Unit</td>
                <td className="py-0.5">: {debitur?.alamat_unit || debitur?.tipe_unit}</td>
                <td className="font-bold py-0.5">Status Bayar</td>
                <td className="py-0.5">
                  : <span className="font-bold uppercase tracking-wider">{invoice.status_bayar}</span>
                  {invoice.status_bayar === 'Lunas' && invoice.tanggal_lunas && (
                    <span className="text-[10px] text-emerald-700 ml-1">
                      (Tgl: {formatDateIndo(invoice.tanggal_lunas)})
                    </span>
                  )}
                </td>
              </tr>
            </tbody>
          </table>

          {/* Table Items */}
          <table className="w-full border-collapse border border-slate-700 mb-4 text-[11px]">
            <thead>
              <tr className="bg-slate-100 text-slate-800 text-[10px] font-bold uppercase">
                <th className="border border-slate-700 py-1.5 px-2 text-center w-8">No</th>
                <th className="border border-slate-700 py-1.5 px-2 text-left">
                  Deskripsi Rincian Tagihan
                </th>
                <th className="border border-slate-700 py-1.5 px-2 text-center w-28">
                  Pemakaian
                </th>
                <th className="border border-slate-700 py-1.5 px-2 text-right w-28">
                  Nilai DPP
                </th>
                <th className="border border-slate-700 py-1.5 px-2 text-right w-24">
                  PPN
                </th>
                <th className="border border-slate-700 py-1.5 px-2 text-right w-32">
                  Total Tagihan
                </th>
              </tr>
            </thead>
            <tbody>
              {items.length === 0 ? (
                <tr>
                  <td colSpan={6} className="border border-slate-700 py-4 text-center text-slate-400">
                    Tidak ada rincian item invoice.
                  </td>
                </tr>
              ) : (
                items.map((item, idx) => (
                  <tr key={item.id} className="hover:bg-slate-50">
                    <td className="border border-slate-700 py-1.5 px-2 text-center font-mono">
                      {idx + 1}
                    </td>
                    <td className="border border-slate-700 py-1.5 px-2 font-medium">
                      {item.keterangan}
                      {item.kode_coa && (
                        <span className="text-[9px] text-slate-500 block font-mono">
                          COA: {item.kode_coa}
                        </span>
                      )}
                    </td>
                    <td className="border border-slate-700 py-1.5 px-2 text-center font-mono">
                      {item.tagihan_bulanan_kategori === 'LISTRIK' &&
                      item.meteran_pln_awal !== null &&
                      item.meteran_pln_akhir !== null
                        ? `${item.meteran_pln_awal} - ${item.meteran_pln_akhir} (${item.pemakaian_volume} kWh)`
                        : item.tagihan_bulanan_kategori === 'AIR' &&
                          item.meteran_air_awal !== null &&
                          item.meteran_air_akhir !== null
                        ? `${item.meteran_air_awal} - ${item.meteran_air_akhir} (${item.pemakaian_volume} m³)`
                        : `${item.pemakaian_volume}`}
                    </td>
                    <td className="border border-slate-700 py-1.5 px-2 text-right font-mono">
                      {formatRupiah(item.nilai_dpp)}
                    </td>
                    <td className="border border-slate-700 py-1.5 px-2 text-right font-mono">
                      {item.ppn_persen > 0
                        ? formatRupiah(item.nilai_dpp * (item.ppn_persen / 100))
                        : '-'}
                    </td>
                    <td className="border border-slate-700 py-1.5 px-2 text-right font-mono font-semibold">
                      {formatRupiah(item.total)}
                    </td>
                  </tr>
                ))
              )}
            </tbody>
            <tfoot>
              <tr className="bg-slate-50 font-bold">
                <td colSpan={5} className="border border-slate-700 py-1.5 px-3 text-right">
                  Subtotal DPP:
                </td>
                <td className="border border-slate-700 py-1.5 px-2 text-right font-mono">
                  {formatRupiah(invoice.subtotal_dpp)}
                </td>
              </tr>
              <tr className="bg-slate-50 font-bold">
                <td colSpan={5} className="border border-slate-700 py-1.5 px-3 text-right">
                  Total PPN ({settings.ppn_default}%):
                </td>
                <td className="border border-slate-700 py-1.5 px-2 text-right font-mono">
                  {formatRupiah(invoice.total_ppn)}
                </td>
              </tr>
              <tr className="bg-cyan-50 font-extrabold text-cyan-900 text-xs">
                <td colSpan={5} className="border border-slate-700 py-2 px-3 text-right">
                  GRAND TOTAL TAGIHAN:
                </td>
                <td className="border border-slate-700 py-2 px-2 text-right font-mono text-xs">
                  {formatRupiah(invoice.grand_total)}
                </td>
              </tr>
            </tfoot>
          </table>

          {/* Terbilang Box */}
          <div className="border border-slate-400 bg-slate-50 p-2.5 rounded italic font-semibold text-[11px] mb-4 text-slate-800">
            Terbilang: #{terbilangText}#
          </div>

          {/* Footer Grid: Bank Account & Signatures */}
          <table className="w-full mt-4">
            <tbody>
              <tr>
                <td className="w-3/5 align-top pr-4">
                  <div className="border border-dashed border-slate-500 bg-slate-50 p-3 rounded text-[10px] space-y-1">
                    <div className="font-bold text-slate-800 uppercase">Instruksi Pembayaran Transfer:</div>
                    <div>Bank: <strong>{settings.nama_bank}</strong></div>
                    <div>No. Rekening: <strong className="font-mono">{settings.no_rekening}</strong></div>
                    <div>Atas Nama: <strong>{settings.atas_nama}</strong></div>
                    <div className="text-[9px] text-slate-500 pt-1 border-t border-slate-200">
                      * Harap mencantumkan nomor invoice pada berita transfer bank.
                    </div>
                  </div>
                </td>

                <td className="w-2/5 text-center align-top">
                  <div className="text-xs">
                    Tanjung Lesung, {formatDateIndo(invoice.tanggal_terbit)}
                  </div>
                  <div className="text-xs font-bold mt-1">
                    {settings.nama_perusahaan}
                  </div>
                  <div className="h-16 flex items-center justify-center text-slate-300">
                    [Tanda Tangan & Cap]
                  </div>
                  <div className="text-xs font-bold underline">
                    Finance & Accounting Dept.
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};
