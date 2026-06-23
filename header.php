<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zimbabwe Multitenant LIS Gateway</title>
    <!-- Tailwind Play CDN for layout classes -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons Library via web script -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen">
<!-- Topbar Banner Navigation Container -->
<header class="h-16 bg-white border-b border-[#BED1E0] px-6 flex items-center justify-between select-none">
    <div class="flex items-center space-x-3">
        <div class="p-2 bg-blue-50 text-[#12426F] rounded-lg border border-blue-100">
            <i data-lucide="activity" class="w-5 h-5 text-[#12426F]"></i>
        </div>
        <div>
            <h1 class="text-sm font-bold text-gray-900 tracking-tight leading-none uppercase">Apex Laboratory Network</h1>
            <p class="text-[10px] text-gray-500 font-semibold tracking-wider mt-0.5">Zimbabwe General Multitenant Gateway</p>
        </div>
    </div>

    <div class="flex items-center space-x-4">
            <span class="text-[11px] bg-[#E1EDF4] text-[#12426F] px-2.5 py-1 rounded font-bold border border-[#CBDDE9]">
                <?php echo htmlspecialchars($_SESSION['user']['lab'] ?? 'All Client Facilities'); ?>
            </span>
        <div class="text-right">
            <div class="text-[11px] font-bold text-gray-800"><?php echo htmlspecialchars($_SESSION['user']['name']); ?></div>
            <div class="text-[10px] text-gray-500 font-medium tracking-wider uppercase"><?php echo htmlspecialchars($_SESSION['user']['role']); ?></div>
        </div>
        <a href="index.php?page=logout" class="text-red-500 hover:text-red-700 p-1 rounded hover:bg-red-50 transition" title="Exit LIS Portal">
            <i data-lucide="log-out" class="w-4.5 h-4.5"></i>
        </a>
    </div>
</header>
