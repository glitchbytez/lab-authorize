<?php
require_once 'config.php';
require_once 'api/bootstrap.php';

// Route Handlers and Administrative Actions
$error = '';
$success = '';

// A. Handle Form POSTs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Authenticate login — always uses local DB regardless of API_DRIVER
    if ($action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        try {
            $pdo  = Database::getInstance();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(name) = LOWER(?) OR LOWER(id) = LOWER(?)");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $user['lab'] = $user['lab_name'];
                unset($user['password_hash'], $user['lab_name'], $user['created_at']);
                $_SESSION['user'] = $user;
                header("Location: index.php?page=pending");
                exit;
            }
        } catch (Throwable $e) {
            $error = 'System error during authentication. Please try again.';
        }

        if (empty($error)) {
            $error = 'Authentication denied. Incorrect credentials supplied.';
        }
    }

    // 2–5. CRUD actions — delegated to the active driver
    // (admin.php handles these via api.js; these handlers exist as a fallback)
    if (in_array($action, ['create_lab', 'delete_lab', 'create_user', 'delete_user'])) {
        $api = ApiClient::make();

        try {
            switch ($action) {
                case 'create_lab':
                    $labName = trim($_POST['lab_name'] ?? '');
                    if (empty($labName)) {
                        $error = 'Please enter a valid laboratory name.';
                    } else {
                        $result  = $api->createLab($labName, trim($_POST['ahfoz_number'] ?? '') ?: null);
                        $success = $result['success'];
                    }
                    break;

                case 'delete_lab':
                    $result  = $api->deleteLab(trim($_POST['lab_name'] ?? ''));
                    $success = $result['success'];
                    break;

                case 'create_user':
                    $name     = trim($_POST['name']     ?? '');
                    $role     = trim($_POST['role']      ?? '');
                    $password = trim($_POST['password']  ?? '');
                    if (empty($name) || empty($role) || empty($password)) {
                        $error = 'All fields (Name, Role, Password) are required.';
                    } else {
                        $result  = $api->createUser($name, $role, trim($_POST['lab'] ?? '') ?: null, $password);
                        $success = $result['success'];
                    }
                    break;

                case 'delete_user':
                    $userId = trim($_POST['user_id'] ?? '');
                    if ($_SESSION['user']['id'] === $userId) {
                        $error = "Security Policy Violation: You cannot delete your own active profile.";
                    } else {
                        $result  = $api->deleteUser($userId);
                        $success = $result['success'];
                    }
                    break;
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

// B. Route Selection
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'pending';
if (!isset($_SESSION['user'])) {
    $currentPage = 'login';
}

if ($currentPage === 'logout') {
    unset($_SESSION['user']);
    session_destroy();
    header("Location: index.php?page=login");
    exit;
}

// Assembly
if ($currentPage === 'login') {
    include 'views/login.php';
} else {
    include 'header.php';
    echo '<div class="flex flex-col md:flex-row min-h-[calc(100vh-64px)]">';
    include 'sidebar.php';
    echo '<main class="flex-1 p-6 bg-slate-50">';

    // Page selection
    switch ($currentPage) {
        case 'pending':
            include 'views/pending_auth.php';
            break;
        case 'completed':
            include 'views/completed.php';
            break;
        case 'admin':
            include 'views/admin.php';
            break;
        default:
            include 'views/pending_auth.php';
    }

    echo '</main></div>';
    echo '</body></html>';
}
