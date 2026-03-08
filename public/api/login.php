<?php
/**
 * Login API Endpoint
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

if(empty($username) || empty($password)){
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Username and password are required"]);
    exit();
}

// Normalize to lowercase so ADM and adm match the same account
$usernameNormalized = mb_strtolower($username, 'UTF-8');

if(!$db_available || !$conn){
    http_response_code(503);
    echo json_encode(["success" => false, "message" => "Database unavailable. Please try again later."]);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE LOWER(username)=?");
    $stmt->execute([$usernameNormalized]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user && password_verify($password, $user['password'])){
        $_SESSION['user_id'] = $user['id'];
        $displayName = mb_strtolower($user['username'], 'UTF-8');
        $_SESSION['username'] = $displayName;
        echo json_encode(["success" => true, "message" => "Login successful", "username" => $displayName]);
    } else {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Invalid username or password"]);
    }
} catch(PDOException $e){
    error_log("Login error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error. Please try again."]);
}
?>
