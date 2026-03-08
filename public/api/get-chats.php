<?php
/**
 * Get messages for a conversation (or legacy: all user messages if no conversation_id)
 */
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "messages" => []]);
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$conversation_id = isset($_GET['conversation_id']) ? (int) $_GET['conversation_id'] : null;
$limit = min(500, max(10, (int)($_GET['limit'] ?? 500)));

if (!$db_available || !$conn) {
    echo json_encode(["success" => true, "messages" => []]);
    exit();
}

try {
    if ($conversation_id > 0) {
        $stmt = $conn->prepare(
            "SELECT role, message, timestamp FROM messages WHERE user_id = ? AND conversation_id = ? ORDER BY id ASC LIMIT " . $limit
        );
        $stmt->execute([$user_id, $conversation_id]);
    } else {
        $stmt = $conn->prepare(
            "SELECT role, message, timestamp FROM messages WHERE user_id = ? AND (conversation_id IS NULL OR conversation_id = 0) ORDER BY id ASC LIMIT " . $limit
        );
        $stmt->execute([$user_id]);
    }
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "messages" => $rows]);
} catch (PDOException $e) {
    error_log("get-chats error: " . $e->getMessage());
    echo json_encode(["success" => false, "messages" => []]);
}
?>
