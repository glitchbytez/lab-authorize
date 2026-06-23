<div class="space-y-6">
    <!-- Action Alerts -->
    <?php if (!empty($success)): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-lg flex items-center space-x-2">
            <i data-lucide="check" class="w-4 h-4"></i>
            <span><?php echo htmlspecialchars($success); ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 text-xs font-semibold rounded-lg flex items-center space-x-2">
            <i data-lucide="x" class="w-4 h-4"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <!-- Multi-Tenant LIS Customer Layout Widget -->
    <div class="bg-white rounded-lg border border-[#BED1E0] shadow-sm flex flex-col overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-slate-50/50 flex items-center justify-between">
            <h2 class="font-bold text-gray-800 text-[15px] flex items-center space-x-2">
                <i data-lucide="building" class="w-4.5 h-4.5 text-[#12426F]"></i>
                <span>Client Facility Laboratories (Multi-Tenant LIS)</span>
            </h2>
            <span class="text-xs bg-[#E1EDF4] text-[#12426F] font-bold px-2.5 py-0.5 rounded-full">
                <?php echo count($_SESSION['client_labs']); ?> Registered Clients
            </span>
        </div>

        <!-- Section Form: Register and Authorize client facility -->
        <div class="p-5 bg-slate-50/70 border-b border-gray-150">
            <form action="index.php?page=admin" method="POST" class="flex flex-col md:flex-row gap-4 text-xs">
                <input type="hidden" name="action" value="create_lab">

                <div class="flex-1 space-y-1">
                    <label class="text-[10px] uppercase font-bold text-gray-500 block">Client Laboratory Name</label>
                    <input
                        type="text"
                        name="lab_name"
                        placeholder="e.g. Mpilo Central Lab, Shurugwi Diagnostics..."
                        class="bg-white border border-gray-300 rounded-lg py-1.5 px-3 w-full text-gray-800 outline-none focus:border-blue-500 font-medium"
                        required
                    />
                </div>

                <div class="w-full md:w-64 space-y-1">
                    <label class="text-[10px] uppercase font-bold text-gray-500 flex items-center justify-between">
                        <span>AHFOZ Registration Number</span>
                        <span class="text-[9px] text-[#12426F] font-semibold italic">(Zim LIS ID)</span>
                    </label>
                    <input
                        type="text"
                        name="ahfoz_number"
                        placeholder="e.g. AH 24/098/B or AHF-2024-0891"
                        class="bg-white border border-gray-300 rounded-lg py-1.5 px-3 w-full text-gray-800 outline-none focus:border-blue-500 font-mono font-medium"
                    />
                </div>

                <div class="flex items-end">
                    <button
                        type="submit"
                        class="bg-[#12426F] hover:bg-[#1D5E9E] text-white py-2 px-5 rounded-lg font-bold transition-all cursor-pointer h-[34px] flex items-center justify-center"
                    >
                        Register Facility
                    </button>
                </div>
            </form>
        </div>

        <!-- Render active listed facilities -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-[#E9F1F6] border-b border-[#BED1E0] text-gray-800 font-bold uppercase tracking-wider text-[10px] select-none">
                <tr>
                    <th class="px-5 py-3">Client Lab Facility Name</th>
                    <th class="px-5 py-3 text-center w-56">AHFOZ Registration Number</th>
                    <th class="px-5 py-3 text-center w-40">Specialists Assigned</th>
                    <th class="px-5 py-3 text-center w-24">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <?php foreach ($_SESSION['client_labs'] as $lab): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5 font-bold text-gray-800">
                            <div class="flex items-center space-x-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#12426F] shrink-0"></span>
                                <span><?php echo htmlspecialchars($lab['name']); ?></span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <?php if (!empty($lab['ahfoz'])): ?>
                                <span class="bg-[#E1EDF4] text-[#12426F] font-mono font-bold px-2.5 py-0.5 rounded text-[11px] border border-[#CBDDE9]">
                                        <?php echo htmlspecialchars($lab['ahfoz']); ?>
                                    </span>
                            <?php else: ?>
                                <span class="text-gray-400 italic text-[11px]">Not Provided</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3.5 text-center text-gray-600 font-semibold font-mono">
                            <?php
                            $count = getLabSpecialistsCount($lab['name']);
                            echo "{$count} specialists Assigned";
                            ?>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <form action="index.php?page=admin" method="POST" onsubmit="return confirm('Are you sure you want to decommission client laboratory \'<?php echo $lab['name']; ?>\'?');" class="inline">
                                <input type="hidden" name="action" value="delete_lab">
                                <input type="hidden" name="lab_name" value="<?php echo htmlspecialchars($lab['name']); ?>">
                                <button
                                    type="submit"
                                    class="text-slate-400 hover:text-red-500 p-1 rounded hover:bg-red-50 transition cursor-pointer"
                                    title="Decommission Facility"
                                >
                                    <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>lucide.createIcons();</script>
