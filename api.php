<?php
session_start();
header('Content-Type: application/json');

// Helper to send error
function sendError($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['error' => $msg]);
    exit;
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    sendError('Unauthorized', 401);
}

if (isset($_SESSION['guest_mode']) && $_SESSION['guest_mode'] === true) {
    sendError('Forbidden', 403);
}

try {
    // Include modules
    require_once __DIR__ . '/includes/db.php';
    require_once __DIR__ . '/includes/api_handlers.php';

    // Initialize Database & Run Migrations
    $db = initDatabase(__DIR__ . '/database.db');

    $method = $_SERVER['REQUEST_METHOD'];
    $action = isset($_GET['action']) ? $_GET['action'] : null;

    // Route Request
    if ($action === 'users') {
        handleUsers($db, $method);
    } else {
        handleHexes($db, $method);
    }

} catch (Exception $e) {
    sendError('System error: ' . $e->getMessage(), 500);
} catch (PDOException $e) {
    sendError('Database error: ' . $e->getMessage(), 500);
}
?>
