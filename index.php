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
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Intercept clicks on links
        document.body.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link && link.href) {
                const url = new URL(link.href);
                // Check if it's an internal dashboard link and NOT logout or login
                if (url.origin === window.location.origin && url.pathname.endsWith('index.php') && url.searchParams.has('page')) {
                    const page = url.searchParams.get('page');
                    if (page !== 'logout' && page !== 'login') {
                        e.preventDefault();
                        navigateTo(link.href);
                    }
                }
            }
        });

        // Intercept form submissions
        document.body.addEventListener('submit', (e) => {
            if (e.defaultPrevented) return;
            const form = e.target;
            if (form.action) {
                const url = new URL(form.action);
                if (url.origin === window.location.origin && url.pathname.endsWith('index.php')) {
                    const page = url.searchParams.get('page');
                    if (page !== 'logout' && page !== 'login') {
                        e.preventDefault();
                        submitForm(form);
                    }
                }
            }
        });

        // Handle browser back/forward buttons
        window.addEventListener('popstate', () => {
            loadPageContent(window.location.href, false);
        });

        function navigateTo(url) {
            loadPageContent(url, true);
        }

        async function loadPageContent(url, pushState = true) {
            try {
                const response = await fetch(url);
                if (!response.ok) throw new Error('Network response error');
                const html = await response.text();
                
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Swap the main element content
                const newMain = doc.querySelector('main');
                const currentMain = document.querySelector('main');
                if (newMain && currentMain) {
                    currentMain.innerHTML = newMain.innerHTML;
                    
                    // Execute script tags in new main content
                    executeScripts(currentMain);
                }
                
                // Swap page title
                document.title = doc.title;
                
                // Update URL in browser history
                if (pushState) {
                    history.pushState(null, '', url);
                }
                
                // Update active state in sidebar links
                updateSidebar(url);
                
                // Re-initialize Lucide icons
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            } catch (error) {
                console.error('Error loading page:', error);
                if (pushState) {
                    window.location.href = url;
                }
            }
        }

        async function submitForm(form) {
            try {
                const url = form.action || window.location.href;
                const formData = new FormData(form);
                
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) throw new Error('Form submission error');
                
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Swap main content
                const newMain = doc.querySelector('main');
                const currentMain = document.querySelector('main');
                
                if (newMain && currentMain) {
                    currentMain.innerHTML = newMain.innerHTML;
                    executeScripts(currentMain);
                }
                
                document.title = doc.title;
                
                // If the server redirected us during the fetch, update the browser URL
                if (response.url && response.url !== window.location.href) {
                    history.pushState(null, '', response.url);
                    updateSidebar(response.url);
                }
                
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            } catch (error) {
                console.error('Form submission failed:', error);
                form.submit();
            }
        }

        function executeScripts(container) {
            const scripts = container.querySelectorAll('script');
            scripts.forEach(oldScript => {
                const newScript = document.createElement('script');
                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                oldScript.parentNode.replaceChild(newScript, oldScript);
            });
        }

        function updateSidebar(currentUrl) {
            const urlParams = new URL(currentUrl, window.location.origin).searchParams;
            const page = urlParams.get('page') || 'pending';
            
            const sidebarLinks = document.querySelectorAll('nav a');
            sidebarLinks.forEach(link => {
                const linkUrl = new URL(link.href, window.location.origin);
                const linkPage = linkUrl.searchParams.get('page');
                
                if (linkPage === page) {
                    link.classList.remove('text-gray-600', 'hover:bg-gray-50');
                    link.classList.add('bg-blue-50', 'text-[#12426F]');
                } else {
                    link.classList.remove('bg-blue-50', 'text-[#12426F]');
                    link.classList.add('text-gray-600', 'hover:bg-gray-50');
                }
            });
        }
    });
    </script>
    <?php
    echo '</body></html>';
}
