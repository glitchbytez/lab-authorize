<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication Portal | Zimbabwe LIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col justify-between">
<div class="w-full max-w-7xl mx-auto px-6 pt-6 flex justify-between items-center text-xs text-slate-400">
    <div class="flex items-center space-x-2">
        <i data-lucide="activity" class="w-4 h-4 text-blue-600 animate-pulse"></i>
        <span class="font-semibold text-slate-600 tracking-wider">HARARE MEDLAB SYSTEM GATEWAY</span>
    </div>
    <div class="flex items-center space-x-1.5 text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded border border-emerald-150">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
        <span class="text-[10px] font-bold tracking-wider uppercase">Zim-Secure Connection</span>
    </div>
</div>

<main class="flex-1 flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white rounded-2xl border border-slate-200/80 shadow-md p-8 flex flex-col relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-700 to-indigo-600"></div>

        <div class="flex flex-col items-center mb-6">
            <div class="w-12 h-12 bg-blue-50 text-blue-800 rounded-xl flex items-center justify-center mb-3 border border-blue-100">
                <i data-lucide="shield" class="w-6 h-6"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900">Personnel Authentication</h3>
            <p class="text-xs text-slate-400 mt-1">Zimbabwe Health Service Access Portal</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="w-full p-3 mb-5 bg-red-50 border border-red-200 rounded-lg text-xs text-red-600 font-semibold text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="index.php?page=login" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="login">

            <div class="space-y-1 text-left">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Clinical Specialist ID / Name</label>
                <input
                    type="text"
                    name="username"
                    placeholder="e.g. S. Sibanda or Admin-01"
                    className="w-full"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium"
                    required
                />
            </div>

            <div class="space-y-1 text-left">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Access Key / Password</label>
                <input
                    type="password"
                    name="password"
                    placeholder="••••••••••••"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium"
                    required
                />
            </div>

            <button
                type="submit"
                class="w-full bg-blue-900 hover:bg-blue-800 text-white font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-all cursor-pointer flex items-center justify-center gap-1.5"
            >
                <span>Authenticate Key</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </form>
    </div>
</main>

<div class="text-center py-6 text-[11px] text-slate-400/80 uppercase tracking-wider">
    Zimbabwe Association of Healthcare Funders (AHFOZ) Regulated Network.
</div>
<script>lucide.createIcons();</script>
</body>
</html>
