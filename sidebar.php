<nav class="w-full md:w-64 bg-white border-r border-[#BED1E0] p-4 space-y-2 select-none shrink-0">
    <div class="pb-4 mb-4 border-b border-gray-100">
        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-3 mb-2">Internal Views</div>

        <a href="index.php?page=queue" class="flex items-center space-x-3 text-xs font-bold py-2 px-3 rounded-md transition-all <?php echo $currentPage === 'queue' ? 'bg-blue-50 text-[#12426F]' : 'text-gray-600 hover:bg-gray-50'; ?>">
            <i data-lucide="layers" class="w-4 h-4"></i>
            <span>Analytical Queue</span>
        </a>

        <a href="index.php?page=pending" class="flex items-center space-x-3 text-xs font-bold py-2 px-3 rounded-md transition-all <?php echo $currentPage === 'pending' ? 'bg-blue-50 text-[#12426F]' : 'text-gray-600 hover:bg-gray-50'; ?>">
            <i data-lucide="shield-alert" class="w-4 h-4"></i>
            <span>Review & Authorize</span>
        </a>

        <a href="index.php?page=completed" class="flex items-center space-x-3 text-xs font-bold py-2 px-3 rounded-md transition-all <?php echo $currentPage === 'completed' ? 'bg-blue-50 text-[#12426F]' : 'text-gray-600 hover:bg-gray-50'; ?>">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            <span>Released Reports</span>
        </a>
    </div>

    <!-- Protected Administration Tab -->
    <?php if ($_SESSION['user']['role'] === 'Administrator' || $_SESSION['user']['role'] === 'LIS Manager'): ?>
        <div>
            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-3 mb-2">System Admin</div>
            <a href="index.php?page=admin" class="flex items-center space-x-3 text-xs font-bold py-2 px-3 rounded-md transition-all <?php echo $currentPage === 'admin' ? 'bg-blue-50 text-[#12426F]' : 'text-gray-600 hover:bg-gray-50'; ?>">
                <i data-lucide="settings" class="w-4 h-4"></i>
                <span>System Management</span>
            </a>
        </div>
    <?php endif; ?>

    <div class="h-1 bg-gradient-to-r from-blue-50 to-emerald-50 my-4 rounded"></div>
</nav>

<!-- Late Init script to trigger icon rendering across dynamically attached fragments -->
<script>lucide.createIcons();</script>