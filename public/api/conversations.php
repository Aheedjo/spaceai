<?php
/**
 * List or create conversations (chats)
 * GET = list user's conversations
 * POST = create new conversation
 */
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: no-store');

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false]);
    exit();
}

$user_id = (int) $_SESSION['user_id'];

if (!$db_available || !$conn) {
    http_response_code(503);
    echo json_encode(["success" => false, "conversations" => []]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->prepare(
            "SELECT id, title, created_at FROM conversations WHERE user_id = ? ORDER BY created_at DESC LIMIT 100"
        );
        $stmt->execute([$user_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "conversations" => $rows]);
    } catch (PDOException $e) {
        error_log("conversations list: " . $e->getMessage());
        echo json_encode(["success" => false, "conversations" => []]);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM conversations WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $count = (int) $stmt->fetchColumn();
        if ($count >= 5) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Maximum 5 chats. Delete one to create a new chat."]);
            exit();
        }
        $stmt = $conn->prepare("INSERT INTO conversations (user_id, title) VALUES (?, 'New chat')");
        $stmt->execute([$user_id]);
        $id = (int) $conn->lastInsertId();
        $stmt = $conn->prepare("SELECT id, title, created_at FROM conversations WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "conversation" => $row]);
    } catch (PDOException $e) {
        error_log("conversations create: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(["success" => false]);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $id = isset($input['id']) ? (int) $input['id'] : (isset($_GET['id']) ? (int) $_GET['id'] : 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Missing conversation id"]);
        exit();
    }
    try {
        $stmt = $conn->prepare("SELECT id FROM conversations WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Conversation not found"]);
            exit();
        }
        $stmt = $conn->prepare("DELETE FROM messages WHERE conversation_id = ?");
        $stmt->execute([$id]);
        $stmt = $conn->prepare("DELETE FROM conversations WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        echo json_encode(["success" => true]);
    } catch (PDOException $e) {
        error_log("conversations delete: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(["success" => false]);
    }
    exit();
}

http_response_code(405);
echo json_encode(["success" => false]);
