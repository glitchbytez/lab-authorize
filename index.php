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
                    header("Location: index.php?page=pending");
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

    // 4. Create new user account
    if (isset($_POST['action']) && $_POST['action'] === 'create_user') {
        $name = trim($_POST['name']);
        $role = trim($_POST['role']);
        $lab = trim($_POST['lab']);
        $password = trim($_POST['password']);

        if (empty($name) || empty($role) || empty($password)) {
            $error = 'All fields (Name, Role, Password) are required.';
        } else {
            // Check uniqueness of name
            $exists = false;
            foreach ($_SESSION['scientists'] as $sc) {
                if (strtolower($sc['name']) === strtolower($name)) {
                    $exists = true;
                    break;
                }
            }
            if ($exists) {
                $error = "A specialist named '{$name}' is already rostered.";
            } else {
                // Generate a unique ID
                $maxIdNum = 0;
                foreach ($_SESSION['scientists'] as $sc) {
                    if (preg_match('/(?:scientist|admin|user)-(\d+)/', $sc['id'], $matches)) {
                        $num = (int)$matches[1];
                        if ($num > $maxIdNum) {
                            $maxIdNum = $num;
                        }
                    }
                }
                $newId = 'user-' . sprintf('%02d', $maxIdNum + 1);
                
                $_SESSION['scientists'][] = [
                    'id' => $newId,
                    'name' => $name,
                    'role' => $role,
                    'lab' => ($lab === '' || $lab === 'None') ? null : $lab,
                    'password' => $password
                ];
                $success = "User account successfully registered and active.";
            }
        }
    }

    // 5. Delete user account
    if (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
        $userId = $_POST['user_id'];
        if ($_SESSION['user']['id'] === $userId) {
            $error = "Security Policy Violation: You cannot delete your own active profile.";
        } else {
            $_SESSION['scientists'] = array_filter($_SESSION['scientists'], function($sc) use ($userId) {
                return $sc['id'] !== $userId;
            });
            $_SESSION['scientists'] = array_values($_SESSION['scientists']);
            $success = "User account decommissioned successfully.";
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
