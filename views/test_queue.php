<?php
require_once 'data.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Queue Overview - Laboratory Information Stream</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-[#F0F4F8] min-h-screen">

<div id="test-queue-view" class="flex flex-col lg:flex-row p-6 gap-6 h-[calc(100vh-64px)] overflow-hidden">

    <!-- Main Panel: Scrollable Queue Table -->
    <div class="flex-1 bg-white rounded-lg border border-[#BED1E0] shadow-sm flex flex-col min-w-0">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between select-none shrink-0">
            <h2 class="font-bold text-gray-800 text-[16px]">
                Queue Overview (By Test Type / Accession)
            </h2>
            <div class="text-[11px] font-semibold text-gray-500 bg-slate-100 px-2.5 py-1 rounded">
                Live Stream: <span class="text-emerald-600 font-bold">ACTIVE</span>
            </div>
        </div>

        <div class="flex-1 overflow-auto">
            <table class="w-full text-left text-xs text-gray-700 min-w-[700px]">
                <thead class="bg-[#E9F1F6] border-b border-[#BED1E0] text-gray-800 font-bold uppercase tracking-wider text-[10px] sticky top-0 z-10 select-none">
                <tr>
                    <th class="px-4 py-3">Accession ID</th>
                    <th class="px-4 py-3">Patient Name</th>
                    <th class="px-4 py-3">Test Type</th>
                    <th class="px-4 py-3">Scheduled Time</th>
                    <th class="px-4 py-3">Current Status</th>
                    <th class="px-4 py-3">Priority</th>
                    <th class="px-4 py-3">Assigned Scientist</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <?php foreach ($queueRecords as $idx => $item):
                    $isUrgent = ($idx % 2 === 0); // Mock priority logic
                    ?>
                    <tr class="hover:bg-[#F4F8FB] transition-colors">
                        <td class="px-4 py-2.5 font-mono font-medium text-[#1A507F]"><?= htmlspecialchars($item['accessionId']) ?></td>
                        <td class="px-4 py-2.5 font-medium text-gray-900"><?= htmlspecialchars($item['patientName']) ?></td>
                        <td class="px-4 py-2.5 text-gray-700">
                            <div class="font-semibold"><?= htmlspecialchars($item['testType']) ?></div>
                            <div class="text-[10px] text-blue-800 font-bold mt-0.5"><?= htmlspecialchars($item['lab']) ?></div>
                        </td>
                        <td class="px-4 py-2.5 font-mono text-gray-500"><?= htmlspecialchars($item['scheduledTime']) ?></td>
                        <td class="px-4 py-2.5">
                            <?php if ($item['status'] === 'Processing'): ?>
                                <span class="bg-[#FFF3E0] text-[#E65100] border border-[#FFE0B2] px-2 py-0.5 rounded-full font-bold text-[9px] inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#E65100] animate-pulse"></span>
                                            Processing
                                        </span>
                            <?php else: ?>
                                <span class="bg-[#E8F5E9] text-[#2E7D32] border border-[#C8E6C9] px-2 py-0.5 rounded-full font-bold text-[9px] inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#2E7D32]"></span>
                                            Received
                                        </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-2.5">
                            <?php if ($isUrgent): ?>
                                <span class="bg-[#FFEBEE] text-[#C62828] border border-[#FFCDD2] px-2 py-0.5 rounded font-bold text-[10px] inline-flex items-center gap-1">
                                            <span>🚩</span> Urgent
                                        </span>
                            <?php else: ?>
                                <span class="text-gray-600 font-medium inline-flex items-center gap-1.5 pl-1 text-[10px]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Normal
                                        </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-2.5 font-medium text-gray-700"><?= htmlspecialchars($item['assignedScientist']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Sidebar: Stats & Diagnostics -->
    <div class="w-full lg:w-72 flex flex-col gap-5 select-none shrink-0">

        <!-- Custom HTML Graphic representation -->
        <div class="bg-white p-4 rounded-lg border border-[#BED1E0] shadow-sm">
            <h3 class="font-bold text-gray-800 text-xs mb-3 leading-tight">
                Test Type Volume (Processing vs. Completed Queue)
            </h3>

            <div class="h-28 flex items-end justify-between px-2 pt-2 border-b border-gray-150">
                <div class="flex flex-col items-center w-8">
                    <div class="w-4 bg-[#12426F] rounded-t" style="height: 78px;"></div>
                    <span class="text-[8px] text-gray-400 mt-1 font-mono">Queue</span>
                </div>
                <div class="flex flex-col items-center w-8">
                    <div class="w-4 bg-[#3483C2] rounded-t" style="height: 62px;"></div>
                    <span class="text-[8px] text-gray-400 mt-1 font-mono">Process</span>
                </div>
                <div class="flex flex-col items-center w-8">
                    <div class="w-4 bg-[#76B4E5] rounded-t" style="height: 51px;"></div>
                    <span class="text-[8px] text-gray-400 mt-1 font-mono">Compl.</span>
                </div>
            </div>
            <p class="text-[10px] text-gray-500 mt-2 text-center italic">Updated just now via system daemon</p>
        </div>

        <!-- Metric Cards -->
        <div class="bg-[#FFF5F5] border border-red-200 p-4 rounded-lg flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <span class="text-red-900 font-bold text-xs">Urgent Cases</span>
                <span class="text-red-600">⚠️</span>
            </div>
            <div class="mt-2">
                <span class="text-2xl font-black text-red-750">(18)</span>
                <span class="text-[10px] text-red-600 font-bold block mt-1">Requires immediate sign-off</span>
            </div>
        </div>

        <div class="bg-[#F0F8FF] border border-blue-100 p-4 rounded-lg flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <span class="text-[#052A4E] font-bold text-xs">Total Received</span>
                <span class="text-[#12426F]">📅</span>
            </div>
            <div class="mt-2">
                <span class="text-2xl font-black text-[#12426F]">(Today: 135)</span>
                <span class="text-[10px] text-blue-600 font-bold block mt-1">Average processing time: 14m</span>
            </div>
        </div>

    </div>
</div>
</body>
</html>
