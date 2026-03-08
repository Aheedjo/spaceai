<?php
/**
 * Registration API Endpoint
 */
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: no-store');

session_start();

$data = json_decode(file_get_contents("php://input"), true);
$username = trim($data['username'] ?? '');
$password = trim($data['password'] ?? '');

if(!$username || !$password){
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Enter username and password"]);
    exit();
}

// Normalize username to lowercase for uniqueness (ADM and adm are the same)
$usernameNormalized = mb_strtolower($username, 'UTF-8');

if(mb_strlen($usernameNormalized) < 3 || mb_strlen($usernameNormalized) > 50){
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Username must be between 3 and 50 characters"]);
    exit();
}

if(strlen($password) < 6){
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Password must be at least 6 characters"]);
    exit();
}

if(!$db_available || !$conn){
    http_response_code(503);
    echo json_encode(["success" => false, "message" => "Database unavailable. Please try again later."]);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(username)=?");
    $stmt->execute([$usernameNormalized]);
    if($stmt->fetch()){
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Username already taken"]);
        exit();
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$usernameNormalized, $hash]);

    $_SESSION['user_id'] = $conn->lastInsertId();
    $_SESSION['username'] = $usernameNormalized;
    echo json_encode(["success" => true, "message" => "Registered successfully", "username" => $usernameNormalized]);
} catch(PDOException $e){
    error_log("Registration error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error. Please try again."]);
}
?>
