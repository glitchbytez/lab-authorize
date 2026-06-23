<?php
require_once 'config.php';

// Route Handlers and Administrative Actions
$error = '';
$success = '';

// A. Handle Form POSTs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Authenticate login
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        foreach ($_SESSION['scientists'] as $sc) {
            if (strtolower($sc['name']) === strtolower($username) || strtolower($sc['id']) === strtolower($username)) {
                if ($sc['password'] === $password) {
                    $_SESSION['user'] = $sc;
                    header("Location: index.php?page=queue");
                    exit;
                }
            }
        }
        $error = 'Authentication denied. Incorrect credentials supplied.';
    }

    // 2. Register new facility with optional AHFOZ Number
    if (isset($_POST['action']) && $_POST['action'] === 'create_lab') {
        $labName = trim($_POST['lab_name']);
        $ahfoz = trim($_POST['ahfoz_number']);

        if (empty($labName)) {
            $error = 'Please enter a valid laboratory name.';
        } else {
            // Check uniqueness
            $exists = false;
            foreach ($_SESSION['client_labs'] as $lab) {
                if (strtolower($lab['name']) === strtolower($labName)) {
                    $exists = true;
                    break;
                }
            }
            if ($exists) {
                $error = "Client Laboratory '{$labName}' is already registered.";
            } else {
                $_SESSION['client_labs'][] = [
                    'name' => $labName,
                    'ahfoz' => !empty($ahfoz) ? $ahfoz : null
                ];
                $success = "Facility successfully authorized and listed under Zimbabwe Health Service.";
            }
        }
    }

    // 3. Decommission / Delete facility
    if (isset($_POST['action']) && $_POST['action'] === 'delete_lab') {
        $labName = $_POST['lab_name'];
        if (getLabSpecialistsCount($labName) > 0) {
            $error = "Cannot decommission '{$labName}' when active specialists are rostered.";
        } else {
            $_SESSION['client_labs'] = array_filter($_SESSION['client_labs'], function($l) use ($labName) {
                return $l['name'] !== $labName;
            });
            // Re-index array
            $_SESSION['client_labs'] = array_values($_SESSION['client_labs']);
            $success = "Client decommissioned successfully.";
        }
    }
}

// B. Route Selection
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'login';
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
        case 'queue':
            include 'views/test_queue.php';
            break;
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
            include 'views/test_queue.php';
    }

    echo '</main></div></body></html>';
}
