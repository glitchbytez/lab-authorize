<?php
require_once 'data.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Reviews - Laboratory Information Stream</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-[#F0F4F8] min-h-screen">

<div id="pending-authorization-view" class="flex flex-col xl:flex-row h-screen overflow-hidden">

    <!-- Left Pane: Record Queue list -->
    <div class="flex-1 flex flex-col min-w-0 bg-white border-r border-[#BED1E0] h-full overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between select-none shrink-0 bg-[#FCFDFE]">
            <h2 class="font-bold text-gray-800 text-[16px]">
                Authorization Queue (Pending Reviews)
            </h2>
            <span class="text-xs bg-[#E1EDF4] text-[#12426F] font-bold px-2 rounded-full py-0.5">
                    <?= count($pendingRecords) ?> records found
                </span>
        </div>

        <div class="flex-1 overflow-auto">
            <table class="w-full text-left text-xs text-gray-700 min-w-[600px]">
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
                <tbody class="divide-y divide-gray-100 select-none">
                <?php foreach ($pendingRecords as $idx => $rec):
                    $isUrgent = in_array($rec['status'], ['Urgent', 'Critical']);
                    ?>
                    <tr class="cursor-pointer transition-all hover:bg-slate-50 patient-row <?= $idx === 0 ? 'bg-[#E3EFF7] border-y border-[#3483C2] font-semibold text-blue-950 shadow-inner' : '' ?>"
                        id="row-<?= htmlspecialchars($rec['accessionId']) ?>"
                        onclick="selectRecord(<?= htmlspecialchars(json_encode($rec)) ?>)">
                        <td class="px-4 py-3 font-mono font-bold text-[#1A507F]"><?= htmlspecialchars($rec['accessionId']) ?></td>
                        <td class="px-4 py-3 text-gray-900 font-medium"><?= htmlspecialchars($rec['patientName']) ?></td>
                        <td class="px-4 py-3 text-gray-500 font-mono"><?= htmlspecialchars($rec['dob']) ?></td>
                        <td class="px-4 py-3 text-gray-700">
                            <div class="font-medium"><?= htmlspecialchars($rec['testType']) ?></div>
                            <div class="text-[10px] text-blue-800 font-semibold mt-0.5"><?= htmlspecialchars($rec['lab'] ?? 'Default Lab') ?></div>
                        </td>
                        <td class="px-4 py-3">
                            <?php if ($rec['status'] === 'Urgent'): ?>
                                <span class="bg-[#FEF6C9] text-[#713F12] border border-[#FDE047] px-2 py-0.5 rounded font-bold text-[9px] uppercase tracking-wider">Urgent</span>
                            <?php elseif ($rec['status'] === 'Critical'): ?>
                                <span class="bg-[#FEE2E2] text-[#991B1B] border border-[#FCA5A5] px-2 py-0.5 rounded font-bold text-[9px] uppercase tracking-wider animate-pulse">Critical</span>
                            <?php else: ?>
                                <span class="bg-slate-100 text-gray-700 border border-slate-200 px-2 py-0.5 rounded font-bold text-[9px]">Routine</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 font-mono text-gray-500"><?= htmlspecialchars($rec['dateTime']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Side: Interaction Details Panel -->
    <div id="right-panel" class="w-full xl:w-[460px] bg-[#E1EDF4] border-t xl:border-t-0 border-[#BED1E0] flex flex-col p-4 select-none shrink-0 overflow-y-auto">
        <!-- Active record container loaded dynamically via selection JS -->
        <div id="detail-card" class="flex-1 flex flex-col space-y-4">

            <!-- Patient Metadata Information Card -->
            <div class="bg-white p-4 rounded border border-[#A5BACD] shadow-sm">
                <h3 id="det-headline" class="font-extrabold text-[#111] text-[15px] border-b border-gray-100 pb-1.5 leading-tight">
                    ACC-2026-0041: Oliver Mwanga's Full Blood Count
                </h3>
                <div class="mt-2 text-xs text-gray-700 space-y-1">
                    <div><span class="text-gray-500 font-medium font-sans">Ordering Physician:</span> <span id="det-physician" class="font-bold">Dr. E. Thompson</span></div>
                    <div><span class="text-gray-500 font-medium font-sans">Lab Client Tenancy:</span> <span id="det-lab" class="font-bold text-blue-900">Downtown Medical Center</span></div>
                    <div><span class="text-gray-500 font-medium font-sans">DOB Address:</span> <span id="det-dob" class="font-semibold font-mono">1991-04-12</span></div>
                    <div><span class="text-gray-500 font-medium font-sans">Received Date/Time:</span> <span id="det-received" class="font-semibold font-mono">2026-06-23 08:14</span></div>
                </div>
            </div>

            <!-- Parameters list table -->
            <div class="bg-white rounded border border-[#A5BACD] shadow-sm flex flex-col">
                <div class="px-3.5 py-2 border-b border-gray-100 bg-[#E9F1F6] text-gray-800 font-bold text-[11px] uppercase tracking-wider">
                    Test Summary Parameters
                </div>
                <table class="w-full text-left text-xs text-gray-700">
                    <thead class="bg-slate-50 border-b border-slate-100">
                    <tr class="text-[10px] text-gray-500 uppercase font-semibold">
                        <th class="px-3.5 py-2">Parameter</th>
                        <th class="px-3.5 py-2">Result</th>
                        <th class="px-3.5 py-2 text-center">Ref. Range</th>
                        <th class="px-3.5 py-2 text-right">Flag</th>
                    </tr>
                    </thead>
                    <tbody id="det-params-body" class="divide-y divide-gray-100 select-none">
                    <!-- Dynamic Injection -->
                    </tbody>
                </table>
            </div>

            <!-- Notes Editor Area -->
            <div class="bg-white p-3 rounded border border-[#A5BACD] shadow-sm flex-1 flex flex-col">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">
                    Scientist Notes
                </label>
                <textarea id="det-notes" class="w-full flex-1 p-2 border border-[#B3C8D7] rounded text-xs text-gray-800 bg-slate-50 focus:outline-none focus:bg-white focus:border-[#12426F] resize-none font-sans" placeholder="Type clinical annotations..."></textarea>
            </div>

            <!-- Action Confirm Notification Bar -->
            <div id="toast-success" class="hidden p-2.5 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded text-xs font-bold leading-normal flex items-center space-x-2">
                <span>✔️ Action successfully dispatched.</span>
            </div>

            <!-- Interactive Buttons -->
            <div class="space-y-2">
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button onclick="triggerAction('Rejected')" class="bg-white py-2.5 px-3 rounded font-medium text-gray-750 border border-[#A5BACD] hover:bg-red-50 hover:text-red-700 hover:border-red-400">Reject</button>
                    <button onclick="triggerAction('Recheck')" class="bg-white py-2.5 px-3 rounded font-medium text-gray-750 border border-[#A5BACD] hover:bg-amber-50 hover:text-amber-700 hover:border-amber-400">Request Recheck</button>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button onclick="alert('Viewing comments')" class="bg-white border border-[#A5BACD] hover:bg-slate-50 py-2.5 px-3 rounded font-semibold text-gray-700 text-center">View Comments</button>
                    <button onclick="triggerVerify()" class="bg-[#12426F] hover:bg-[#1D5E9E] active:bg-[#07213A] text-white py-2.5 px-3 rounded font-bold flex items-center justify-center space-x-2">
                        <span>Verify & Authorize</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Active interactive logic layer -->
<script>
    let currentRecord = null;

    function selectRecord(record) {
        currentRecord = record;

        // Highlight Row
        document.querySelectorAll('.patient-row').forEach(row => row.classList.remove('bg-[#E3EFF7]', 'font-semibold', 'text-blue-950', 'border-y', 'border-[#3483C2]'));
        const selectedRow = document.getElementById('row-' + record.accessionId);
        if (selectedRow) {
            selectedRow.classList.add('bg-[#E3EFF7]', 'font-semibold', 'text-blue-950', 'border-y', 'border-[#3483C2]');
        }

        // Sync Detail Title & Text fields
        document.getElementById('det-headline').innerText = `${record.accessionId}: ${record.patientName}'s ${record.testType}`;
        document.getElementById('det-physician').innerText = record.orderingPhysician;
        document.getElementById('det-lab').innerText = record.lab || 'Default Lab';
        document.getElementById('det-dob').innerText = record.dob;
        document.getElementById('det-received').innerText = record.dateTime;
        document.getElementById('det-notes').value = record.scientistNotes || '';

        // Render Parameters list
        const paramsBody = document.getElementById('det-params-body');
        paramsBody.innerHTML = '';
        record.parameters.forEach(param => {
            const isNormal = param.flag === 'Normal';
            const flagColorClass = isNormal
                ? 'bg-[#EBF5FC] text-[#12426F] border-[#BED1E0]'
                : 'bg-[#FFE1E1] text-[#A61C1C] border-[#FFA3A3]';

            paramsBody.innerHTML += `
                    <tr class="hover:bg-slate-50">
                        <td class="px-3.5 py-2.5 font-semibold text-gray-900">${param.name}</td>
                        <td class="px-3.5 py-2.5 font-mono font-bold">${param.result}</td>
                        <td class="px-3.5 py-2.5 text-center font-mono text-gray-500">${param.referenceRange}</td>
                        <td class="px-3.5 py-2.5 text-right">
                            <span class="px-2 py-0.5 rounded font-black text-[9px] border ${flagColorClass}">${param.flag}</span>
                        </td>
                    </tr>
                `;
        });
    }

    function triggerVerify() {
        if(!currentRecord) return;
        showToast(`Accession ${currentRecord.accessionId} verified & authorized clinical release!`);
    }

    function triggerAction(actionName) {
        if(!currentRecord) return;
        showToast(`Action [${actionName}] applied to ${currentRecord.accessionId}.`);
    }

    function showToast(message) {
        const toast = document.getElementById('toast-success');
        toast.innerText = message;
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 3500);
    }

    // Initialize with first diagnostic row
    document.addEventListener('DOMContentLoaded', () => {
        const firstRec = <?= json_encode($pendingRecords[0]) ?>;
        selectRecord(firstRec);
    });
</script>
</body>
</html>