<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json');

Auth::requireLogin();

$method   = $_SERVER['REQUEST_METHOD'];
$action   = $_GET['action']   ?? '';
$resource = $_GET['resource'] ?? '';
$api      = ApiClient::make();

function apiError(string $message, int $code = 400): void
{
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

function apiSuccess(array $data): void
{
    echo json_encode($data);
    exit;
}

// ── GET — resource fetching ───────────────────────────────────────────────
if ($method === 'GET') {
    try {
        switch ($resource) {
            case 'pending':   apiSuccess($api->getPendingRecords());
            case 'completed': apiSuccess($api->getCompletedRecords());
            case 'labs':      apiSuccess($api->getLabs());
            case 'users':     apiSuccess($api->getUsers());
            default:          apiError("Unknown resource '{$resource}'.");
        }
    } catch (Throwable $e) {
        apiError($e->getMessage(), 502);
    }
}

// ── POST — action mutations ───────────────────────────────────────────────
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        $input = $_POST;
    }

    try {
        switch ($action) {

            case 'create_lab':
                apiSuccess($api->createLab(
                    trim($input['lab_name']     ?? ''),
                    trim($input['ahfoz_number'] ?? '') ?: null
                ));

            case 'delete_lab':
                apiSuccess($api->deleteLab(trim($input['lab_name'] ?? '')));

            case 'create_user':
                apiSuccess($api->createUser(
                    trim($input['name']     ?? ''),
                    trim($input['role']     ?? ''),
                    trim($input['lab']      ?? '') ?: null,
                    trim($input['password'] ?? '')
                ));

            case 'delete_user':
                $userId = trim($input['user_id'] ?? '');
                if ($userId === Auth::currentUser()['id']) {
                    apiError('Security Policy Violation: You cannot delete your own active profile.');
                }
                apiSuccess($api->deleteUser($userId));

            case 'verify_record':
                apiSuccess($api->verifyRecord(
                    trim($input['accessionId']    ?? ''),
                    trim($input['scientistNotes'] ?? ''),
                    Auth::currentUser()['name']
                ));

            case 'reject_record':
                apiSuccess($api->rejectRecord(
                    trim($input['accessionId']    ?? ''),
                    trim($input['scientistNotes'] ?? ''),
                    Auth::currentUser()['name']
                ));

            case 'recheck_record':
                apiSuccess($api->recheckRecord(
                    trim($input['accessionId']    ?? ''),
                    trim($input['scientistNotes'] ?? '')
                ));

            default:
                apiError("Unknown action '{$action}'.");
        }
    } catch (Throwable $e) {
        apiError($e->getMessage(), 500);
    }
}

apiError('Method not allowed.', 405);
