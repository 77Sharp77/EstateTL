import React, { useState } from 'react';
import {
  Building2,
  Check,
  CreditCard,
  Droplet,
  Percent,
  RefreshCw,
  Save,
  Settings,
  Trees,
  Zap,
} from 'lucide-react';
import { useApp } from '../../context/AppContext';
import { initialSettings } from '../../data/initialData';

export const SettingsView: React.FC = () => {
  const { settings, updateSettings } = useApp();
  const [formData, setFormData] = useState({ ...settings });
  const [savedSuccess, setSavedSuccess] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    updateSettings(formData);
    setSavedSuccess(true);
    setTimeout(() => setSavedSuccess(false), 3000);
  };

  const handleResetToDefault = () => {
    if (confirm('Apakah Anda yakin ingin mengembalikan seluruh parameter tarif ke standar pabrik?')) {
      setFormData(initialSettings);
      updateSettings(initialSettings);
      setSavedSuccess(true);
      setTimeout(() => setSavedSuccess(false), 3000);
    }
  };

  return (
    <div className="space-y-6">
      {/* Banner */}
      <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-base font-bold text-white flex items-center gap-2">
            <Settings className="w-5 h-5 text-cyan-400" />
            Pengaturan Sistem & Master Tarif Utility
          </h2>
          <p className="text-xs text-slate-400 mt-1">
            Konfigurasi identitas kop surat, rekening bank penampung, dan formula tarif tagihan listrik, air, serta kawasan.
          </p>
        </div>

        <div className="flex items-center gap-2">
          {savedSuccess && (
            <div className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold">
              <Check className="w-4 h-4" />
              <span>Pengaturan Tersimpan!</span>
            </div>
          )}
          <button
            type="button"
            onClick={handleResetToDefault}
            className="flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium transition-colors"
          >
            <RefreshCw className="w-3.5 h-3.5" />
            <span>Reset Standar</span>
          </button>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="space-y-6">
        {/* Company & Bank Info Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {/* Company Profile */}
          <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-4">
            <h3 className="text-xs font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-2">
              <Building2 className="w-4 h-4 text-cyan-400" />
              Identitas Perusahaan & Kop Surat
            </h3>

            <div className="space-y-3">
              <div>
                <label className="block text-xs font-medium text-slate-400 mb-1">
                  Nama Perusahaan
                </label>
                <input
                  type="text"
                  value={formData.nama_perusahaan}
                  onChange={(e) => setFormData({ ...formData, nama_perusahaan: e.target.value })}
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 focus:border-cyan-500"
                />
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-400 mb-1">
                  Sub Nama / Area
                </label>
                <input
                  type="text"
                  value={formData.sub_nama}
                  onChange={(e) => setFormData({ ...formData, sub_nama: e.target.value })}
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 focus:border-cyan-500"
                />
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-400 mb-1">
                  Alamat Kantor Operasional
                </label>
                <input
                  type="text"
                  value={formData.alamat}
                  onChange={(e) => setFormData({ ...formData, alamat: e.target.value })}
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 focus:border-cyan-500"
                />
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-400 mb-1">
                  Telepon / Hotline
                </label>
                <input
                  type="text"
                  value={formData.telepon}
                  onChange={(e) => setFormData({ ...formData, telepon: e.target.value })}
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 focus:border-cyan-500"
                />
              </div>
            </div>
          </div>

          {/* Bank Account */}
          <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-4">
            <h3 className="text-xs font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-2">
              <CreditCard className="w-4 h-4 text-cyan-400" />
              Rekening Bank Penampung Tagihan
            </h3>

            <div className="space-y-3">
              <div>
                <label className="block text-xs font-medium text-slate-400 mb-1">
                  Nama Bank
                </label>
                <input
                  type="text"
                  value={formData.nama_bank}
                  onChange={(e) => setFormData({ ...formData, nama_bank: e.target.value })}
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 focus:border-cyan-500"
                />
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-400 mb-1">
                  Nomor Rekening
                </label>
                <input
                  type="text"
                  value={formData.no_rekening}
                  onChange={(e) => setFormData({ ...formData, no_rekening: e.target.value })}
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-mono focus:border-cyan-500"
                />
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-400 mb-1">
                  Atas Nama Rekening
                </label>
                <input
                  type="text"
                  value={formData.atas_nama}
                  onChange={(e) => setFormData({ ...formData, atas_nama: e.target.value })}
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 focus:border-cyan-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-3 pt-1">
                <div>
                  <label className="block text-xs font-medium text-slate-400 mb-1">
                    PPN Default (%)
                  </label>
                  <input
                    type="number"
                    step="0.5"
                    value={formData.ppn_default}
                    onChange={(e) =>
                      setFormData({ ...formData, ppn_default: Number(e.target.value) })
                    }
                    className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-mono text-right focus:border-cyan-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-medium text-slate-400 mb-1">
                    Prefix RVBW
                  </label>
                  <input
                    type="text"
                    value={formData.prefix_rvbw}
                    onChange={(e) => setFormData({ ...formData, prefix_rvbw: e.target.value })}
                    className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2.5 font-mono focus:border-cyan-500"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Utilities Formula Parameters */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {/* Listrik PLN */}
          <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-3">
            <h3 className="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-2">
              <Zap className="w-4 h-4 text-amber-400" />
              Tarif Listrik PLN
            </h3>

            <div>
              <label className="block text-[11px] font-medium text-slate-400 mb-1">
                Tarif per kWh (Rp)
              </label>
              <input
                type="number"
                step="0.01"
                value={formData.tarif_pln_per_kwh}
                onChange={(e) =>
                  setFormData({ ...formData, tarif_pln_per_kwh: Number(e.target.value) })
                }
                className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2 font-mono text-right focus:border-amber-500"
              />
            </div>

            <div>
              <label className="block text-[11px] font-medium text-slate-400 mb-1">
                Faktor Rekening Minimum (40 Jam Nyala)
              </label>
              <input
                type="number"
                step="0.001"
                value={formData.faktor_rekening_minimum}
                onChange={(e) =>
                  setFormData({ ...formData, faktor_rekening_minimum: Number(e.target.value) })
                }
                className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2 font-mono text-right focus:border-amber-500"
              />
            </div>

            <div className="grid grid-cols-2 gap-2">
              <div>
                <label className="block text-[11px] font-medium text-slate-400 mb-1">
                  Loses (%)
                </label>
                <input
                  type="number"
                  step="0.1"
                  value={formData.persen_loses}
                  onChange={(e) =>
                    setFormData({ ...formData, persen_loses: Number(e.target.value) })
                  }
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2 font-mono text-right focus:border-amber-500"
                />
              </div>

              <div>
                <label className="block text-[11px] font-medium text-slate-400 mb-1">
                  PPJ (%)
                </label>
                <input
                  type="number"
                  step="0.1"
                  value={formData.persen_ppj}
                  onChange={(e) =>
                    setFormData({ ...formData, persen_ppj: Number(e.target.value) })
                  }
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2 font-mono text-right focus:border-amber-500"
                />
              </div>
            </div>

            <div>
              <label className="block text-[11px] font-medium text-slate-400 mb-1">
                Jasa Pelayanan Listrik & Air (%)
              </label>
              <input
                type="number"
                step="0.5"
                value={formData.persen_jasa_listrik_air}
                onChange={(e) =>
                  setFormData({ ...formData, persen_jasa_listrik_air: Number(e.target.value) })
                }
                className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2 font-mono text-right focus:border-amber-500"
              />
            </div>
          </div>

          {/* Air PDAM Tiering */}
          <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-3">
            <h3 className="text-xs font-bold text-cyan-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-2">
              <Droplet className="w-4 h-4 text-cyan-400" />
              Tarif Air PDAM (Tiering)
            </h3>

            <div>
              <label className="block text-[11px] font-medium text-slate-400 mb-1">
                Biaya Abodemen (Rp)
              </label>
              <input
                type="number"
                step="500"
                value={formData.tarif_air_abodemen}
                onChange={(e) =>
                  setFormData({ ...formData, tarif_air_abodemen: Number(e.target.value) })
                }
                className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2 font-mono text-right focus:border-cyan-500"
              />
            </div>

            <div className="grid grid-cols-2 gap-2">
              <div>
                <label className="block text-[11px] font-medium text-slate-400 mb-1">
                  Batas Tier 1 (m³)
                </label>
                <input
                  type="number"
                  value={formData.tarif_air_tier1_batas}
                  onChange={(e) =>
                    setFormData({ ...formData, tarif_air_tier1_batas: Number(e.target.value) })
                  }
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2 font-mono text-right focus:border-cyan-500"
                />
              </div>
              <div>
                <label className="block text-[11px] font-medium text-slate-400 mb-1">
                  Tarif Tier 1 (Rp)
                </label>
                <input
                  type="number"
                  value={formData.tarif_air_tier1}
                  onChange={(e) =>
                    setFormData({ ...formData, tarif_air_tier1: Number(e.target.value) })
                  }
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2 font-mono text-right focus:border-cyan-500"
                />
              </div>
            </div>

            <div className="grid grid-cols-2 gap-2">
              <div>
                <label className="block text-[11px] font-medium text-slate-400 mb-1">
                  Batas Tier 2 (m³)
                </label>
                <input
                  type="number"
                  value={formData.tarif_air_tier2_batas}
                  onChange={(e) =>
                    setFormData({ ...formData, tarif_air_tier2_batas: Number(e.target.value) })
                  }
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2 font-mono text-right focus:border-cyan-500"
                />
              </div>
              <div>
                <label className="block text-[11px] font-medium text-slate-400 mb-1">
                  Tarif Tier 2 (Rp)
                </label>
                <input
                  type="number"
                  value={formData.tarif_air_tier2}
                  onChange={(e) =>
                    setFormData({ ...formData, tarif_air_tier2: Number(e.target.value) })
                  }
                  className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2 font-mono text-right focus:border-cyan-500"
                />
              </div>
            </div>

            <div>
              <label className="block text-[11px] font-medium text-slate-400 mb-1">
                Tarif Tier 3 (&gt; Batas Tier 2) (Rp)
              </label>
              <input
                type="number"
                value={formData.tarif_air_tier3}
                onChange={(e) =>
                  setFormData({ ...formData, tarif_air_tier3: Number(e.target.value) })
                }
                className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2 font-mono text-right focus:border-cyan-500"
              />
            </div>
          </div>

          {/* Kawasan & Lingkungan */}
          <div className="bg-[#121418] border border-slate-800/80 rounded-xl p-5 space-y-3">
            <h3 className="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-2">
              <Trees className="w-4 h-4 text-emerald-400" />
              Tarif Kawasan & IPL
            </h3>

            <div>
              <label className="block text-[11px] font-medium text-slate-400 mb-1">
                Tarif Kawasan per m² (Rp)
              </label>
              <input
                type="number"
                step="100"
                value={formData.tarif_kawasan_per_m2}
                onChange={(e) =>
                  setFormData({ ...formData, tarif_kawasan_per_m2: Number(e.target.value) })
                }
                className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2 font-mono text-right focus:border-emerald-500"
              />
            </div>

            <div>
              <label className="block text-[11px] font-medium text-slate-400 mb-1">
                Management Fee Kawasan (%)
              </label>
              <input
                type="number"
                step="0.5"
                value={formData.persen_fee_kawasan}
                onChange={(e) =>
                  setFormData({ ...formData, persen_fee_kawasan: Number(e.target.value) })
                }
                className="w-full bg-[#1a1d23] border border-slate-700 text-slate-100 text-xs rounded-lg p-2 font-mono text-right focus:border-emerald-500"
              />
            </div>
          </div>
        </div>

        {/* Save Bar */}
        <div className="flex items-center justify-end">
          <button
            type="submit"
            className="flex items-center gap-2 px-6 py-2.5 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold shadow-lg shadow-cyan-950/40 transition-colors"
          >
            <Save className="w-4 h-4" />
            <span>Simpan Semua Perubahan</span>
          </button>
        </div>
      </form>
    </div>
  );
};
