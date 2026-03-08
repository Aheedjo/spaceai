<?php
/**
 * Auth check – returns current user if session cookie is valid
 */
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: no-store');

session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(["logged_in" => false]);
    exit();
}

echo json_encode([
    "logged_in" => true,
    "username" => $_SESSION['username']
]);
?>
