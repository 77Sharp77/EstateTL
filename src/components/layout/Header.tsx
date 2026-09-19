import React, { useState } from 'react';
import { Check, ChevronDown, UserCheck } from 'lucide-react';
import { useApp } from '../../context/AppContext';

interface HeaderProps {
  title: string;
  subtitle?: string;
}

export const Header: React.FC<HeaderProps> = ({ title, subtitle }) => {
  const { currentUser, setCurrentUser, users } = useApp();
  const [showRoleDropdown, setShowRoleDropdown] = useState(false);

  return (
    <header className="h-14 bg-[#121418] border-b border-slate-800/80 px-4 sm:px-6 flex items-center justify-between z-20 shrink-0">
      <div className="flex items-center gap-3">
        <h1 className="text-sm font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
          {title}
        </h1>
        {subtitle && (
          <span className="text-xs text-slate-500 hidden sm:inline">• {subtitle}</span>
        )}
      </div>

      {/* User & Role Switcher */}
      <div className="flex items-center gap-3 relative">
        <div className="relative">
          <button
            onClick={() => setShowRoleDropdown(!showRoleDropdown)}
            className="flex items-center gap-2.5 px-3 py-1.5 rounded-lg bg-[#1a1d23] border border-slate-800 hover:border-slate-700 transition-colors text-left"
          >
            <div
              className="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-white uppercase shrink-0"
              style={{ backgroundColor: currentUser.avatar_color }}
            >
              {currentUser.nama_lengkap.charAt(0)}
            </div>
            <div className="text-left hidden md:block">
              <p className="text-xs font-semibold text-slate-200 leading-none">
                {currentUser.nama_lengkap}
              </p>
              <p className="text-[10px] text-slate-400 mt-0.5 leading-none flex items-center gap-1">
                {currentUser.role}
              </p>
            </div>
            <ChevronDown className="w-3.5 h-3.5 text-slate-400 ml-1" />
          </button>

          {showRoleDropdown && (
            <div className="absolute right-0 mt-2 w-56 bg-[#1a1d23] border border-slate-800 rounded-xl shadow-xl p-1.5 z-50">
              <div className="px-3 py-2 border-b border-slate-800/80 text-[11px] font-semibold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                <UserCheck className="w-3.5 h-3.5 text-cyan-400" />
                Ganti Role / Pengguna
              </div>
              <div className="py-1 space-y-0.5">
                {users.map((u) => (
                  <button
                    key={u.id}
                    onClick={() => {
                      setCurrentUser(u);
                      setShowRoleDropdown(false);
                    }}
                    className={`w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs transition-colors ${
                      currentUser.id === u.id
                        ? 'bg-cyan-500/15 text-cyan-400 font-semibold'
                        : 'text-slate-300 hover:bg-slate-800/60'
                    }`}
                  >
                    <div className="flex items-center gap-2">
                      <div
                        className="w-5 h-5 rounded-full flex items-center justify-center text-[9px] font-bold text-white uppercase"
                        style={{ backgroundColor: u.avatar_color }}
                      >
                        {u.nama_lengkap.charAt(0)}
                      </div>
                      <div className="text-left">
                        <div className="leading-none">{u.nama_lengkap}</div>
                        <div className="text-[10px] text-slate-500 mt-0.5">{u.role}</div>
                      </div>
                    </div>
                    {currentUser.id === u.id && <Check className="w-3.5 h-3.5 text-cyan-400" />}
                  </button>
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </header>
  );
};
