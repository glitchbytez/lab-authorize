<?php
require_once __DIR__ . '/../Database.php';

class DatabaseDriver implements ApiContract
{
    // ── Records ──────────────────────────────────────────────────────────

    public function getPendingRecords(?string $labFilter = null): array
    {
        [$where, $params] = $this->recordsWhere(
            "r.status NOT IN ('Approved', 'Rejected')", $labFilter
        );

        $stmt = Database::getInstance()->prepare("
            SELECT
                r.accession_id       AS accessionId,
                r.patient_name       AS patientName,
                r.dob,
                r.test_type          AS testType,
                r.lab_name           AS lab,
                r.status,
                r.date_time          AS dateTime,
                r.ordering_physician AS orderingPhysician,
                r.scientist_notes    AS scientistNotes,
                p.id                 AS paramId,
                p.name               AS paramName,
                p.result,
                p.reference_range    AS referenceRange,
                p.flag
            FROM records r
            LEFT JOIN record_parameters p ON p.record_id = r.id
            WHERE {$where}
            ORDER BY r.id, p.id
        ");
        $stmt->execute($params);
        return $this->groupRecordsWithParameters($stmt->fetchAll());
    }

    public function getCompletedRecords(?string $labFilter = null): array
    {
        [$where, $params] = $this->recordsWhere(
            "r.status IN ('Approved', 'Rejected')", $labFilter
        );

        $stmt = Database::getInstance()->prepare("
            SELECT
                r.accession_id         AS accessionId,
                r.patient_name         AS patientName,
                r.dob,
                r.test_type            AS testType,
                r.lab_name             AS lab,
                r.status,
                r.date_time            AS dateTime,
                r.ordering_physician   AS orderingPhysician,
                r.scientist_notes      AS scientistNotes,
                r.authorized_scientist AS authorizedScientist,
                r.authorized_time      AS authorizedTime,
                p.id                   AS paramId,
                p.name                 AS paramName,
                p.result,
                p.reference_range      AS referenceRange,
                p.flag
            FROM records r
            LEFT JOIN record_parameters p ON p.record_id = r.id
            WHERE {$where}
            ORDER BY r.id DESC, p.id
        ");
        $stmt->execute($params);
        return $this->groupRecordsWithParameters($stmt->fetchAll());
    }

    public function verifyRecord(string $accessionId, string $notes, string $scientist): array
    {
        $stmt = Database::getInstance()->prepare("
            UPDATE records
            SET status               = 'Approved',
                scientist_notes      = ?,
                authorized_scientist = ?,
                authorized_time      = ?
            WHERE accession_id = ? AND status NOT IN ('Approved', 'Rejected')
        ");
        $stmt->execute([$notes, $scientist, date('g:i A'), $accessionId]);

        if ($stmt->rowCount() === 0) {
            throw new RuntimeException("Record {$accessionId} not found or already resolved.");
        }
        return ['success' => "Accession {$accessionId} verified and authorized for clinical release."];
    }

    public function rejectRecord(string $accessionId, string $notes, string $scientist): array
    {
        $stmt = Database::getInstance()->prepare("
            UPDATE records
            SET status               = 'Rejected',
                scientist_notes      = ?,
                authorized_scientist = ?,
                authorized_time      = ?
            WHERE accession_id = ? AND status NOT IN ('Approved', 'Rejected')
        ");
        $stmt->execute([$notes, $scientist, date('g:i A'), $accessionId]);

        if ($stmt->rowCount() === 0) {
            throw new RuntimeException("Record {$accessionId} not found or already resolved.");
        }
        return ['success' => "Accession {$accessionId} rejected."];
    }

    public function recheckRecord(string $accessionId, string $notes): array
    {
        $stmt = Database::getInstance()->prepare("
            UPDATE records SET scientist_notes = ?
            WHERE accession_id = ? AND status NOT IN ('Approved', 'Rejected')
        ");
        $stmt->execute([$notes, $accessionId]);

        if ($stmt->rowCount() === 0) {
            throw new RuntimeException("Record {$accessionId} not found.");
        }
        return ['success' => "Recheck request dispatched for {$accessionId}."];
    }

    // ── Labs ─────────────────────────────────────────────────────────────

    public function getLabs(): array
    {
        return Database::getInstance()
            ->query("SELECT name, ahfoz FROM labs ORDER BY name")
            ->fetchAll();
    }

    public function createLab(string $name, ?string $ahfoz): array
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Laboratory name is required.');
        }

        $pdo   = Database::getInstance();
        $check = $pdo->prepare("SELECT id FROM labs WHERE LOWER(name) = LOWER(?)");
        $check->execute([$name]);
        if ($check->fetch()) {
            throw new RuntimeException("Client Laboratory '{$name}' is already registered.");
        }

        $pdo->prepare("INSERT INTO labs (name, ahfoz) VALUES (?, ?)")->execute([$name, $ahfoz]);

        return [
            'success' => "Facility successfully authorized and registered.",
            'labs'    => $this->getLabs(),
        ];
    }

    public function deleteLab(string $name): array
    {
        $pdo   = Database::getInstance();
        $count = $pdo->prepare("SELECT COUNT(*) FROM users WHERE lab_name = ?");
        $count->execute([$name]);

        if ((int) $count->fetchColumn() > 0) {
            throw new RuntimeException("Cannot decommission '{$name}' when active specialists are rostered.");
        }

        $pdo->prepare("DELETE FROM labs WHERE name = ?")->execute([$name]);

        return [
            'success' => "Client laboratory decommissioned successfully.",
            'labs'    => $this->getLabs(),
        ];
    }

    // ── Users ────────────────────────────────────────────────────────────

    public function getUsers(): array
    {
        return Database::getInstance()
            ->query("SELECT id, name, role, lab_name AS lab FROM users ORDER BY name")
            ->fetchAll();
    }

    public function createUser(string $name, string $role, ?string $lab, string $password): array
    {
        if (empty($name) || empty($role) || empty($password)) {
            throw new InvalidArgumentException('All fields (Name, Role, Password) are required.');
        }

        $pdo   = Database::getInstance();
        $check = $pdo->prepare("SELECT id FROM users WHERE LOWER(name) = LOWER(?)");
        $check->execute([$name]);
        if ($check->fetch()) {
            throw new RuntimeException("A specialist named '{$name}' is already rostered.");
        }

        $maxId = 0;
        foreach ($pdo->query("SELECT id FROM users")->fetchAll() as $row) {
            if (preg_match('/(?:scientist|admin|user)-(\d+)/', $row['id'], $m)) {
                $maxId = max($maxId, (int) $m[1]);
            }
        }

        $newId  = 'user-' . sprintf('%02d', $maxId + 1);
        $labVal = ($lab === '' || $lab === 'None') ? null : $lab;

        $pdo->prepare("INSERT INTO users (id, name, role, lab_name, password_hash) VALUES (?, ?, ?, ?, ?)")
            ->execute([$newId, $name, $role, $labVal, password_hash($password, PASSWORD_BCRYPT)]);

        return [
            'success' => "User account successfully registered and active.",
            'users'   => $this->getUsers(),
        ];
    }

    public function deleteUser(string $userId): array
    {
        Database::getInstance()
            ->prepare("DELETE FROM users WHERE id = ?")
            ->execute([$userId]);

        return [
            'success' => "User account decommissioned successfully.",
            'users'   => $this->getUsers(),
        ];
    }

    // ── Private helpers ──────────────────────────────────────────────────

    /**
     * Builds the WHERE clause and bound params for record queries.
     * When $labFilter is set, results are scoped to that lab only.
     */
    private function recordsWhere(string $statusClause, ?string $labFilter): array
    {
        if ($labFilter === null) {
            return [$statusClause, []];
        }
        return ["{$statusClause} AND r.lab_name = ?", [$labFilter]];
    }

    private function groupRecordsWithParameters(array $rows): array
    {
        $records = [];

        foreach ($rows as $row) {
            $id = $row['accessionId'];

            if (!isset($records[$id])) {
                $record = ['parameters' => []];

                foreach (['accessionId', 'patientName', 'dob', 'testType', 'lab', 'status',
                          'dateTime', 'orderingPhysician', 'scientistNotes'] as $col) {
                    $record[$col] = $row[$col] ?? null;
                }
                // completed-only fields — only set if present in the query
                foreach (['authorizedScientist', 'authorizedTime'] as $col) {
                    if (array_key_exists($col, $row)) {
                        $record[$col] = $row[$col];
                    }
                }

                $records[$id] = $record;
            }

            if ($row['paramId'] !== null) {
                $records[$id]['parameters'][] = [
                    'id'             => (string) $row['paramId'],
                    'name'           => $row['paramName'],
                    'result'         => $row['result'],
                    'referenceRange' => $row['referenceRange'],
                    'flag'           => $row['flag'],
                ];
            }
        }

        return array_values($records);
    }
}
