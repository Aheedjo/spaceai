<?php
/**
 * Registration API Endpoint
 */
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

$data = json_decode(file_get_contents("php://input"), true);
$username = trim($data['username'] ?? '');
$password = trim($data['password'] ?? '');

if(!$username || !$password){
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Enter username and password"]);
    exit();
}

if(strlen($username) < 3 || strlen($username) > 50){
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
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = $username;
    echo json_encode(["success" => true, "message" => "Registered successfully (demo mode - no database)", "username" => $username]);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
    $stmt->execute([$username]);
    if($stmt->fetch()){
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Username already exists"]);
        exit();
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $hash]);

    $_SESSION['user_id'] = $conn->lastInsertId();
    $_SESSION['username'] = $username;
    echo json_encode(["success" => true, "message" => "Registered successfully", "username" => $username]);
} catch(PDOException $e){
    error_log("Registration error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error. Please try again."]);
}
?>
