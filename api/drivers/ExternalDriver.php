<?php
class ExternalDriver implements ApiContract
{
    private string $baseUrl;
    private string $apiKey;
    private int    $timeout = 15;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('EXTERNAL_API_URL', ''), '/');
        $this->apiKey  = env('EXTERNAL_API_KEY', '');
    }

    // ── Records ──────────────────────────────────────────────────────────

    public function getPendingRecords(): array
    {
        return $this->request('/records/pending');
    }

    public function getCompletedRecords(): array
    {
        return $this->request('/records/completed');
    }

    public function verifyRecord(string $accessionId, string $notes, string $scientist): array
    {
        return $this->request('/records/' . urlencode($accessionId) . '/verify', 'POST', [
            'scientistNotes' => $notes,
            'authorizedBy'   => $scientist,
        ]);
    }

    public function rejectRecord(string $accessionId, string $notes, string $scientist): array
    {
        return $this->request('/records/' . urlencode($accessionId) . '/reject', 'POST', [
            'scientistNotes' => $notes,
            'rejectedBy'     => $scientist,
        ]);
    }

    public function recheckRecord(string $accessionId, string $notes): array
    {
        return $this->request('/records/' . urlencode($accessionId) . '/recheck', 'POST', [
            'scientistNotes' => $notes,
        ]);
    }

    // ── Labs ─────────────────────────────────────────────────────────────

    public function getLabs(): array
    {
        return $this->request('/labs');
    }

    public function createLab(string $name, ?string $ahfoz): array
    {
        return $this->request('/labs', 'POST', ['name' => $name, 'ahfoz' => $ahfoz]);
    }

    public function deleteLab(string $name): array
    {
        return $this->request('/labs/' . urlencode($name), 'DELETE');
    }

    // ── Users ────────────────────────────────────────────────────────────

    public function getUsers(): array
    {
        return $this->request('/users');
    }

    public function createUser(string $name, string $role, ?string $lab, string $password): array
    {
        return $this->request('/users', 'POST', [
            'name'     => $name,
            'role'     => $role,
            'lab'      => $lab,
            'password' => $password,
        ]);
    }

    public function deleteUser(string $userId): array
    {
        return $this->request('/users/' . urlencode($userId), 'DELETE');
    }

    // ── HTTP transport ────────────────────────────────────────────────────

    private function request(string $endpoint, string $method = 'GET', ?array $payload = null): array
    {
        $url  = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $curl = curl_init();

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ];

        $opts = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
        ];

        if ($payload !== null && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($payload);
        }

        curl_setopt_array($curl, $opts);

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
}
