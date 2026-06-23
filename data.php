<?php
// data.php - Central mock database array

$clientLabs = [
    ['name' => 'Downtown Medical Center', 'ahfoz' => 'AH 24/098/B'],
    ['name' => 'St. Jude Clinical Laboratory', 'ahfoz' => 'JH 25/714/S'],
    ['name' => 'Apex Diagnostic Partners', 'ahfoz' => 'AP 23/110/A'],
    ['name' => 'Valley Health LIS', 'ahfoz' => 'VH 26/030/V']
];

$pendingRecords = [
    [
        'accessionId' => 'ACC-2026-0041',
        'patientName' => 'Oliver Mwanga',
        'dob' => '1991-04-12',
        'testType' => 'Full Blood Count',
        'lab' => 'Downtown Medical Center',
        'status' => 'Urgent',
        'dateTime' => '2026-06-23 08:14',
        'orderingPhysician' => 'Dr. E. Thompson',
        'scientistNotes' => 'Slightly elevated WBC counts observed. Red blood platelet distribution indices normal.',
        'parameters' => [
            ['id' => 'p1', 'name' => 'White Blood Cell (WBC)', 'result' => '11.8 x10^9/L', 'referenceRange' => '4.5 - 11.0', 'flag' => 'High'],
            ['id' => 'p2', 'name' => 'Red Blood Cell (RBC)', 'result' => '4.85 x10^12/L', 'referenceRange' => '4.30 - 5.90', 'flag' => 'Normal'],
            ['id' => 'p3', 'name' => 'Haemoglobin', 'result' => '14.2 g/dL', 'referenceRange' => '13.5 - 17.5', 'flag' => 'Normal']
        ]
    ],
    [
        'accessionId' => 'ACC-2026-0042',
        'patientName' => 'Sarah Ndlovu',
        'dob' => '1984-11-03',
        'testType' => 'Lipid Profile',
        'lab' => 'St. Jude Clinical Laboratory',
        'status' => 'Critical',
        'dateTime' => '2026-06-23 08:30',
        'orderingPhysician' => 'Dr. T. Sibanda',
        'scientistNotes' => 'Critical total cholesterol concentration logged.',
        'parameters' => [
            ['id' => 'p4', 'name' => 'Total Cholesterol', 'result' => '6.8 mmol/L', 'referenceRange' => '< 5.2', 'flag' => 'Critically High'],
            ['id' => 'p5', 'name' => 'HDL Cholesterol', 'result' => '0.9 mmol/L', 'referenceRange' => '> 1.0', 'flag' => 'Critically Low']
        ]
    ],
    [
        'accessionId' => 'ACC-2026-0043',
        'patientName' => 'Tinashe Mariga',
        'dob' => '1976-07-22',
        'testType' => 'Renal Function',
        'lab' => 'Apex Diagnostic Partners',
        'status' => 'Routine',
        'dateTime' => '2026-06-23 09:15',
        'orderingPhysician' => 'Dr. A. Mutasa',
        'scientistNotes' => 'Urea and serum creatinine parameters are within standard baseline thresholds.',
        'parameters' => [
            ['id' => 'p6', 'name' => 'Serum Creatinine', 'result' => '82 umol/L', 'referenceRange' => '60 - 110', 'flag' => 'Normal'],
            ['id' => 'p7', 'name' => 'Blood Urea Nitrogen', 'result' => '4.1 mmol/L', 'referenceRange' => '2.5 - 7.1', 'flag' => 'Normal']
        ]
    ]
];

$completedRecords = [
    [
        'accessionId' => 'ACC-2026-0038',
        'patientName' => 'Farai Gumbo',
        'dob' => '1998-09-17',
        'testType' => 'Glycated Haemoglobin (HbA1c)',
        'lab' => 'Apex Diagnostic Partners',
        'status' => 'Approved',
        'dateTime' => '2026-06-22 15:40',
        'authorizedScientist' => 'Dr. Chen',
        'authorizedTime' => '16:10 PM',
        'scientistNotes' => 'Glycated hemoglobin indicates stable glycemic management profile (Pre-diabetic threshold). No panic alerts requested.',
        'parameters' => [
            ['id' => 'p10', 'name' => 'HbA1c Concentration', 'result' => '5.9 %', 'referenceRange' => '4.0 - 5.6', 'flag' => 'High']
        ]
    ],
    [
        'accessionId' => 'ACC-2026-0039',
        'patientName' => 'Kudzanai Zhou',
        'dob' => '1965-02-28',
        'testType' => 'Liver Panel',
        'lab' => 'Valley Health LIS',
        'status' => 'Rejected',
        'dateTime' => '2026-06-22 14:15',
        'authorizedScientist' => 'Dr. S. Moyo',
        'authorizedTime' => '14:45 PM',
        'scientistNotes' => 'Sample Hemolyzed. Requested recollect action sequence from nursing workstation.',
        'parameters' => [
            ['id' => 'p11', 'name' => 'Alanine Aminotransferase (ALT)', 'result' => '280 U/L', 'referenceRange' => '7 - 56', 'flag' => 'Critically High']
        ]
    ]
];

$queueRecords = [
    [
        'accessionId' => 'ACC-2026-0044',
        'patientName' => 'Theresa Mpofu',
        'testType' => 'Serum Electrolytes',
        'lab' => 'Valley Health LIS',
        'scheduledTime' => '09:45 AM',
        'status' => 'Processing',
        'assignedScientist' => 'Scientist Moyo'
    ],
    [
        'accessionId' => 'ACC-2026-0045',
        'patientName' => 'Blessing Makoni',
        'testType' => 'Thyroid Panel (TSH/FT4)',
        'lab' => 'Downtown Medical Center',
        'scheduledTime' => '10:15 AM',
        'status' => 'Received',
        'assignedScientist' => 'Scientist Chen'
    ]
];
