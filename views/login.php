<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LIS Gateway — Sign In</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">

<div class="w-full max-w-sm bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-900">Sign in</h1>
        <p class="text-sm text-slate-400 mt-1">LIS Authorization Portal</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="p-3 mb-5 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600 text-center">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="index.php?page=login" method="POST" class="space-y-4">
        <input type="hidden" name="action" value="login">

        <div class="space-y-1">
            <label class="text-xs font-medium text-slate-600">Username</label>
            <input
                type="text"
                name="username"
                placeholder="Name or ID"
                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                required
            />
        </div>

        <div class="space-y-1">
            <label class="text-xs font-medium text-slate-600">Password</label>
            <input
                type="password"
                name="password"
                placeholder="••••••••"
                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                required
            />
        </div>

        <button
            type="submit"
            class="w-full bg-blue-900 hover:bg-blue-800 text-white font-semibold py-2.5 px-4 rounded-lg transition-all cursor-pointer"
        >
            Sign in
        </button>
    </form>
</div>

</body>
</html>
