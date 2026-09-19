import React from 'react';
import {
  BookOpen,
  Building2,
  Calculator,
  Database,
  FileText,
  FileUp,
  Hammer,
  Home,
  LayoutDashboard,
  Receipt,
  Settings,
  Users,
  Wallet,
} from 'lucide-react';
import { useApp } from '../../context/AppContext';

export const Sidebar: React.FC = () => {
  const { activeTab, setActiveTab, currentUser } = useApp();

  const navItems = [
    { route: 'dashboard', label: 'Dashboard', icon: LayoutDashboard },
    { route: 'debitur', label: 'Master Debitur', icon: Users },
    { route: 'skp', label: 'SKP (Penjualan Unit)', icon: Home },
    { route: 'spk', label: 'SPK Kontraktor', icon: Hammer },
    { route: 'tagihan', label: 'Tagihan Bulanan', icon: Calculator },
    { route: 'rvbw', label: 'Penerimaan RVBW', icon: Receipt },
    { route: 'invoice', label: 'Daftar Invoice', icon: FileText },
    { route: 'kartu-piutang', label: 'Kartu Piutang', icon: BookOpen },
    { route: 'titipan', label: 'Titipan Dana (RVBW/PV)', icon: Wallet },
    { route: 'settings', label: 'Pengaturan & Tarif', icon: Settings },
  ];

  if (currentUser.role === 'Super Admin' || currentUser.role === 'Finance Admin') {
    navItems.push(
      { route: 'backup', label: 'Backup & Excel Sync', icon: Database },
      { route: 'import', label: 'Import Data (Mode SQL)', icon: FileUp }
    );
  }

  return (
    <aside className="w-64 bg-[#121418] border-r border-slate-800/80 flex flex-col shrink-0 select-none">
      {/* Brand Logo & Header */}
      <div className="h-14 border-b border-slate-800/80 px-4 flex items-center gap-3">
        <div className="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400">
          <Building2 className="w-4 h-4" />
        </div>
        <div className="overflow-hidden">
          <h2 className="text-xs font-bold text-white uppercase tracking-wider truncate">
            KALICAA VILLA
          </h2>
          <p className="text-[9px] text-slate-400 font-medium truncate">
            Tanjung Lesung Beach Resort
          </p>
        </div>
      </div>

      {/* Navigation Links */}
      <nav className="flex-1 py-3 px-2 space-y-1 overflow-y-auto">
        {navItems.map((item) => {
          const Icon = item.icon;
          const isActive = activeTab === item.route;
          return (
            <button
              key={item.route}
              onClick={() => setActiveTab(item.route)}
              className={`w-full flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition-colors text-left ${
                isActive
                  ? 'bg-cyan-500/15 text-cyan-400 border-l-2 border-cyan-400 font-semibold'
                  : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50'
              }`}
            >
              <Icon className="w-4 h-4 shrink-0" />
              <span className="truncate">{item.label}</span>
            </button>
          );
        })}
      </nav>

      {/* Footer System Status */}
      <div className="p-3 border-t border-slate-800/80 text-[10px] text-slate-500 flex items-center justify-between">
        <span className="font-mono text-[9px] text-slate-400">Estate TL v2.5</span>
        <span className="text-emerald-400 flex items-center gap-1.5 font-medium">
          <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
          Online
        </span>
      </div>
    </aside>
  );
};
