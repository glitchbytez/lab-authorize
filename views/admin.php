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

    <!-- User Management Widget -->
    <div class="bg-white rounded-lg border border-[#BED1E0] shadow-sm flex flex-col overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-slate-50/50 flex items-center justify-between">
            <h2 class="font-bold text-gray-800 text-[15px] flex items-center space-x-2">
                <i data-lucide="users" class="w-4.5 h-4.5 text-[#12426F]"></i>
                <span>Personnel & User Accounts Management</span>
            </h2>
            <span class="text-xs bg-[#E1EDF4] text-[#12426F] font-bold px-2.5 py-0.5 rounded-full">
                <?php echo count($_SESSION['scientists']); ?> Registered Users
            </span>
        </div>

        <!-- Section Form: Register and Authorize new user account -->
        <div class="p-5 bg-slate-50/70 border-b border-gray-150">
            <form action="index.php?page=admin" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4 text-xs">
                <input type="hidden" name="action" value="create_user">

                <div class="space-y-1">
                    <label class="text-[10px] uppercase font-bold text-gray-500 block">Full Name</label>
                    <input
                        type="text"
                        name="name"
                        placeholder="e.g. Dr. S. Moyo"
                        class="bg-white border border-gray-300 rounded-lg py-1.5 px-3 w-full text-gray-800 outline-none focus:border-blue-500 font-medium"
                        required
                    />
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] uppercase font-bold text-gray-500 block">System Role</label>
                    <select
                        name="role"
                        class="bg-white border border-gray-300 rounded-lg py-1.5 px-3 w-full text-gray-800 outline-none focus:border-blue-500 font-medium font-sans"
                        required
                    >
                        <option value="Lab Scientist">Lab Scientist</option>
                        <option value="Administrator">Administrator</option>
                        <option value="LIS Manager">LIS Manager</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] uppercase font-bold text-gray-500 block">Assigned Lab Facility</label>
                    <select
                        name="lab"
                        class="bg-white border border-gray-300 rounded-lg py-1.5 px-3 w-full text-gray-800 outline-none focus:border-blue-500 font-medium font-sans"
                    >
                        <option value="None">None (System-wide / Admin)</option>
                        <?php foreach ($_SESSION['client_labs'] as $lab): ?>
                            <option value="<?php echo htmlspecialchars($lab['name']); ?>"><?php echo htmlspecialchars($lab['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] uppercase font-bold text-gray-500 block">Access Key / Password</label>
                    <input
                        type="text"
                        name="password"
                        placeholder="Enter password..."
                        class="bg-white border border-gray-300 rounded-lg py-1.5 px-3 w-full text-gray-800 outline-none focus:border-blue-500 font-mono font-medium"
                        required
                    />
                </div>

                <div class="flex items-end">
                    <button
                        type="submit"
                        class="w-full bg-[#12426F] hover:bg-[#1D5E9E] text-white py-2 px-4 rounded-lg font-bold transition-all cursor-pointer h-[34px] flex items-center justify-center space-x-1"
                    >
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        <span>Register User</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Render active listed user accounts -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-[#E9F1F6] border-b border-[#BED1E0] text-gray-800 font-bold uppercase tracking-wider text-[10px] select-none">
                <tr>
                    <th class="px-5 py-3">Specialist ID</th>
                    <th class="px-5 py-3">Full Name</th>
                    <th class="px-5 py-3">Role</th>
                    <th class="px-5 py-3">Assigned Lab Facility</th>
                    <th class="px-5 py-3 text-center">Password</th>
                    <th class="px-5 py-3 text-center w-24">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <?php foreach ($_SESSION['scientists'] as $sc): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5 font-mono font-bold text-[#1A507F]">
                            <?php echo htmlspecialchars($sc['id']); ?>
                        </td>
                        <td class="px-5 py-3.5 font-bold text-gray-800">
                            <?php echo htmlspecialchars($sc['name']); ?>
                        </td>
                        <td class="px-5 py-3.5">
                            <?php if ($sc['role'] === 'Administrator'): ?>
                                <span class="bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 rounded font-bold text-[10px] uppercase tracking-wider">
                                    Administrator
                                </span>
                            <?php elseif ($sc['role'] === 'LIS Manager'): ?>
                                <span class="bg-purple-50 text-purple-700 border border-purple-200 px-2 py-0.5 rounded font-bold text-[10px] uppercase tracking-wider">
                                    LIS Manager
                                </span>
                            <?php else: ?>
                                <span class="bg-slate-100 text-gray-700 border border-slate-200 px-2 py-0.5 rounded font-bold text-[10px] uppercase tracking-wider">
                                    Lab Scientist
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3.5 font-medium text-gray-700">
                            <?php if (!empty($sc['lab'])): ?>
                                <div class="flex items-center space-x-1.5">
                                    <i data-lucide="building" class="w-3.5 h-3.5 text-gray-400"></i>
                                    <span><?php echo htmlspecialchars($sc['lab']); ?></span>
                                </div>
                            <?php else: ?>
                                <span class="text-gray-400 italic text-[11px]">System-Wide (None)</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="font-mono bg-slate-100 rounded px-2 py-0.5 text-slate-600 select-all" title="Click to select password">
                                <?php echo htmlspecialchars($sc['password']); ?>
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <form action="index.php?page=admin" method="POST" onsubmit="return confirm('Are you sure you want to decommission user account \'<?php echo $sc['name']; ?>\'?');" class="inline">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($sc['id']); ?>">
                                <button
                                    type="submit"
                                    class="text-slate-400 hover:text-red-500 p-1 rounded hover:bg-red-50 transition cursor-pointer"
                                    title="Decommission User"
                                    <?php echo $_SESSION['user']['id'] === $sc['id'] ? 'disabled style="opacity: 0.3; cursor: not-allowed;"' : ''; ?>
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
