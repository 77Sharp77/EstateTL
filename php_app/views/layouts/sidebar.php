<?php
/**
 * Kalicaa Villa - Sidebar Navigation
 */
$currentPage = $currentPage ?? 'dashboard';

$navItems = [
    ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
    ['route' => 'debitur', 'label' => 'Master Debitur', 'icon' => 'users'],
    ['route' => 'skp', 'label' => 'SKP (Penjualan Unit)', 'icon' => 'home'],
    ['route' => 'spk', 'label' => 'SPK Kontraktor', 'icon' => 'hammer'],
    ['route' => 'tagihan', 'label' => 'Tagihan Bulanan', 'icon' => 'calculator'],
    ['route' => 'rvbw', 'label' => 'Penerimaan RVBW', 'icon' => 'receipt'],
    ['route' => 'invoice', 'label' => 'Daftar Invoice', 'icon' => 'file-text'],
    ['route' => 'kartu-piutang', 'label' => 'Kartu Piutang', 'icon' => 'book-open'],
    ['route' => 'titipan', 'label' => 'Titipan Dana (RVBW/PV)', 'icon' => 'wallet'],
    ['route' => 'settings', 'label' => 'Pengaturan & Tarif', 'icon' => 'settings'],
];

if (($user['role'] ?? '') === 'Super Admin') {
    $navItems[] = ['route' => 'backup', 'label' => 'Backup & Excel Sync', 'icon' => 'database'];
    $navItems[] = ['route' => 'import', 'label' => 'Import Data (Mode SQL)', 'icon' => 'file-up'];
}
?>
<aside class="w-64 bg-[#121418] border-r border-slate-800/80 flex flex-col shrink-0">
  <!-- Brand Logo & Header -->
  <div class="h-14 border-b border-slate-800/80 px-4 flex items-center gap-3">
    <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400">
      <i data-lucide="building-2" class="w-4 h-4"></i>
    </div>
    <div>
      <h2 class="text-xs font-bold text-white uppercase tracking-wider">KALICAA VILLA</h2>
      <p class="text-[9px] text-slate-400 font-medium truncate">Tanjung Lesung Beach Resort</p>
    </div>
  </div>

  <!-- Navigation Links -->
  <nav class="flex-1 py-4 px-2 space-y-1 overflow-y-auto">
    <?php foreach ($navItems as $item): ?>
      <?php $isActive = ($currentPage === $item['route']); ?>
      <a href="?route=<?= $item['route'] ?>" 
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition-colors <?= $isActive 
            ? 'bg-cyan-500/15 text-cyan-400 border-l-2 border-cyan-400 font-semibold' 
            : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' ?>">
        <i data-lucide="<?= $item['icon'] ?>" class="w-4 h-4 shrink-0"></i>
        <span><?= htmlspecialchars($item['label']) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <!-- Footer Info -->
  <div class="p-3 border-t border-slate-800/80 text-[10px] text-slate-500 flex items-center justify-between">
    <span>PHP 8.2 • MySQL Edition</span>
    <span class="text-emerald-400 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Online</span>
  </div>
</aside>
