<?php
/**
 * Kalicaa Villa - Header Layout
 */
$user = $_SESSION['user'] ?? [
    'nama_lengkap' => 'Administrator',
    'role' => 'Super Admin',
    'username' => 'admin',
    'avatar_color' => '#06b6d4'
];
$currentPage = $currentPage ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? 'Estate Management & Billing System') ?> - Kalicaa Villa</title>
  
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            brand: {
              50: '#ecfeff', 100: '#cffafe', 500: '#06b6d4', 600: '#0891b2', 700: '#0e7490',
            },
            dark: {
              900: '#0d0f12', 850: '#121418', 800: '#1a1d23', 700: '#262a33'
            }
          },
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
            mono: ['JetBrains Mono', 'Menlo', 'monospace']
          }
        }
      }
    }
  </script>
  <!-- Google Fonts & Chart.js -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>

  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #0d0f12;
      color: #f1f5f9;
    }
    .font-mono {
      font-family: 'JetBrains Mono', monospace;
    }
  </style>
</head>
<body class="bg-[#0d0f12] text-slate-100 min-h-screen flex">
  
  <!-- Sidebar Include -->
  <?php include __DIR__ . '/sidebar.php'; ?>

  <!-- Main Content Area -->
  <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    
    <!-- Top Bar -->
    <header class="h-14 bg-[#121418] border-b border-slate-800/80 px-6 flex items-center justify-between z-10 shrink-0">
      <div class="flex items-center gap-3">
        <h1 class="text-sm font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
          <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?>
        </h1>
        <?php if (!empty($pageSubtitle)): ?>
          <span class="text-xs text-slate-500 hidden sm:inline">• <?= htmlspecialchars($pageSubtitle) ?></span>
        <?php endif; ?>
      </div>

      <!-- User Profile & Action -->
      <div class="flex items-center gap-3">
        <a href="/" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-cyan-950/50 border border-cyan-500/30 text-cyan-400 hover:bg-cyan-900/40 text-xs font-semibold transition-colors" title="Beralih ke Versi React">
          <i data-lucide="layout" class="w-3.5 h-3.5"></i>
          <span class="hidden sm:inline">Versi React</span>
        </a>
        <div class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg bg-[#1a1d23] border border-slate-800">
          <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-white uppercase" style="background-color: <?= htmlspecialchars($user['avatar_color']) ?>;">
            <?= substr($user['nama_lengkap'], 0, 1) ?>
          </div>
          <div class="text-left hidden md:block">
            <p class="text-xs font-semibold text-slate-200 leading-none"><?= htmlspecialchars($user['nama_lengkap']) ?></p>
            <p class="text-[10px] text-slate-400 mt-0.5 leading-none"><?= htmlspecialchars($user['role']) ?></p>
          </div>
        </div>
        <a href="?route=logout" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition-colors" title="Keluar">
          <i data-lucide="log-out" class="w-4 h-4"></i>
        </a>
      </div>
    </header>

    <!-- Content Body -->
    <main class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-6">
