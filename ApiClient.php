<?php
/**
 * ApiClient.php
 *
 * Central PHP API Client — Backend-for-Frontend (BFF) Gateway.
 *
 * ─────────────────────────────────────────────────────────────────────
 *  HOW IT WORKS (Production):
 * ─────────────────────────────────────────────────────────────────────
 *  1. Set API_BASE_URL to your real LIS API endpoint.
 *  2. Set API_KEY to your secret bearer token / API key.
 *  3. Remove the "MOCK MODE" section below (or set USE_MOCK = false).
 *  4. Every method will then forward the call to the real API via cURL.
 *
 *  The browser never sees the API key — it only talks to api.php locally.
 * ─────────────────────────────────────────────────────────────────────
 *  HOW IT WORKS (Mock / Development):
 * ─────────────────────────────────────────────────────────────────────
 *  When USE_MOCK = true (default), ApiClient reads from and writes to
 *  $_SESSION, simulating a real API. Swap to false when your LIS API
 *  is ready.
 * ─────────────────────────────────────────────────────────────────────
 */

class ApiClient
{
    // ─── Configuration ────────────────────────────────────────────────
    private const API_BASE_URL = 'https://api.lis-provider.co.zw/v1'; // Replace with real LIS API base URL
    private const API_KEY      = 'YOUR_LIS_API_KEY_HERE';              // Replace with real secret key
    private const USE_MOCK     = true;                                  // Set false when real API is ready
    private const TIMEOUT      = 15;                                    // cURL request timeout in seconds

    // ─── HTTP Transport (used in production) ──────────────────────────

    /**
     * Sends a cURL request to the real LIS API.
     *
     * @param string      $endpoint  e.g. '/records/pending'
     * @param string      $method    'GET' | 'POST' | 'PUT' | 'DELETE'
     * @param array|null  $payload   Request body data (JSON encoded automatically)
     *
     * @return array Decoded JSON response
     * @throws RuntimeException On network or HTTP failure
     */
    private static function request(string $endpoint, string $method = 'GET', ?array $payload = null): array
    {
        $url  = rtrim(self::API_BASE_URL, '/') . '/' . ltrim($endpoint, '/');

        $curl = curl_init();

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . self::API_KEY,
        ];

        $curlOpts = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
        ];

        if ($payload !== null && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            $curlOpts[CURLOPT_POSTFIELDS] = json_encode($payload);
        }

        curl_setopt_array($curl, $curlOpts);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($curl);
        curl_close($curl);

        if ($curlErr) {
            throw new RuntimeException("LIS API connection error: {$curlErr}");
        }

        $decoded = json_decode($response, true);

        if ($httpCode >= 400) {
            $msg = $decoded['message'] ?? $decoded['error'] ?? "HTTP {$httpCode} error from LIS API.";
            throw new RuntimeException($msg);
        }

        return $decoded ?? [];
    }

    // ─── Public API Methods ───────────────────────────────────────────

    /**
     * Retrieve all pending (awaiting authorization) diagnostic records.
     */
    public static function getPendingRecords(): array
    {
        if (self::USE_MOCK) {
            return self::mock_getPendingRecords();
        }
        return self::request('/records/pending', 'GET');
    }

    /**
     * Retrieve all completed (authorized or rejected) diagnostic records.
     */
    public static function getCompletedRecords(): array
    {
        if (self::USE_MOCK) {
            return self::mock_getCompletedRecords();
        }
        return self::request('/records/completed', 'GET');
    }

    /**
     * Retrieve all registered client laboratory facilities.
     */
    public static function getLabs(): array
    {
        if (self::USE_MOCK) {
            return $_SESSION['client_labs'] ?? [];
        }
        return self::request('/labs', 'GET');
    }

    /**
     * Retrieve all registered personnel / user accounts.
     */
    public static function getUsers(): array
    {
        if (self::USE_MOCK) {
            return $_SESSION['scientists'] ?? [];
        }
        return self::request('/users', 'GET');
    }

    /**
     * Register a new client laboratory facility.
     *
     * @param string      $labName   Facility name
     * @param string|null $ahfoz     Optional AHFOZ registration number
     */
    public static function createLab(string $labName, ?string $ahfoz = null): array
    {
        if (self::USE_MOCK) {
            return self::mock_createLab($labName, $ahfoz);
        }
        return self::request('/labs', 'POST', [
            'name'  => $labName,
            'ahfoz' => $ahfoz,
        ]);
    }

    /**
     * Decommission / delete a client laboratory facility.
     *
     * @param string $labName Facility name to remove
     */
    public static function deleteLab(string $labName): array
    {
        if (self::USE_MOCK) {
            return self::mock_deleteLab($labName);
        }
        return self::request('/labs/' . urlencode($labName), 'DELETE');
    }

    /**
     * Register a new personnel / user account.
     *
     * @param string      $name      Full name
     * @param string      $role      System role (Lab Scientist | Administrator | LIS Manager)
     * @param string|null $lab       Assigned client lab (null for system-wide)
     * @param string      $password  Account access key
     */
    public static function createUser(string $name, string $role, ?string $lab, string $password): array
    {
        if (self::USE_MOCK) {
            return self::mock_createUser($name, $role, $lab, $password);
        }
        return self::request('/users', 'POST', [
            'name'     => $name,
            'role'     => $role,
            'lab'      => $lab,
            'password' => $password,
        ]);
    }

    /**
     * Decommission / delete a personnel account.
     *
     * @param string $userId  Internal user ID
     */
    public static function deleteUser(string $userId): array
    {
        if (self::USE_MOCK) {
            return self::mock_deleteUser($userId);
        }
        return self::request('/users/' . urlencode($userId), 'DELETE');
    }

    /**
     * Verify and authorize a pending diagnostic record.
     *
     * @param string $accessionId    Diagnostic accession number
     * @param string $scientistNotes Clinical annotations from the authorizing scientist
     * @param string $scientistName  Name of the authorizing scientist
     */
    public static function verifyRecord(string $accessionId, string $scientistNotes, string $scientistName): array
    {
        if (self::USE_MOCK) {
            return self::mock_resolveRecord($accessionId, $scientistNotes, $scientistName, 'Approved');
        }
        return self::request('/records/' . urlencode($accessionId) . '/verify', 'POST', [
            'scientistNotes' => $scientistNotes,
            'authorizedBy'   => $scientistName,
        ]);
    }

    /**
     * Reject a pending diagnostic record.
     *
     * @param string $accessionId    Diagnostic accession number
     * @param string $scientistNotes Clinical annotations / rejection rationale
     * @param string $scientistName  Name of the rejecting scientist
     */
    public static function rejectRecord(string $accessionId, string $scientistNotes, string $scientistName): array
    {
        if (self::USE_MOCK) {
            return self::mock_resolveRecord($accessionId, $scientistNotes, $scientistName, 'Rejected');
        }
        return self::request('/records/' . urlencode($accessionId) . '/reject', 'POST', [
            'scientistNotes' => $scientistNotes,
            'rejectedBy'     => $scientistName,
        ]);
    }

    /**
     * Flag a pending record for recheck without moving it to completed.
     *
     * @param string $accessionId    Diagnostic accession number
     * @param string $scientistNotes Recheck rationale / updated notes
     */
    public static function recheckRecord(string $accessionId, string $scientistNotes): array
    {
        if (self::USE_MOCK) {
            return self::mock_recheckRecord($accessionId, $scientistNotes);
        }
        return self::request('/records/' . urlencode($accessionId) . '/recheck', 'POST', [
            'scientistNotes' => $scientistNotes,
        ]);
    }

    // ─── Mock Implementations (Development / Session State) ───────────
    // When USE_MOCK = true, these methods read/write from $_SESSION.
    // Replace with real API calls by setting USE_MOCK = false above.

    private static function mock_getPendingRecords(): array
    {
        if (!isset($_SESSION['pending_records'])) {
            $_SESSION['pending_records'] = self::defaultPendingRecords();
        }
        return $_SESSION['pending_records'];
    }

    private static function mock_getCompletedRecords(): array
    {
        if (!isset($_SESSION['completed_records'])) {
            $_SESSION['completed_records'] = self::defaultCompletedRecords();
        }
        return $_SESSION['completed_records'];
    }

    private static function mock_createLab(string $labName, ?string $ahfoz): array
    {
        if (empty($labName)) {
            throw new InvalidArgumentException('Laboratory name is required.');
        }

        foreach ($_SESSION['client_labs'] as $lab) {
            if (strtolower($lab['name']) === strtolower($labName)) {
                throw new RuntimeException("Client Laboratory '{$labName}' is already registered.");
            }
        }

        $_SESSION['client_labs'][] = [
            'name'  => $labName,
            'ahfoz' => !empty($ahfoz) ? $ahfoz : null,
        ];

        return [
            'success' => "Facility successfully authorized and registered.",
            'labs'    => $_SESSION['client_labs'],
        ];
    }

    private static function mock_deleteLab(string $labName): array
    {
        // Check for active specialists
        $count = 0;
        foreach ($_SESSION['scientists'] as $sc) {
            if ($sc['lab'] === $labName) {
                $count++;
            }
        }

        if ($count > 0) {
            throw new RuntimeException("Cannot decommission '{$labName}' when active specialists are rostered.");
        }

        $_SESSION['client_labs'] = array_values(
            array_filter($_SESSION['client_labs'], fn($l) => $l['name'] !== $labName)
        );

        return [
            'success' => "Client laboratory decommissioned successfully.",
            'labs'    => $_SESSION['client_labs'],
        ];
    }

    private static function mock_createUser(string $name, string $role, ?string $lab, string $password): array
    {
        if (empty($name) || empty($role) || empty($password)) {
            throw new InvalidArgumentException('All fields (Name, Role, Password) are required.');
        }

        foreach ($_SESSION['scientists'] as $sc) {
            if (strtolower($sc['name']) === strtolower($name)) {
                throw new RuntimeException("A specialist named '{$name}' is already rostered.");
            }
        }

        // Auto-generate incremental ID
        $maxIdNum = 0;
        foreach ($_SESSION['scientists'] as $sc) {
            if (preg_match('/(?:scientist|admin|user)-(\d+)/', $sc['id'], $m)) {
                $maxIdNum = max($maxIdNum, (int)$m[1]);
            }
        }
        $newId = 'user-' . sprintf('%02d', $maxIdNum + 1);

        $_SESSION['scientists'][] = [
            'id'       => $newId,
            'name'     => $name,
            'role'     => $role,
            'lab'      => ($lab === '' || $lab === 'None') ? null : $lab,
            'password' => $password,
        ];

        return [
            'success' => "User account successfully registered and active.",
            'users'   => $_SESSION['scientists'],
        ];
    }

    private static function mock_deleteUser(string $userId): array
    {
        $_SESSION['scientists'] = array_values(
            array_filter($_SESSION['scientists'], fn($sc) => $sc['id'] !== $userId)
        );

        return [
            'success' => "User account decommissioned successfully.",
            'users'   => $_SESSION['scientists'],
        ];
    }

    private static function mock_resolveRecord(
        string $accessionId,
        string $notes,
        string $scientistName,
        string $resolution
    ): array {
        $pending = &$_SESSION['pending_records'];

        $foundIndex = -1;
        foreach ($pending as $idx => $rec) {
            if ($rec['accessionId'] === $accessionId) {
                $foundIndex = $idx;
                break;
            }
        }

        if ($foundIndex === -1) {
            throw new RuntimeException("Record {$accessionId} not found in the pending authorization queue.");
        }

        $record                       = $pending[$foundIndex];
        $record['scientistNotes']     = $notes;
        $record['status']             = $resolution;
        $record['authorizedScientist'] = $scientistName;
        $record['authorizedTime']     = date('H:i A');

        $_SESSION['completed_records'][] = $record;
        array_splice($pending, $foundIndex, 1);

        $verb = $resolution === 'Approved' ? 'verified and authorized for clinical release' : 'rejected';
        return ['success' => "Accession {$accessionId} {$verb}."];
    }

    private static function mock_recheckRecord(string $accessionId, string $notes): array
    {
        $pending = &$_SESSION['pending_records'];

        foreach ($pending as &$rec) {
            if ($rec['accessionId'] === $accessionId) {
                $rec['scientistNotes'] = $notes;
                return ['success' => "Recheck request dispatched for {$accessionId}."];
            }
        }

        throw new RuntimeException("Record {$accessionId} not found.");
    }

    // ─── Default Seed Data ────────────────────────────────────────────

    private static function defaultPendingRecords(): array
    {
        return [
            [
                'accessionId'      => 'ACC-2026-0041',
                'patientName'      => 'Oliver Mwanga',
                'dob'              => '1991-04-12',
                'testType'         => 'Full Blood Count',
                'lab'              => 'Downtown Medical Center',
                'status'           => 'Urgent',
                'dateTime'         => '2026-06-23 08:14',
                'orderingPhysician'=> 'Dr. E. Thompson',
                'scientistNotes'   => 'Slightly elevated WBC counts observed. Red blood platelet distribution indices normal.',
                'parameters'       => [
                    ['id' => 'p1', 'name' => 'White Blood Cell (WBC)',  'result' => '11.8 x10^9/L',  'referenceRange' => '4.5 - 11.0', 'flag' => 'High'],
                    ['id' => 'p2', 'name' => 'Red Blood Cell (RBC)',    'result' => '4.85 x10^12/L', 'referenceRange' => '4.30 - 5.90','flag' => 'Normal'],
                    ['id' => 'p3', 'name' => 'Haemoglobin',             'result' => '14.2 g/dL',     'referenceRange' => '13.5 - 17.5','flag' => 'Normal'],
                ],
            ],
            [
                'accessionId'      => 'ACC-2026-0042',
                'patientName'      => 'Sarah Ndlovu',
                'dob'              => '1984-11-03',
                'testType'         => 'Lipid Profile',
                'lab'              => 'St. Jude Clinical Laboratory',
                'status'           => 'Critical',
                'dateTime'         => '2026-06-23 08:30',
                'orderingPhysician'=> 'Dr. T. Sibanda',
                'scientistNotes'   => 'Critical total cholesterol concentration logged.',
                'parameters'       => [
                    ['id' => 'p4', 'name' => 'Total Cholesterol', 'result' => '6.8 mmol/L', 'referenceRange' => '< 5.2', 'flag' => 'Critically High'],
                    ['id' => 'p5', 'name' => 'HDL Cholesterol',   'result' => '0.9 mmol/L', 'referenceRange' => '> 1.0', 'flag' => 'Critically Low'],
                ],
            ],
            [
                'accessionId'      => 'ACC-2026-0043',
                'patientName'      => 'Tinashe Mariga',
                'dob'              => '1976-07-22',
                'testType'         => 'Renal Function',
                'lab'              => 'Apex Diagnostic Partners',
                'status'           => 'Routine',
                'dateTime'         => '2026-06-23 09:15',
                'orderingPhysician'=> 'Dr. A. Mutasa',
                'scientistNotes'   => 'Urea and serum creatinine parameters are within standard baseline thresholds.',
                'parameters'       => [
                    ['id' => 'p6', 'name' => 'Serum Creatinine',      'result' => '82 umol/L',  'referenceRange' => '60 - 110', 'flag' => 'Normal'],
                    ['id' => 'p7', 'name' => 'Blood Urea Nitrogen',   'result' => '4.1 mmol/L', 'referenceRange' => '2.5 - 7.1','flag' => 'Normal'],
                ],
            ],
        ];
    }

    private static function defaultCompletedRecords(): array
    {
        return [
            [
                'accessionId'        => 'ACC-2026-0038',
                'patientName'        => 'Farai Gumbo',
                'dob'                => '1998-09-17',
                'testType'           => 'Glycated Haemoglobin (HbA1c)',
                'lab'                => 'Apex Diagnostic Partners',
                'status'             => 'Approved',
                'dateTime'           => '2026-06-22 15:40',
                'authorizedScientist'=> 'Dr. Andrew Chen',
                'authorizedTime'     => '16:10 PM',
                'scientistNotes'     => 'Glycated hemoglobin indicates stable glycemic management profile (Pre-diabetic threshold). No panic alerts requested.',
                'parameters'         => [
                    ['id' => 'p10', 'name' => 'HbA1c Concentration', 'result' => '5.9 %', 'referenceRange' => '4.0 - 5.6', 'flag' => 'High'],
                ],
            ],
            [
                'accessionId'        => 'ACC-2026-0039',
                'patientName'        => 'Kudzanai Zhou',
                'dob'                => '1965-02-28',
                'testType'           => 'Liver Panel',
                'lab'                => 'Valley Health LIS',
                'status'             => 'Rejected',
                'dateTime'           => '2026-06-22 14:15',
                'authorizedScientist'=> 'Dr. S. Moyo',
                'authorizedTime'     => '14:45 PM',
                'scientistNotes'     => 'Sample Hemolyzed. Requested recollect action sequence from nursing workstation.',
                'parameters'         => [
                    ['id' => 'p11', 'name' => 'Alanine Aminotransferase (ALT)', 'result' => '280 U/L', 'referenceRange' => '7 - 56', 'flag' => 'Critically High'],
                ],
            ],
        ];
    }
}
