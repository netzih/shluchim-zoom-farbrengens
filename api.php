<?php
/**
 * Shluchim Zoom Farbrengens API
 * RESTful API for managing farbrengen events, authentication, and site settings
 */

require_once 'config.php';

// Enable CORS for local testing (remove or restrict in production)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

// Route the request
try {
    switch ($action) {
        // Authentication
        case 'login':
            if ($method !== 'POST') jsonResponse(false, null, 'Method not allowed', 405);
            handleLogin();
            break;
        case 'logout':
            handleLogout();
            break;
        case 'check_auth':
            handleCheckAuth();
            break;
        case 'change_password':
            if ($method !== 'POST') jsonResponse(false, null, 'Method not allowed', 405);
            handleChangePassword();
            break;

        // Events
        case 'get_events':
            handleGetEvents();
            break;
        case 'get_admin_events':
            handleGetAdminEvents();
            break;
        case 'get_event':
            handleGetEvent();
            break;
        case 'create_event':
            if ($method !== 'POST') jsonResponse(false, null, 'Method not allowed', 405);
            handleCreateEvent();
            break;
        case 'update_event':
            if ($method !== 'POST') jsonResponse(false, null, 'Method not allowed', 405);
            handleUpdateEvent();
            break;
        case 'delete_event':
            if ($method !== 'POST') jsonResponse(false, null, 'Method not allowed', 405);
            handleDeleteEvent();
            break;
        case 'submit_public_event':
            if ($method !== 'POST') jsonResponse(false, null, 'Method not allowed', 405);
            handleSubmitPublicEvent();
            break;
        case 'verify_submission_password':
            if ($method !== 'POST') jsonResponse(false, null, 'Method not allowed', 405);
            handleVerifySubmissionPassword();
            break;

        // Settings
        case 'get_settings':
            handleGetSettings();
            break;
        case 'update_settings':
            if ($method !== 'POST') jsonResponse(false, null, 'Method not allowed', 405);
            handleUpdateSettings();
            break;
        case 'upload_banner':
            if ($method !== 'POST') jsonResponse(false, null, 'Method not allowed', 405);
            handleUploadBanner();
            break;
        case 'remove_banner':
            if ($method !== 'POST') jsonResponse(false, null, 'Method not allowed', 405);
            handleRemoveBanner();
            break;
        case 'upload_social_image':
            if ($method !== 'POST') jsonResponse(false, null, 'Method not allowed', 405);
            handleUploadSocialImage();
            break;
        case 'remove_social_image':
            if ($method !== 'POST') jsonResponse(false, null, 'Method not allowed', 405);
            handleRemoveSocialImage();
            break;

        // Users
        case 'get_users':
            handleGetUsers();
            break;
        case 'create_user':
            if ($method !== 'POST') jsonResponse(false, null, 'Method not allowed', 405);
            handleCreateUser();
            break;
        case 'delete_user':
            if ($method !== 'POST') jsonResponse(false, null, 'Method not allowed', 405);
            handleDeleteUser();
            break;

        default:
            jsonResponse(false, null, 'Invalid action', 400);
    }
} catch (Exception $e) {
    jsonResponse(false, null, DEBUG_MODE ? $e->getMessage() : 'An error occurred', 500);
}

// === AUTHENTICATION ===

function handleLogin() {
    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        jsonResponse(false, null, 'Username and password required', 400);
    }

    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT id, username, password_hash, full_name, role, organization FROM admin_users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            startSecureSession();
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_name'] = $user['full_name'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_organization'] = $user['organization'];

            $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);

            jsonResponse(true, [
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'role' => $user['role'],
                'organization' => $user['organization']
            ], 'Login successful');
        } else {
            jsonResponse(false, null, 'Invalid credentials', 401);
        }
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Authentication error', 500);
    }
}

function handleLogout() {
    startSecureSession();
    session_destroy();
    jsonResponse(true, null, 'Logged out successfully');
}

function handleCheckAuth() {
    if (isLoggedIn()) {
        jsonResponse(true, [
            'username' => $_SESSION['admin_username'],
            'full_name' => $_SESSION['admin_name'],
            'role' => $_SESSION['admin_role'],
            'organization' => $_SESSION['admin_organization']
        ], 'Authenticated');
    } else {
        jsonResponse(false, null, 'Not authenticated', 401);
    }
}

function handleChangePassword() {
    if (!isLoggedIn()) jsonResponse(false, null, 'Unauthorized', 401);
    if (!isset($_POST['new_password']) || strlen($_POST['new_password']) < 6) {
        jsonResponse(false, null, 'Password must be at least 6 characters', 400);
    }

    try {
        $pdo = getDbConnection();
        $hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?")->execute([$hash, $_SESSION['admin_id']]);
        jsonResponse(true, null, 'Password changed successfully');
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Failed to change password', 500);
    }
}

// === EVENTS ===

function handleGetEvents() {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->query("SELECT * FROM events WHERE is_active = 1 ORDER BY event_date ASC, created_at DESC");
        $events = $stmt->fetchAll();

        $transformed = array_map(function($e) {
            return [
                'id' => $e['id'],
                'title' => htmlspecialchars_decode($e['title'], ENT_QUOTES),
                'description' => htmlspecialchars_decode($e['description'], ENT_QUOTES),
                'farbrenger' => htmlspecialchars_decode($e['farbrenger'], ENT_QUOTES),
                'occasion' => htmlspecialchars_decode($e['occasion'], ENT_QUOTES),
                'date' => $e['event_date'],
                'timezone' => $e['event_timezone'] ?? 'America/New_York',
                'zoomLink' => $e['zoom_link']
            ];
        }, $events);

        jsonResponse(true, $transformed, 'Events retrieved successfully');
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Failed to retrieve events', 500);
    }
}

function handleGetAdminEvents() {
    if (!isLoggedIn()) jsonResponse(false, null, 'Unauthorized', 401);

    try {
        $pdo = getDbConnection();
        if ($_SESSION['admin_role'] === 'super_admin') {
            $stmt = $pdo->query("SELECT * FROM events WHERE is_active = 1 ORDER BY event_date ASC, created_at DESC");
        } else {
            $stmt = $pdo->prepare("SELECT * FROM events WHERE is_active = 1 AND created_by = ? ORDER BY event_date ASC");
            $stmt->execute([$_SESSION['admin_id']]);
        }
        jsonResponse(true, $stmt->fetchAll(), 'Events retrieved successfully');
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Failed to retrieve events', 500);
    }
}

function handleGetEvent() {
    $id = $_GET['id'] ?? $_GET['event_id'] ?? null;
    if (!$id) jsonResponse(false, null, 'Event ID required', 400);

    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        $event = $stmt->fetch();
        $event ? jsonResponse(true, $event) : jsonResponse(false, null, 'Event not found', 404);
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Failed to retrieve event', 500);
    }
}

function handleCreateEvent() {
    if (!isLoggedIn()) jsonResponse(false, null, 'Unauthorized', 401);
    if (empty(trim($_POST['title'] ?? ''))) jsonResponse(false, null, 'Title is required', 400);

    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("INSERT INTO events (title, description, farbrenger, occasion, event_date, event_timezone, zoom_link, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            sanitizeInput($_POST['title']),
            sanitizeInput($_POST['description'] ?? null),
            sanitizeInput($_POST['farbrenger'] ?? null),
            sanitizeInput($_POST['occasion'] ?? null),
            sanitizeInput($_POST['event_date'] ?? null),
            sanitizeInput($_POST['event_timezone'] ?? 'America/New_York'),
            sanitizeInput($_POST['zoom_link'] ?? null),
            $_SESSION['admin_id']
        ]);
        jsonResponse(true, ['id' => $pdo->lastInsertId()], 'Event created successfully', 201);
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Failed to create event', 500);
    }
}

function handleUpdateEvent() {
    if (!isLoggedIn()) jsonResponse(false, null, 'Unauthorized', 401);
    if (!isset($_POST['event_id'])) jsonResponse(false, null, 'Event ID required', 400);
    if (empty(trim($_POST['title'] ?? ''))) jsonResponse(false, null, 'Title is required', 400);

    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("UPDATE events SET title=?, description=?, farbrenger=?, occasion=?, event_date=?, event_timezone=?, zoom_link=? WHERE id=?");
        $stmt->execute([
            sanitizeInput($_POST['title']),
            sanitizeInput($_POST['description'] ?? null),
            sanitizeInput($_POST['farbrenger'] ?? null),
            sanitizeInput($_POST['occasion'] ?? null),
            sanitizeInput($_POST['event_date'] ?? null),
            sanitizeInput($_POST['event_timezone'] ?? 'America/New_York'),
            sanitizeInput($_POST['zoom_link'] ?? null),
            $_POST['event_id']
        ]);
        jsonResponse(true, null, 'Event updated successfully');
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Failed to update event', 500);
    }
}

function handleDeleteEvent() {
    if (!isLoggedIn()) jsonResponse(false, null, 'Unauthorized', 401);
    if (!isset($_POST['event_id'])) jsonResponse(false, null, 'Event ID required', 400);

    try {
        $pdo = getDbConnection();
        $pdo->prepare("UPDATE events SET is_active = 0 WHERE id = ?")->execute([$_POST['event_id']]);
        jsonResponse(true, null, 'Event deleted successfully');
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Failed to delete event', 500);
    }
}

function handleSubmitPublicEvent() {
    if (empty(trim($_POST['title'] ?? ''))) jsonResponse(false, null, 'Title is required', 400);

    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("INSERT INTO events (title, description, farbrenger, occasion, event_date, event_timezone, zoom_link, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([
            sanitizeInput($_POST['title']),
            sanitizeInput($_POST['description'] ?? null),
            sanitizeInput($_POST['farbrenger'] ?? null),
            sanitizeInput($_POST['occasion'] ?? null),
            sanitizeInput($_POST['event_date'] ?? null),
            sanitizeInput($_POST['event_timezone'] ?? 'America/New_York'),
            sanitizeInput($_POST['zoom_link'] ?? null)
        ]);
        jsonResponse(true, ['id' => $pdo->lastInsertId()], 'Event submitted successfully!', 201);
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Failed to submit event', 500);
    }
}

function handleVerifySubmissionPassword() {
    if (empty(trim($_POST['password'] ?? ''))) jsonResponse(false, null, 'Password is required', 400);

    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'submission_password'");
        $stmt->execute();
        $stored = $stmt->fetchColumn();

        if (!$stored) jsonResponse(false, null, 'Submission form is not configured', 403);
        $_POST['password'] === $stored ? jsonResponse(true, null, 'Password verified') : jsonResponse(false, null, 'Incorrect password', 401);
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Failed to verify password', 500);
    }
}

// === SETTINGS ===

function handleGetSettings() {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
        $settings = [];
        foreach ($stmt->fetchAll() as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        jsonResponse(true, $settings, 'Settings retrieved successfully');
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Failed to retrieve settings', 500);
    }
}

function handleUpdateSettings() {
    if (!isLoggedIn()) jsonResponse(false, null, 'Unauthorized', 401);

    try {
        $pdo = getDbConnection();
        $settings = $_POST;
        unset($settings['action']);

        foreach ($settings as $key => $value) {
            $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, updated_by) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = ?, updated_by = ?")
                ->execute([$key, $value, $_SESSION['admin_id'], $value, $_SESSION['admin_id']]);
        }
        jsonResponse(true, null, 'Settings updated successfully');
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Failed to update settings', 500);
    }
}

function isSuperAdmin() {
    return isLoggedIn() && $_SESSION['admin_role'] === 'super_admin';
}

function handleUploadBanner() {
    if (!isSuperAdmin()) jsonResponse(false, null, 'Unauthorized', 403);
    if (!isset($_FILES['banner_file']) || $_FILES['banner_file']['error'] !== UPLOAD_ERR_OK) {
        jsonResponse(false, null, 'No file uploaded', 400);
    }

    $file = $_FILES['banner_file'];
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed)) jsonResponse(false, null, 'Invalid file type', 400);
    if ($file['size'] > 5 * 1024 * 1024) jsonResponse(false, null, 'File too large', 400);

    try {
        $pdo = getDbConnection();
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'banner_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $path = 'uploads/banners/' . $filename;
        if (!is_dir('uploads/banners')) mkdir('uploads/banners', 0755, true);
        move_uploaded_file($file['tmp_name'], $path);

        $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('header_banner', ?) ON DUPLICATE KEY UPDATE setting_value = ?")
            ->execute([$path, $path]);
        jsonResponse(true, ['banner_url' => $path], 'Banner uploaded successfully');
    } catch (Exception $e) {
        jsonResponse(false, null, 'Failed to upload banner', 500);
    }
}

function handleRemoveBanner() {
    if (!isSuperAdmin()) jsonResponse(false, null, 'Unauthorized', 403);
    try {
        $pdo = getDbConnection();
        $pdo->prepare("DELETE FROM site_settings WHERE setting_key = 'header_banner'")->execute();
        jsonResponse(true, null, 'Banner removed');
    } catch (Exception $e) {
        jsonResponse(false, null, 'Failed to remove banner', 500);
    }
}

function handleUploadSocialImage() {
    if (!isSuperAdmin()) jsonResponse(false, null, 'Unauthorized', 403);
    if (!isset($_FILES['social_image_file']) || $_FILES['social_image_file']['error'] !== UPLOAD_ERR_OK) {
        jsonResponse(false, null, 'No file uploaded', 400);
    }

    $file = $_FILES['social_image_file'];
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed)) jsonResponse(false, null, 'Invalid file type', 400);
    if ($file['size'] > 5 * 1024 * 1024) jsonResponse(false, null, 'File too large', 400);

    try {
        $pdo = getDbConnection();
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'social_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $path = 'uploads/social/' . $filename;
        if (!is_dir('uploads/social')) mkdir('uploads/social', 0755, true);
        move_uploaded_file($file['tmp_name'], $path);

        $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('social_image', ?) ON DUPLICATE KEY UPDATE setting_value = ?")
            ->execute([$path, $path]);
        jsonResponse(true, ['social_image_url' => $path], 'Social image uploaded');
    } catch (Exception $e) {
        jsonResponse(false, null, 'Failed to upload social image', 500);
    }
}

function handleRemoveSocialImage() {
    if (!isSuperAdmin()) jsonResponse(false, null, 'Unauthorized', 403);
    try {
        $pdo = getDbConnection();
        $pdo->prepare("DELETE FROM site_settings WHERE setting_key = 'social_image'")->execute();
        jsonResponse(true, null, 'Social image removed');
    } catch (Exception $e) {
        jsonResponse(false, null, 'Failed to remove social image', 500);
    }
}

// === USERS ===

function handleGetUsers() {
    if (!isSuperAdmin()) jsonResponse(false, null, 'Unauthorized', 403);
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->query("SELECT id, username, email, full_name, organization, role, is_active, created_at, last_login FROM admin_users WHERE is_active = 1 ORDER BY created_at DESC");
        jsonResponse(true, $stmt->fetchAll(), 'Users retrieved');
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Failed to retrieve users', 500);
    }
}

function handleCreateUser() {
    if (!isSuperAdmin()) jsonResponse(false, null, 'Unauthorized', 403);
    if (!isset($_POST['username'], $_POST['email'], $_POST['password'])) {
        jsonResponse(false, null, 'Username, email, and password required', 400);
    }
    if (strlen($_POST['password']) < 6) jsonResponse(false, null, 'Password must be at least 6 characters', 400);

    try {
        $pdo = getDbConnection();
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = ($_POST['role'] ?? 'admin') === 'super_admin' ? 'super_admin' : 'admin';
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, email, password_hash, full_name, organization, role, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            sanitizeInput($_POST['username']),
            sanitizeInput($_POST['email']),
            $hash,
            sanitizeInput($_POST['full_name'] ?? ''),
            sanitizeInput($_POST['organization'] ?? ''),
            $role,
            $_SESSION['admin_id']
        ]);
        jsonResponse(true, ['id' => $pdo->lastInsertId()], 'User created', 201);
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Failed to create user', 500);
    }
}

function handleDeleteUser() {
    if (!isSuperAdmin()) jsonResponse(false, null, 'Unauthorized', 403);
    if (!isset($_POST['user_id'])) jsonResponse(false, null, 'User ID required', 400);
    if ($_POST['user_id'] == $_SESSION['admin_id']) jsonResponse(false, null, 'Cannot delete yourself', 400);

    try {
        $pdo = getDbConnection();
        $pdo->prepare("DELETE FROM admin_users WHERE id = ?")->execute([$_POST['user_id']]);
        jsonResponse(true, null, 'User deleted');
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Failed to delete user', 500);
    }
}
?>
