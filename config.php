<?php
// Start session for state persistence
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Initialize Default Client Facility Laboratories
if (!isset($_SESSION['client_labs'])) {
    $_SESSION['client_labs'] = [
        ['name' => 'Downtown Medical Center', 'ahfoz' => 'AH 24/098/B'],
        ['name' => 'St. Jude Clinical Laboratory', 'ahfoz' => 'JH 25/714/S'],
        ['name' => 'Apex Diagnostic Partners', 'ahfoz' => 'AP 23/110/A'],
        ['name' => 'Valley Health LIS', 'ahfoz' => 'VH 26/030/V']
    ];
}

// 2. Initialize Laboratory Specialists (Scientists & Admins)
if (!isset($_SESSION['scientists'])) {
    $_SESSION['scientists'] = [
        ['id' => 'admin-01', 'name' => 'Dr. Andrew Chen', 'role' => 'Administrator', 'lab' => null, 'password' => 'admin123'],
        ['id' => 'scientist-01', 'name' => 'S. Sibanda', 'role' => 'Lab Scientist', 'lab' => 'Downtown Medical Center', 'password' => 'password123'],
        ['id' => 'scientist-02', 'name' => 'M. Moyo', 'role' => 'Lab Scientist', 'lab' => 'St. Jude Clinical Laboratory', 'password' => 'password123']
    ];
}

// 3. Initialize Patient Test Records
if (!isset($_SESSION['records'])) {
    $_SESSION['records'] = [
        [
            'accessionId' => 'ACC-2026-610',
            'patientName' => 'Prince Nyoni',
            'dob' => '12/04/1988',
            'testType' => 'FBC (Full Blood Count)',
            'lab' => 'Downtown Medical Center',
            'status' => 'Pending Review',
            'results' => [['parameter' => 'WBC', 'value' => '11.5', 'units' => '10^9/L', 'range' => '4.0 - 11.0', 'flag' => 'High']],
            'submittedBy' => 'S. Sibanda',
            'timestamp' => '10:45 AM',
            'orderingPhysician' => 'Dr. T. Gumbo'
        ]
    ];
}

// Helper: Count specialists active in a specific lab
function getLabSpecialistsCount($labName) {
    $count = 0;
    foreach ($_SESSION['scientists'] as $sc) {
        if ($sc['lab'] === $labName) {
            $count++;
        }
    }
    return $count;
}
