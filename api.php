<?php
/**
 * api.php — Local API Gateway
 *
 * Acts as the single HTTP endpoint the browser's JavaScript talks to.
 * All business logic and real LIS API communication is handled by ApiClient.php.
 *
 * Flow:
 *   Browser (fetch) → api.php (gateway) → ApiClient.php → Real LIS API
 *                                                       OR $_SESSION (mock mode)
 */

require_once 'config.php';
require_once 'ApiClient.php';

header('Content-Type: application/json');

// ─── Authentication Guard ────────────────────────────────────────────
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized. Please login to access the LIS API.']);
    exit;
}

$method   = $_SERVER['REQUEST_METHOD'];
$action   = $_GET['action']   ?? '';
$resource = $_GET['resource'] ?? '';

// ─── Helper: send a JSON error and exit ─────────────────────────────
function apiError(string $message, int $code = 400): void
{
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

// ─── Helper: send a JSON success and exit ───────────────────────────
function apiSuccess(array $data): void
{
    echo json_encode($data);
    exit;
}

// ─── GET — Resource Fetching ─────────────────────────────────────────
if ($method === 'GET') {
    try {
        switch ($resource) {
            case 'pending':
                apiSuccess(ApiClient::getPendingRecords());

            case 'completed':
                apiSuccess(ApiClient::getCompletedRecords());

            case 'labs':
                apiSuccess(ApiClient::getLabs());

            case 'users':
                apiSuccess(ApiClient::getUsers());

            default:
                apiError("Unknown resource '{$resource}'. Valid options: pending, completed, labs, users.");
        }
    } catch (Throwable $e) {
        apiError($e->getMessage(), 502);
    }
}

// ─── POST — Action Mutations ──────────────────────────────────────────
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        $input = $_POST;
    }

    try {
        switch ($action) {

            // ── Facility Management ──────────────────────────────────
            case 'create_lab':
                $labName = trim($input['lab_name']    ?? '');
                $ahfoz   = trim($input['ahfoz_number'] ?? '') ?: null;

                if (empty($labName)) {
                    apiError('Laboratory name is required.');
                }

                apiSuccess(ApiClient::createLab($labName, $ahfoz));

            case 'delete_lab':
                $labName = trim($input['lab_name'] ?? '');

                if (empty($labName)) {
                    apiError('Laboratory name is required.');
                }

                apiSuccess(ApiClient::deleteLab($labName));

            // ── User Management ──────────────────────────────────────
            case 'create_user':
                $name     = trim($input['name']     ?? '');
                $role     = trim($input['role']     ?? '');
                $lab      = trim($input['lab']      ?? '') ?: null;
                $password = trim($input['password'] ?? '');

                if (empty($name) || empty($role) || empty($password)) {
                    apiError('All fields (Name, Role, Password) are required.');
                }

                apiSuccess(ApiClient::createUser($name, $role, $lab, $password));

            case 'delete_user':
                $userId = trim($input['user_id'] ?? '');

                if (empty($userId)) {
                    apiError('User ID is required.');
                }

                // Security: prevent self-deletion at the gateway level
                if ($_SESSION['user']['id'] === $userId) {
                    apiError('Security Policy Violation: You cannot delete your own active profile.');
                }

                apiSuccess(ApiClient::deleteUser($userId));

            // ── Record Authorization Actions ─────────────────────────
            case 'verify_record':
                $accessionId    = trim($input['accessionId']    ?? '');
                $scientistNotes = trim($input['scientistNotes'] ?? '');

                if (empty($accessionId)) {
                    apiError('Accession ID is required.');
                }

                apiSuccess(ApiClient::verifyRecord(
                    $accessionId,
                    $scientistNotes,
                    $_SESSION['user']['name']
                ));

            case 'reject_record':
                $accessionId    = trim($input['accessionId']    ?? '');
                $scientistNotes = trim($input['scientistNotes'] ?? '');

                if (empty($accessionId)) {
                    apiError('Accession ID is required.');
                }

                apiSuccess(ApiClient::rejectRecord(
                    $accessionId,
                    $scientistNotes,
                    $_SESSION['user']['name']
                ));

            case 'recheck_record':
                $accessionId    = trim($input['accessionId']    ?? '');
                $scientistNotes = trim($input['scientistNotes'] ?? '');

                if (empty($accessionId)) {
                    apiError('Accession ID is required.');
                }

                apiSuccess(ApiClient::recheckRecord($accessionId, $scientistNotes));

            // ── Fallback ─────────────────────────────────────────────
            default:
                apiError("Unknown action '{$action}'.");
        }
    } catch (Throwable $e) {
        apiError($e->getMessage(), 500);
    }
}

// ─── Unsupported Method ───────────────────────────────────────────────
apiError('Method not allowed.', 405);
