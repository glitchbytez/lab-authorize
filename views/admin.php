<div class="space-y-6">
    <!-- Action Alerts -->
    <div id="admin-success-alert" class="hidden p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-lg flex items-center space-x-2">
        <i data-lucide="check" class="w-4 h-4 text-emerald-600"></i>
        <span id="admin-success-text">Success message</span>
    </div>
    <div id="admin-error-alert" class="hidden p-4 bg-red-50 border border-red-200 text-red-800 text-xs font-semibold rounded-lg flex items-center space-x-2">
        <i data-lucide="x" class="w-4 h-4 text-red-650"></i>
        <span id="admin-error-text">Error message</span>
    </div>

    <!-- Multi-Tenant LIS Customer Layout Widget -->
    <div class="bg-white rounded-lg border border-[#BED1E0] shadow-sm flex flex-col overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-slate-50/50 flex items-center justify-between">
            <h2 class="font-bold text-gray-800 text-[15px] flex items-center space-x-2">
                <i data-lucide="building" class="w-4.5 h-4.5 text-[#12426F]"></i>
                <span>Client Facility Laboratories (Multi-Tenant LIS)</span>
            </h2>
            <span id="labs-count-badge" class="text-xs bg-[#E1EDF4] text-[#12426F] font-bold px-2.5 py-0.5 rounded-full">
                0 Registered Clients
            </span>
        </div>

        <!-- Section Form: Register and Authorize client facility -->
        <div class="p-5 bg-slate-50/70 border-b border-gray-150">
            <form id="create-lab-form" class="flex flex-col md:flex-row gap-4 text-xs">
                <div class="flex-1 space-y-1">
                    <label class="text-[10px] uppercase font-bold text-gray-500 block">Client Laboratory Name</label>
                    <input
                        type="text"
                        name="lab_name"
                        id="form-lab-name"
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
                        id="form-ahfoz-number"
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
                    <th class="px-5 py-3 text-center w-40">Actions</th>
                </tr>
                </thead>
                <tbody id="labs-table-body" class="divide-y divide-gray-100">
                    <tr>
                        <td colspan="3" class="px-5 py-4 text-center text-gray-400 italic">
                            Loading registered laboratory clients...
                        </td>
                    </tr>
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
            <span id="users-count-badge" class="text-xs bg-[#E1EDF4] text-[#12426F] font-bold px-2.5 py-0.5 rounded-full">
                0 Registered Users
            </span>
        </div>

        <!-- Section Form: Register and Authorize new user account -->
        <div class="p-5 bg-slate-50/70 border-b border-gray-150">
            <form id="create-user-form" class="grid grid-cols-1 md:grid-cols-5 gap-4 text-xs">
                <div class="space-y-1">
                    <label class="text-[10px] uppercase font-bold text-gray-500 block">Full Name</label>
                    <input
                        type="text"
                        name="name"
                        id="form-user-name"
                        placeholder="e.g. Dr. S. Moyo"
                        class="bg-white border border-gray-300 rounded-lg py-1.5 px-3 w-full text-gray-800 outline-none focus:border-blue-500 font-medium"
                        required
                    />
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] uppercase font-bold text-gray-500 block">System Role</label>
                    <select
                        name="role"
                        id="form-user-role"
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
                        id="form-user-lab"
                        class="bg-white border border-gray-300 rounded-lg py-1.5 px-3 w-full text-gray-800 outline-none focus:border-blue-500 font-medium font-sans"
                    >
                        <option value="None">None (System-wide / Admin)</option>
                        <!-- Injected dynamically -->
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] uppercase font-bold text-gray-500 block">Access Key / Password</label>
                    <input
                        type="text"
                        name="password"
                        id="form-user-password"
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
                <tbody id="users-table-body" class="divide-y divide-gray-100">
                    <tr>
                        <td colspan="6" class="px-5 py-4 text-center text-gray-400 italic">
                            Loading registered personnel files...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Get currently authenticated administrator ID from session PHP
    const CURRENT_USER_ID = <?php echo json_encode($_SESSION['user']['id'] ?? ''); ?>;

    let allLabs = [];
    let allUsers = [];

    // Clear alert helpers
    function clearAlerts() {
        document.getElementById('admin-success-alert').classList.add('hidden');
        document.getElementById('admin-error-alert').classList.add('hidden');
    }

    function showSuccess(msg) {
        clearAlerts();
        document.getElementById('admin-success-text').innerText = msg;
        document.getElementById('admin-success-alert').classList.remove('hidden');
        setTimeout(clearAlerts, 5000);
    }

    function showError(msg) {
        clearAlerts();
        document.getElementById('admin-error-text').innerText = msg;
        document.getElementById('admin-error-alert').classList.remove('hidden');
        setTimeout(clearAlerts, 5000);
    }

    async function loadAdminData() {
        try {
            // Fetch labs and users in parallel
            const [labs, users] = await Promise.all([API.getLabs(), API.getUsers()]);
            allLabs = labs;
            allUsers = users;

            renderLabsTable();
            renderUsersTable();
            populateLabsDropdown();

            // Re-render Lucide icons
            if (window.lucide) {
                window.lucide.createIcons();
            }
        } catch (err) {
            console.error("Failed to fetch administrative data from LIS API:", err);
            showError("System Offline: Unable to query administrative registry.");
        }
    }

    function renderLabsTable() {
        const badge = document.getElementById('labs-count-badge');
        if (badge) badge.innerText = `${allLabs.length} Registered Clients`;

        const tbody = document.getElementById('labs-table-body');
        tbody.innerHTML = '';

        if (allLabs.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="px-5 py-6 text-center text-gray-450 italic">No registered client facilities.</td></tr>';
            return;
        }

        allLabs.forEach(lab => {
            const tr = document.createElement('tr');
            tr.className = "hover:bg-slate-50 transition-colors";
            
            const ahfozBadge = lab.ahfoz 
                ? `<span class="bg-[#E1EDF4] text-[#12426F] font-mono font-bold px-2.5 py-0.5 rounded text-[11px] border border-[#CBDDE9]">${lab.ahfoz}</span>`
                : `<span class="text-gray-400 italic text-[11px]">Not Provided</span>`;

            tr.innerHTML = `
                <td class="px-5 py-3.5 font-bold text-gray-800">
                    <div class="flex items-center space-x-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#12426F] shrink-0"></span>
                        <span>${escapeHtml(lab.name)}</span>
                    </div>
                </td>
                <td class="px-5 py-3.5 text-center">
                    ${ahfozBadge}
                </td>
                <td class="px-5 py-3.5 text-center">
                    <button
                        onclick="deleteLab('${escapeJs(lab.name)}')"
                        class="text-slate-400 hover:text-red-500 p-1 rounded hover:bg-red-50 transition cursor-pointer"
                        title="Decommission Facility"
                    >
                        <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderUsersTable() {
        const badge = document.getElementById('users-count-badge');
        if (badge) badge.innerText = `${allUsers.length} Registered Users`;

        const tbody = document.getElementById('users-table-body');
        tbody.innerHTML = '';

        if (allUsers.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-5 py-6 text-center text-gray-450 italic">No rostered personnel accounts.</td></tr>';
            return;
        }

        allUsers.forEach(sc => {
            const tr = document.createElement('tr');
            tr.className = "hover:bg-slate-50 transition-colors";

            let roleBadge = `<span class="bg-slate-100 text-gray-700 border border-slate-200 px-2 py-0.5 rounded font-bold text-[10px] uppercase tracking-wider">Lab Scientist</span>`;
            if (sc.role === 'Administrator') {
                roleBadge = `<span class="bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 rounded font-bold text-[10px] uppercase tracking-wider">Administrator</span>`;
            } else if (sc.role === 'LIS Manager') {
                roleBadge = `<span class="bg-purple-50 text-purple-700 border border-purple-200 px-2 py-0.5 rounded font-bold text-[10px] uppercase tracking-wider">LIS Manager</span>`;
            }

            const facilityInfo = sc.lab
                ? `<div class="flex items-center space-x-1.5"><i data-lucide="building" class="w-3.5 h-3.5 text-gray-400"></i><span>${escapeHtml(sc.lab)}</span></div>`
                : `<span class="text-gray-400 italic text-[11px]">System-Wide (None)</span>`;

            const isSelf = CURRENT_USER_ID === sc.id;
            const disabledAttr = isSelf ? 'disabled style="opacity: 0.3; cursor: not-allowed;"' : '';

            tr.innerHTML = `
                <td class="px-5 py-3.5 font-mono font-bold text-[#1A507F]">
                    ${escapeHtml(sc.id)}
                </td>
                <td class="px-5 py-3.5 font-bold text-gray-800">
                    ${escapeHtml(sc.name)}
                </td>
                <td class="px-5 py-3.5">
                    ${roleBadge}
                </td>
                <td class="px-5 py-3.5 font-medium text-gray-700">
                    ${facilityInfo}
                </td>
                <td class="px-5 py-3.5 text-center">
                    <span class="font-mono bg-slate-100 rounded px-2 py-0.5 text-slate-600 select-all" title="Click to select password">
                        ${escapeHtml(sc.password)}
                    </span>
                </td>
                <td class="px-5 py-3.5 text-center">
                    <button
                        onclick="deleteUser('${escapeJs(sc.id)}', '${escapeJs(sc.name)}')"
                        class="text-slate-400 hover:text-red-500 p-1 rounded hover:bg-red-50 transition cursor-pointer"
                        title="Decommission User"
                        ${disabledAttr}
                    >
                        <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function populateLabsDropdown() {
        const select = document.getElementById('form-user-lab');
        if (!select) return;
        
        const currentValue = select.value;
        select.innerHTML = '<option value="None">None (System-wide / Admin)</option>';
        
        allLabs.forEach(lab => {
            const opt = document.createElement('option');
            opt.value = lab.name;
            opt.innerText = lab.name;
            select.appendChild(opt);
        });
        
        select.value = currentValue || 'None';
    }

    // Submit Event Listeners
    document.getElementById('create-lab-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const labName = document.getElementById('form-lab-name').value;
        const ahfoz = document.getElementById('form-ahfoz-number').value;

        try {
            const res = await API.createLab(labName, ahfoz);
            showSuccess(res.success || `Facility '${labName}' registered successfully.`);
            document.getElementById('create-lab-form').reset();
            await loadAdminData();
        } catch (err) {
            showError(err.message);
        }
    });

    document.getElementById('create-user-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const name = document.getElementById('form-user-name').value;
        const role = document.getElementById('form-user-role').value;
        const lab = document.getElementById('form-user-lab').value;
        const password = document.getElementById('form-user-password').value;

        try {
            const res = await API.createUser(name, role, lab, password);
            showSuccess(res.success || `Personnel file registered for '${name}'.`);
            document.getElementById('create-user-form').reset();
            await loadAdminData();
        } catch (err) {
            showError(err.message);
        }
    });

    // Delete actions
    async function deleteLab(labName) {
        if (!confirm(`Are you sure you want to decommission client laboratory '${labName}'?`)) {
            return;
        }
        try {
            // Count specialists active in this lab client-side
            const specialistsCount = allUsers.filter(u => u.lab === labName).length;
            if (specialistsCount > 0) {
                showError(`Cannot decommission '${labName}' when active specialists are rostered.`);
                return;
            }

            const res = await API.deleteLab(labName);
            showSuccess(res.success || `Laboratory decommissioned successfully.`);
            await loadAdminData();
        } catch (err) {
            showError(err.message);
        }
    }

    async function deleteUser(id, name) {
        if (id === CURRENT_USER_ID) {
            showError("Security Policy Violation: You cannot delete your own active profile.");
            return;
        }
        if (!confirm(`Are you sure you want to decommission user account '${name}'?`)) {
            return;
        }
        try {
            const res = await API.deleteUser(id);
            showSuccess(res.success || `User account decommissioned successfully.`);
            await loadAdminData();
        } catch (err) {
            showError(err.message);
        }
    }

    // HTML Escaping Utility
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;")
                  .replace(/</g, "&lt;")
                  .replace(/>/g, "&gt;")
                  .replace(/"/g, "&quot;")
                  .replace(/'/g, "&#039;");
    }

    function escapeJs(str) {
        if (!str) return '';
        return str.replace(/\\/g, '\\\\')
                  .replace(/'/g, "\\'")
                  .replace(/"/g, '\\"');
    }

    // Run immediately
    (function() {
        loadAdminData();
    })();
</script>
