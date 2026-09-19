import React from 'react';
import { BackupView } from './components/backup/BackupView';
import { DashboardView } from './components/dashboard/DashboardView';
import { DebiturView } from './components/debitur/DebiturView';
import { ImportView } from './components/import/ImportView';
import { InvoiceView } from './components/invoice/InvoiceView';
import { KartuPiutangView } from './components/kartu_piutang/KartuPiutangView';
import { Header } from './components/layout/Header';
import { Sidebar } from './components/layout/Sidebar';
import { RvbwView } from './components/rvbw/RvbwView';
import { SettingsView } from './components/settings/SettingsView';
import { SkpView } from './components/skp/SkpView';
import { SpkView } from './components/spk/SpkView';
import { TagihanView } from './components/tagihan/TagihanView';
import { TitipanView } from './components/titipan/TitipanView';
import { useApp } from './context/AppContext';

export const AppContent: React.FC = () => {
  const { activeTab } = useApp();

  const getTabMeta = () => {
    switch (activeTab) {
      case 'dashboard':
        return { title: 'Dashboard Utama', subtitle: 'Overview Finansial & Operasional Resort' };
      case 'debitur':
        return { title: 'Master Debitur', subtitle: 'Pengelolaan Unit & Pemilik Kavling' };
      case 'skp':
        return { title: 'SKP Penjualan Unit', subtitle: 'Surat Kesepakatan Penjualan Properti' };
      case 'spk':
        return { title: 'SPK Kontraktor', subtitle: 'Surat Perintah Kerja Pemeliharaan' };
      case 'tagihan':
        return { title: 'Kalkulator Tagihan', subtitle: 'Pencatatan Meteran & Generate Invoice' };
      case 'rvbw':
        return { title: 'Penerimaan RVBW', subtitle: 'Receipt Voucher Beachfront / West' };
      case 'invoice':
        return { title: 'Daftar Invoice', subtitle: 'Faktur Tagihan & Cetak Dokumen Resmi' };
      case 'kartu-piutang':
        return { title: 'Kartu Piutang Debitur', subtitle: 'Buku Pembantu & Saldo Berjalan' };
      case 'titipan':
        return { title: 'Titipan Dana', subtitle: 'Jaminan Renovasi & Security Deposit' };
      case 'settings':
        return { title: 'Pengaturan Sistem', subtitle: 'Konfigurasi Tarif Listrik, Air & Kawasan' };
      case 'backup':
        return { title: 'Backup & Restore', subtitle: 'Ekspor Cadangan & Pemulihan Basis Data' };
      case 'import':
        return { title: 'Impor Data', subtitle: 'Migrasi dari File SQL & Tabel CSV' };
      default:
        return { title: 'Estate Management', subtitle: 'Kalicaa Villa Tanjung Lesung' };
    }
  };

  const meta = getTabMeta();

  return (
    <div className="flex h-screen bg-[#0d0f12] text-slate-100 overflow-hidden font-sans">
      {/* Sidebar Navigation */}
      <Sidebar />

      {/* Main Workspace */}
      <div className="flex-1 flex flex-col min-w-0 overflow-hidden">
        <Header title={meta.title} subtitle={meta.subtitle} />

        {/* Scrollable View Area */}
        <main className="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
          <div className="max-w-7xl mx-auto pb-12">
            {activeTab === 'dashboard' && <DashboardView />}
            {activeTab === 'debitur' && <DebiturView />}
            {activeTab === 'skp' && <SkpView />}
            {activeTab === 'spk' && <SpkView />}
            {activeTab === 'tagihan' && <TagihanView />}
            {activeTab === 'rvbw' && <RvbwView />}
            {activeTab === 'invoice' && <InvoiceView />}
            {activeTab === 'kartu-piutang' && <KartuPiutangView />}
            {activeTab === 'titipan' && <TitipanView />}
            {activeTab === 'settings' && <SettingsView />}
            {activeTab === 'backup' && <BackupView />}
            {activeTab === 'import' && <ImportView />}
          </div>
        </main>
      </div>
    </div>
  );
};
