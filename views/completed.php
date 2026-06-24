<div id="completed-records-view" class="flex flex-col xl:flex-row h-screen overflow-hidden">

    <!-- Left Pane: Grid Table of Completed Files -->
    <div class="flex-1 flex flex-col min-w-0 bg-white border-r border-[#BED1E0] h-full">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between select-none shrink-0 bg-[#FCFDFE]">
            <h2 class="font-bold text-gray-800 text-[16px]">Completed Data Results</h2>
            <span id="completed-count-badge" class="text-xs bg-emerald-100 text-emerald-800 font-bold px-2 rounded-full py-0.5">
                0 completed
            </span>
        </div>

        <div class="flex-1 overflow-auto">
            <table class="w-full text-left text-xs text-gray-700 min-w-[650px]">
                <thead class="bg-[#E9F1F6] border-b border-[#BED1E0] text-gray-800 font-bold uppercase tracking-wider text-[10px] sticky top-0 z-10 select-none">
                <tr>
                    <th class="px-4 py-3">Accession #</th>
                    <th class="px-4 py-3">Patient Name</th>
                    <th class="px-4 py-3">DOB</th>
                    <th class="px-4 py-3">Test Type</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Date/Time</th>
                </tr>
                </thead>
                <tbody id="completed-records-body" class="divide-y divide-gray-100 select-none">
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-450 italic">
                            Loading completed cases from database...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Side: Frozen Report Detail with Watermark -->
    <div class="w-full xl:w-[460px] bg-[#E1EDF4] border-t xl:border-t-0 border-[#BED1E0] flex flex-col p-4 select-none shrink-0 overflow-y-auto relative h-full">

        <!-- WATERMARK OVERLAY (Faded, rotated complete text behind elements) -->
        <div class="absolute inset-0 pt-36 pointer-events-none flex items-center justify-center overflow-hidden z-10 select-none">
                <span class="text-gray-400/20 text-[52px] font-black tracking-widest uppercase font-mono transform -rotate-15 select-none text-center">
                    REPORT COMPLETED
                </span>
        </div>

        <!-- Content Card (Relative z-20 for separation from watermark) -->
        <div class="flex-1 flex flex-col space-y-4 relative z-20">

            <div class="bg-white p-4 rounded border border-[#A5BACD] shadow-sm">
                <h3 id="det-headline" class="font-extrabold text-[#111] text-[15px] border-b border-gray-150 pb-1.5 leading-tight">
                    Select a record
                </h3>
                <div class="mt-2 text-xs text-gray-700 space-y-1">
                    <div><span class="text-gray-500 font-medium">Authorizing Scientist:</span> <span id="det-auth-scientist" class="font-bold text-[#12426F]">-</span></div>
                    <div><span class="text-gray-500 font-medium">Lab Client Tenancy:</span> <span id="det-lab" class="font-bold text-blue-900">-</span></div>
                    <div><span class="text-gray-500 font-medium">DOB Address:</span> <span id="det-dob" class="font-semibold font-mono">-</span></div>
                    <div><span class="text-gray-500 font-medium">Authorized Time:</span> <span id="det-auth-time" class="font-semibold font-mono text-slate-800">-</span></div>
                </div>
            </div>

            <!-- Parameters summary panel -->
            <div class="bg-white rounded border border-[#A5BACD] shadow-sm">
                <div class="px-3.5 py-2 border-b border-gray-100 bg-[#E9F1F6] text-gray-800 font-bold text-[11px] uppercase tracking-wider">
                    Test Summary Parameters
                </div>
                <table class="w-full text-left text-xs text-gray-700">
                    <thead class="bg-slate-50 border-b border-slate-100">
                    <tr class="text-[10px] text-gray-500 uppercase font-semibold">
                        <th class="px-3.5 py-1.5">Parameter</th>
                        <th class="px-3.5 py-1.5">Result</th>
                        <th class="px-3.5 py-1.5 text-center">Ref. Range</th>
                        <th class="px-3.5 py-1.5 text-right font-bold w-16">Flag</th>
                    </tr>
                    </thead>
                    <tbody id="det-params-body" class="divide-y divide-gray-100 font-medium">
                        <!-- Injected dynamically -->
                    </tbody>
                </table>
            </div>

            <!-- Locked notes block -->
            <div class="bg-white/95 p-3.5 rounded border border-[#A5BACD] shadow-sm flex-1 flex flex-col">
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide flex items-center gap-1.5">
                    <span>🔒</span> <span class="font-bold">Scientist Notes (Report Locked)</span>
                </label>
                <div id="det-notes" class="flex-1 p-2.5 bg-slate-50 border border-slate-200 text-xs italic text-gray-600 rounded select-none font-serif leading-relaxed">
                    -
                </div>
            </div>

            <!-- Print Command Buttons -->
            <div class="space-y-2">
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button disabled class="bg-slate-100 border border-slate-200 text-slate-400 py-2.5 px-3 rounded font-medium text-center cursor-not-allowed">Reject (Locked)</button>
                    <button disabled class="bg-slate-100 border border-slate-200 text-slate-400 py-2.5 px-3 rounded font-medium text-center cursor-not-allowed">Request Recheck (Locked)</button>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button onclick="alert('Review baseline comment archives completed')" class="bg-white border border-[#A5BACD] hover:bg-slate-50 py-2.5 px-3 rounded font-semibold text-gray-750">View Comments</button>
                    <button onclick="triggerPrint()" class="bg-[#12426F] hover:bg-[#1D5E9E] text-white py-2.5 px-3 rounded font-bold flex items-center justify-center space-x-1.5 shadow">
                        <span>Print Authorized Report 🖨️</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Active interactive logic layer -->
<script>
    let completedRecords = [];
    let currentRecord = null;

    async function loadCompletedRecords() {
        try {
            completedRecords = await API.getCompletedRecords();
            
            // Sync count badge
            const countBadge = document.getElementById('completed-count-badge');
            if (countBadge) {
                countBadge.innerText = `${completedRecords.length} completed`;
            }
            
            const tbody = document.getElementById('completed-records-body');
            tbody.innerHTML = '';
            
            if (completedRecords.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 italic">No completed diagnostic files.</td></tr>';
                clearDetails();
                return;
            }
            
            completedRecords.forEach((rec, idx) => {
                const isRejected = rec.status === 'Rejected';
                
                let badge = `<span class="bg-emerald-100 text-emerald-800 border border-emerald-250 px-2 py-0.5 rounded font-semibold text-[9px] inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Approved</span>`;
                if (isRejected) {
                    badge = `<span class="bg-red-100 text-red-850 border border-red-200 px-2 py-0.5 rounded font-bold text-[9px] uppercase tracking-wider">Rejected</span>`;
                }
                
                const row = document.createElement('tr');
                row.id = `row-${rec.accessionId}`;
                row.className = `cursor-pointer transition-all patient-row`;
                row.addEventListener('click', () => selectCompleted(rec));
                
                row.innerHTML = `
                    <td class="px-4 py-3 font-mono font-bold text-[#1A507F]">${rec.accessionId}</td>
                    <td class="px-4 py-3 text-gray-900 font-medium">${rec.patientName}</td>
                    <td class="px-4 py-3 text-gray-500 font-mono">${rec.dob}</td>
                    <td class="px-4 py-3 text-gray-700">
                        <div class="font-medium">${rec.testType}</div>
                        <div class="text-[10px] text-blue-800 font-semibold mt-0.5">${rec.lab}</div>
                    </td>
                    <td class="px-4 py-3">${badge}</td>
                    <td class="px-4 py-3 font-mono text-gray-500">${rec.dateTime}</td>
                `;
                tbody.appendChild(row);
            });
            
            selectCompleted(completedRecords[0]);
        } catch (e) {
            console.error("Failed to load completed records:", e);
            document.getElementById('completed-records-body').innerHTML = `<tr><td colspan="6" class="px-4 py-8 text-center text-red-500 font-bold">Failed to load records from LIS API.</td></tr>`;
        }
    }

    function selectCompleted(record) {
        if (!record) {
            clearDetails();
            return;
        }
        currentRecord = record;

        // Highlight Row
        document.querySelectorAll('.patient-row').forEach(row => row.classList.remove('bg-[#E3EFF7]', 'font-semibold', 'text-blue-950'));
        const selectedRow = document.getElementById('row-' + record.accessionId);
        if (selectedRow) selectedRow.classList.add('bg-[#E3EFF7]', 'font-semibold', 'text-blue-950');

        // Sync Header Detail Fields
        document.getElementById('det-headline').innerText = `${record.accessionId}: ${record.patientName}'s ${record.testType}`;
        document.getElementById('det-auth-scientist').innerText = record.authorizedScientist || 'N/A';
        document.getElementById('det-lab').innerText = record.lab;
        document.getElementById('det-dob').innerText = record.dob;
        document.getElementById('det-auth-time').innerText = record.authorizedTime || 'N/A';
        document.getElementById('det-notes').innerText = record.scientistNotes || 'Report Locked/Sign-off finalized.';

        // Render Locked Parameters
        const paramsBody = document.getElementById('det-params-body');
        paramsBody.innerHTML = '';
        record.parameters.forEach(param => {
            const isNormal = param.flag === 'Normal';
            const flagColorClass = isNormal
                ? 'bg-[#EBF5FC] text-[#12426F] border-[#BED1E0]'
                : 'bg-[#FFE1E1] text-[#A61C1C] border-[#FFA3A3]';

            paramsBody.innerHTML += `
                    <tr class="hover:bg-slate-50">
                        <td class="px-3.5 py-2 font-semibold text-gray-800">${param.name}</td>
                        <td class="px-3.5 py-2 font-mono text-gray-900 font-bold">${param.result}</td>
                        <td class="px-3.5 py-2 text-center font-mono text-gray-400">${param.referenceRange}</td>
                        <td class="px-3.5 py-2 text-right">
                            <span class="px-2 py-0.5 rounded font-black text-[9px] border ${flagColorClass}">${param.flag}</span>
                        </td>
                    </tr>
                `;
        });
    }

    function clearDetails() {
        currentRecord = null;
        document.getElementById('det-headline').innerText = 'No Record Selected';
        document.getElementById('det-auth-scientist').innerText = '-';
        document.getElementById('det-lab').innerText = '-';
        document.getElementById('det-dob').innerText = '-';
        document.getElementById('det-auth-time').innerText = '-';
        document.getElementById('det-notes').innerText = '-';
        document.getElementById('det-params-body').innerHTML = '<tr><td colspan="4" class="px-3.5 py-4 text-center text-gray-400 italic">No parameters available</td></tr>';
    }

    function triggerPrint() {
        if(!currentRecord) return;
        alert(`Clinical Dispatcher: Sent verification package for patient ${currentRecord.patientName} (Accession # ${currentRecord.accessionId}) to local laboratory printer sequence.`);
    }

    // Initialize with first completed record
    (function() {
        loadCompletedRecords();
    })();
</script>