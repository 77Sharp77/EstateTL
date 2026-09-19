import React, { createContext, useContext, useEffect, useState } from 'react';
import {
  initialDebiturs,
  initialInvoiceItems,
  initialInvoices,
  initialRvbwList,
  initialSettings,
  initialSkpList,
  initialSpkList,
  initialSpkPayments,
  initialTitipanPvbw,
  initialTitipanRvbw,
  initialUsers,
} from '../data/initialData';
import {
  AppSettings,
  Debitur,
  Invoice,
  InvoiceItem,
  RvbwRecord,
  SkpProperty,
  SpkPayment,
  SpkRecord,
  TitipanPvbw,
  TitipanRvbw,
  User,
} from '../types';

interface AppContextType {
  activeTab: string;
  setActiveTab: (tab: string) => void;
  settings: AppSettings;
  updateSettings: (newSettings: Partial<AppSettings>) => void;
  currentUser: User;
  setCurrentUser: (user: User) => void;
  users: User[];
  debiturs: Debitur[];
  addDebitur: (debitur: Omit<Debitur, 'id'>) => Debitur;
  updateDebitur: (id: number, debitur: Partial<Debitur>) => void;
  invoices: Invoice[];
  invoiceItems: InvoiceItem[];
  addInvoice: (
    invoice: Omit<Invoice, 'id'>,
    items: Omit<InvoiceItem, 'id' | 'invoice_id'>[]
  ) => Invoice;
  toggleInvoiceStatus: (id: number) => void;
  rvbwList: RvbwRecord[];
  addRvbw: (rvbw: Omit<RvbwRecord, 'id'>) => RvbwRecord;
  updateRvbwStatus: (id: number, status: 'Draft' | 'Disetujui' | 'Diposting') => void;
  skpList: SkpProperty[];
  addSkp: (skp: Omit<SkpProperty, 'id'>) => SkpProperty;
  spkList: SpkRecord[];
  addSpk: (spk: Omit<SpkRecord, 'id'>) => SpkRecord;
  spkPayments: SpkPayment[];
  addSpkPayment: (payment: Omit<SpkPayment, 'id'>) => SpkPayment;
  titipanList: TitipanRvbw[];
  addTitipan: (titipan: Omit<TitipanRvbw, 'id'>) => TitipanRvbw;
  titipanPvbwList: TitipanPvbw[];
  addTitipanPvbw: (pvbw: Omit<TitipanPvbw, 'id'>) => TitipanPvbw;
  resetToDefaultData: () => void;
  resetToInitialData: () => void;
  restoreData: (data: Record<string, any>, mode: 'replace' | 'merge') => { success: boolean; stats: Record<string, number> };
  exportAllData: () => Record<string, any>;
  exportAllDataAsJson: () => string;
  restoreDataFromJson: (jsonStr: string) => boolean;
  // For viewing print dialogs
  selectedInvoiceForPrint: Invoice | null;
  setSelectedInvoiceForPrint: (inv: Invoice | null) => void;
  selectedInvoiceForLampiran: Invoice | null;
  setSelectedInvoiceForLampiran: (inv: Invoice | null) => void;
}

const AppContext = createContext<AppContextType | undefined>(undefined);

const STORAGE_KEY = 'kalicaa_estate_state_v1';

export const AppProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [activeTab, setActiveTab] = useState<string>('dashboard');
  const [settings, setSettings] = useState<AppSettings>(initialSettings);
  const [users] = useState<User[]>(initialUsers);
  const [currentUser, setCurrentUser] = useState<User>(initialUsers[0]);
  const [debiturs, setDebiturs] = useState<Debitur[]>(initialDebiturs);
  const [invoices, setInvoices] = useState<Invoice[]>(initialInvoices);
  const [invoiceItems, setInvoiceItems] = useState<InvoiceItem[]>(initialInvoiceItems);
  const [rvbwList, setRvbwList] = useState<RvbwRecord[]>(initialRvbwList);
  const [skpList, setSkpList] = useState<SkpProperty[]>(initialSkpList);
  const [spkList, setSpkList] = useState<SpkRecord[]>(initialSpkList);
  const [spkPayments, setSpkPayments] = useState<SpkPayment[]>(initialSpkPayments);
  const [titipanList, setTitipanList] = useState<TitipanRvbw[]>(initialTitipanRvbw);
  const [titipanPvbwList, setTitipanPvbwList] = useState<TitipanPvbw[]>(initialTitipanPvbw);

  const [selectedInvoiceForPrint, setSelectedInvoiceForPrint] = useState<Invoice | null>(null);
  const [selectedInvoiceForLampiran, setSelectedInvoiceForLampiran] = useState<Invoice | null>(null);

  // Load from localStorage on mount
  useEffect(() => {
    try {
      const saved = localStorage.getItem(STORAGE_KEY);
      if (saved) {
        const parsed = JSON.parse(saved);
        if (parsed.settings) setSettings(parsed.settings);
        if (parsed.debiturs) setDebiturs(parsed.debiturs);
        if (parsed.invoices) setInvoices(parsed.invoices);
        if (parsed.invoiceItems) setInvoiceItems(parsed.invoiceItems);
        if (parsed.rvbwList) setRvbwList(parsed.rvbwList);
        if (parsed.skpList) setSkpList(parsed.skpList);
        if (parsed.spkList) setSpkList(parsed.spkList);
        if (parsed.spkPayments) setSpkPayments(parsed.spkPayments);
        if (parsed.titipanList) setTitipanList(parsed.titipanList);
        if (parsed.titipanPvbwList) setTitipanPvbwList(parsed.titipanPvbwList);
      }
    } catch (e) {
      console.error('Failed to load state from localStorage', e);
    }
  }, []);

  // Save to localStorage whenever state changes
  useEffect(() => {
    try {
      const stateToSave = {
        settings,
        debiturs,
        invoices,
        invoiceItems,
        rvbwList,
        skpList,
        spkList,
        spkPayments,
        titipanList,
        titipanPvbwList,
      };
      localStorage.setItem(STORAGE_KEY, JSON.stringify(stateToSave));
    } catch (e) {
      console.error('Failed to save state to localStorage', e);
    }
  }, [
    settings,
    debiturs,
    invoices,
    invoiceItems,
    rvbwList,
    skpList,
    spkList,
    spkPayments,
    titipanList,
    titipanPvbwList,
  ]);

  const updateSettings = (newSettings: Partial<AppSettings>) => {
    setSettings((prev) => ({ ...prev, ...newSettings }));
  };

  const addDebitur = (data: Omit<Debitur, 'id'>): Debitur => {
    const nextId = Math.max(0, ...debiturs.map((d) => d.id)) + 1;
    const newDeb: Debitur = { ...data, id: nextId, created_at: new Date().toISOString() };
    setDebiturs((prev) => [...prev, newDeb]);
    return newDeb;
  };

  const updateDebitur = (id: number, data: Partial<Debitur>) => {
    setDebiturs((prev) => prev.map((d) => (d.id === id ? { ...d, ...data } : d)));
  };

  const addInvoice = (
    invData: Omit<Invoice, 'id'>,
    itemsData: Omit<InvoiceItem, 'id' | 'invoice_id'>[]
  ): Invoice => {
    const nextInvId = Math.max(0, ...invoices.map((i) => i.id)) + 1;
    const newInvoice: Invoice = {
      ...invData,
      id: nextInvId,
      created_at: new Date().toISOString(),
    };

    let startItemId = Math.max(0, ...invoiceItems.map((item) => item.id)) + 1;
    const newItems: InvoiceItem[] = itemsData.map((item) => ({
      ...item,
      id: startItemId++,
      invoice_id: nextInvId,
    }));

    setInvoices((prev) => [newInvoice, ...prev]);
    setInvoiceItems((prev) => [...prev, ...newItems]);
    setSettings((prev) => ({ ...prev, invoice_counter: prev.invoice_counter + 1 }));

    return newInvoice;
  };

  const toggleInvoiceStatus = (id: number) => {
    setInvoices((prev) =>
      prev.map((inv) => {
        if (inv.id === id) {
          const isLunas = inv.status_bayar === 'Lunas';
          return {
            ...inv,
            status_bayar: isLunas ? 'Belum Bayar' : 'Lunas',
            tanggal_lunas: isLunas ? null : new Date().toISOString().split('T')[0],
          };
        }
        return inv;
      })
    );
  };

  const addRvbw = (data: Omit<RvbwRecord, 'id'>): RvbwRecord => {
    const nextId = Math.max(0, ...rvbwList.map((r) => r.id)) + 1;
    const newRv: RvbwRecord = { ...data, id: nextId, created_at: new Date().toISOString() };
    setRvbwList((prev) => [newRv, ...prev]);
    return newRv;
  };

  const updateRvbwStatus = (id: number, status: 'Draft' | 'Disetujui' | 'Diposting') => {
    setRvbwList((prev) => prev.map((r) => (r.id === id ? { ...r, status } : r)));
  };

  const addSkp = (data: Omit<SkpProperty, 'id'>): SkpProperty => {
    const nextId = Math.max(0, ...skpList.map((s) => s.id)) + 1;
    const newSkp: SkpProperty = { ...data, id: nextId, created_at: new Date().toISOString() };
    setSkpList((prev) => [newSkp, ...prev]);
    return newSkp;
  };

  const addSpk = (data: Omit<SpkRecord, 'id'>): SpkRecord => {
    const nextId = Math.max(0, ...spkList.map((s) => s.id)) + 1;
    const newSpk: SpkRecord = { ...data, id: nextId, created_at: new Date().toISOString() };
    setSpkList((prev) => [newSpk, ...prev]);
    return newSpk;
  };

  const addSpkPayment = (data: Omit<SpkPayment, 'id'>): SpkPayment => {
    const nextId = Math.max(0, ...spkPayments.map((p) => p.id)) + 1;
    const newPayment: SpkPayment = { ...data, id: nextId, created_at: new Date().toISOString() };
    setSpkPayments((prev) => [newPayment, ...prev]);
    return newPayment;
  };

  const addTitipan = (data: Omit<TitipanRvbw, 'id'>): TitipanRvbw => {
    const nextId = Math.max(0, ...titipanList.map((t) => t.id)) + 1;
    const newTitipan: TitipanRvbw = { ...data, id: nextId, created_at: new Date().toISOString() };
    setTitipanList((prev) => [newTitipan, ...prev]);
    return newTitipan;
  };

  const addTitipanPvbw = (data: Omit<TitipanPvbw, 'id'>): TitipanPvbw => {
    const nextId = Math.max(0, ...titipanPvbwList.map((p) => p.id)) + 1;
    const newPvbw: TitipanPvbw = { ...data, id: nextId, created_at: new Date().toISOString() };
    setTitipanPvbwList((prev) => [newPvbw, ...prev]);

    // Check if total returned >= total deposit, if so mark status Selesai
    const titipan = titipanList.find((t) => t.id === data.titipan_rvbw_id);
    if (titipan) {
      const existingReturned = titipanPvbwList
        .filter((p) => p.titipan_rvbw_id === data.titipan_rvbw_id)
        .reduce((sum, p) => sum + Number(p.nominal), 0);
      if (existingReturned + Number(data.nominal) >= Number(titipan.nominal)) {
        setTitipanList((prev) =>
          prev.map((t) => (t.id === data.titipan_rvbw_id ? { ...t, status: 'Selesai' } : t))
        );
      }
    }

    return newPvbw;
  };

  const resetToDefaultData = () => {
    localStorage.removeItem(STORAGE_KEY);
    setSettings(initialSettings);
    setDebiturs(initialDebiturs);
    setInvoices(initialInvoices);
    setInvoiceItems(initialInvoiceItems);
    setRvbwList(initialRvbwList);
    setSkpList(initialSkpList);
    setSpkList(initialSpkList);
    setSpkPayments(initialSpkPayments);
    setTitipanList(initialTitipanRvbw);
    setTitipanPvbwList(initialTitipanPvbw);
  };

  const exportAllData = () => {
    return {
      settings,
      debiturs,
      invoices,
      invoice_items: invoiceItems,
      rvbw: rvbwList,
      skp_properties: skpList,
      spk_records: spkList,
      spk_payments: spkPayments,
      titipan_rvbw: titipanList,
      titipan_pvbw: titipanPvbwList,
      exported_at: new Date().toISOString(),
    };
  };

  const restoreData = (data: Record<string, any>, mode: 'replace' | 'merge') => {
    const stats: Record<string, number> = {
      debiturs: 0,
      invoices: 0,
      skp: 0,
      spk: 0,
      titipan: 0,
    };

    if (data.settings && mode === 'replace') {
      setSettings(data.settings);
    }

    if (Array.isArray(data.debiturs)) {
      if (mode === 'replace') {
        setDebiturs(data.debiturs);
        stats.debiturs = data.debiturs.length;
      } else {
        setDebiturs((prev) => {
          const map = new Map(prev.map((d) => [d.kode_kav, d]));
          data.debiturs.forEach((d: Debitur) => {
            map.set(d.kode_kav, d);
            stats.debiturs++;
          });
          return Array.from(map.values());
        });
      }
    }

    if (Array.isArray(data.invoices)) {
      if (mode === 'replace') {
        setInvoices(data.invoices);
        stats.invoices = data.invoices.length;
      } else {
        setInvoices((prev) => {
          const map = new Map(prev.map((i) => [i.nomor_invoice, i]));
          data.invoices.forEach((i: Invoice) => {
            map.set(i.nomor_invoice, i);
            stats.invoices++;
          });
          return Array.from(map.values());
        });
      }
    }

    if (Array.isArray(data.invoice_items) || Array.isArray(data.invoiceItems)) {
      const items = data.invoice_items || data.invoiceItems;
      if (mode === 'replace') {
        setInvoiceItems(items);
      } else {
        setInvoiceItems((prev) => [...prev, ...items]);
      }
    }

    if (Array.isArray(data.skp_properties) || Array.isArray(data.skpList)) {
      const items = data.skp_properties || data.skpList;
      if (mode === 'replace') {
        setSkpList(items);
        stats.skp = items.length;
      } else {
        setSkpList((prev) => [...prev, ...items]);
        stats.skp = items.length;
      }
    }

    if (Array.isArray(data.spk_records) || Array.isArray(data.spkList)) {
      const items = data.spk_records || data.spkList;
      if (mode === 'replace') {
        setSpkList(items);
        stats.spk = items.length;
      } else {
        setSpkList((prev) => [...prev, ...items]);
        stats.spk = items.length;
      }
    }

    if (Array.isArray(data.titipan_rvbw) || Array.isArray(data.titipanList)) {
      const items = data.titipan_rvbw || data.titipanList;
      if (mode === 'replace') {
        setTitipanList(items);
        stats.titipan = items.length;
      } else {
        setTitipanList((prev) => [...prev, ...items]);
        stats.titipan = items.length;
      }
    }

    return { success: true, stats };
  };

  const resetToInitialData = () => {
    resetToDefaultData();
  };

  const exportAllDataAsJson = () => {
    return JSON.stringify(exportAllData(), null, 2);
  };

  const restoreDataFromJson = (jsonStr: string): boolean => {
    try {
      const parsed = JSON.parse(jsonStr);
      const res = restoreData(parsed, 'replace');
      return res.success;
    } catch {
      return false;
    }
  };

  return (
    <AppContext.Provider
      value={{
        activeTab,
        setActiveTab,
        settings,
        updateSettings,
        currentUser,
        setCurrentUser,
        users,
        debiturs,
        addDebitur,
        updateDebitur,
        invoices,
        invoiceItems,
        addInvoice,
        toggleInvoiceStatus,
        rvbwList,
        addRvbw,
        updateRvbwStatus,
        skpList,
        addSkp,
        spkList,
        addSpk,
        spkPayments,
        addSpkPayment,
        titipanList,
        addTitipan,
        titipanPvbwList,
        addTitipanPvbw,
        resetToDefaultData,
        resetToInitialData,
        restoreData,
        exportAllData,
        exportAllDataAsJson,
        restoreDataFromJson,
        selectedInvoiceForPrint,
        setSelectedInvoiceForPrint,
        selectedInvoiceForLampiran,
        setSelectedInvoiceForLampiran,
      }}
    >
      {children}
    </AppContext.Provider>
  );
};

export const useApp = () => {
  const context = useContext(AppContext);
  if (!context) {
    throw new Error('useApp must be used within an AppProvider');
  }
  return context;
};
