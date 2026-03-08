<?php
/**
 * SpaceAI API Endpoint
 * Handles chat requests using Google Gemini API
 */
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

ob_clean();

if(!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(["success" => false, "answer" => "⚠️ You must log in first"]);
    exit();
}

$user_id = $_SESSION['user_id'];

$raw_input = file_get_contents("php://input");
$input = json_decode($raw_input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["success" => false, "answer" => "⚠️ Invalid request format"]);
    exit();
}

$question = trim($input['question'] ?? "");
$conversation_id = isset($input['conversation_id']) ? (int) $input['conversation_id'] : null;

if(empty($question)){
    http_response_code(400);
    echo json_encode(["success" => false, "answer" => "⚠️ Please provide a question"]);
    exit();
}

$banned = ["sex","porn","nude","kill","drugs","bomb","terrorism"];
foreach($banned as $word){
    if(stripos($question, $word) !== false){
        http_response_code(400);
        echo json_encode(["success" => false, "answer" => "⚠️ SpaceAI cannot answer inappropriate questions"]);
        exit();
    }
}

$api_key = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
$model = defined('GEMINI_MODEL') ? GEMINI_MODEL : 'models/gemini-2.5-flash';

if($api_key === 'YOUR_GEMINI_API_KEY_HERE' || empty($api_key)){
    http_response_code(500);
    echo json_encode(["success" => false, "answer" => "⚠️ Gemini API key not configured. Please set your API key in config.php"]);
    exit();
}

$answer = "";
$error = null;

try {
    $answer = callGeminiAPI($api_key, $question, $model);
} catch(Exception $e){
    $error = $e->getMessage();
    error_log("Gemini API Error: " . $error);
}

if($error || empty($answer)){
    http_response_code(500);
    echo json_encode(["success" => false, "answer" => "⚠️ AI service error: " . ($error ?? "No response received")]);
    exit();
}

$conversation_id_to_return = $conversation_id;
$conversation_title_updated = null;
if($db_available && $conn){
    try {
        if ($conversation_id <= 0) {
            $stmt = $conn->prepare("INSERT INTO conversations (user_id, title) VALUES (?, 'New chat')");
            $stmt->execute([$user_id]);
            $conversation_id_to_return = (int) $conn->lastInsertId();
            $conversation_id = $conversation_id_to_return;
        }
        if ($conversation_id > 0) {
            $stmt = $conn->prepare("INSERT INTO messages (user_id, conversation_id, role, message) VALUES (?,?,?,?)");
            $stmt->execute([$user_id, $conversation_id, "user", $question]);
            $stmt->execute([$user_id, $conversation_id, "ai", $answer]);
            $stmt = $conn->prepare("SELECT title FROM conversations WHERE id = ? AND user_id = ?");
            $stmt->execute([$conversation_id, $user_id]);
            $conv = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($conv && $conv['title'] === 'New chat') {
                $title = mb_substr(trim($question), 0, 50, 'UTF-8');
                if ($title === '') $title = 'New chat';
                $stmt = $conn->prepare("UPDATE conversations SET title = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$title, $conversation_id, $user_id]);
                $conversation_title_updated = $title;
            }
        }
    } catch(PDOException $e){
        error_log("Database error saving message: " . $e->getMessage());
    }
} else {
    error_log("Chat message not saved - database unavailable");
}

$out = ["success" => true, "answer" => $answer, "conversation_id" => $conversation_id_to_return];
if ($conversation_title_updated !== null) {
    $out["conversation_title"] = $conversation_title_updated;
}
echo json_encode($out);

function callGeminiAPI($api_key, $question, $model = 'models/gemini-2.5-flash'){
    $system_instruction = defined('GEMINI_SYSTEM_INSTRUCTION') && GEMINI_SYSTEM_INSTRUCTION !== ''
        ? GEMINI_SYSTEM_INSTRUCTION
        : null;

    $data = [
        "contents" => [[
            "parts" => [["text" => $question]]
        ]],
        "generationConfig" => [
            "temperature" => 0.7,
            "maxOutputTokens" => 2048
        ]
    ];
    if ($system_instruction !== null) {
        $data["systemInstruction"] = [
            "parts" => [["text" => $system_instruction]]
        ];
    }

    $model_name = str_replace('models/', '', $model);
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model_name}:generateContent?key=" . urlencode($api_key);
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);

    if($curl_error){
        throw new Exception("CURL Error: " . $curl_error);
    }

    if($http_code !== 200){
        $error_data = json_decode($response, true);
        $error_msg = $error_data['error']['message'] ?? "API returned status code: {$http_code}";
        error_log("Gemini API Error - URL: {$url}, Status: {$http_code}, Error: " . json_encode($error_data));
        
        if($http_code === 404){
            $fallback_models = [
                'models/gemini-2.5-flash',
                'models/gemini-flash-latest',
                'models/gemini-pro-latest',
                'models/gemini-2.0-flash'
            ];
            foreach($fallback_models as $fallback_model){
                if($model !== $fallback_model){
                    error_log("Model {$model} not found, trying {$fallback_model} as fallback");
                    try {
                        return callGeminiAPI($api_key, $question, $fallback_model);
                    } catch(Exception $e){
                        continue;
                    }
                }
            }
        }
        throw new Exception($error_msg);
    }

    $ai = json_decode($response, true);
    if(isset($ai['candidates'][0]['content']['parts'][0]['text'])){
        return $ai['candidates'][0]['content']['parts'][0]['text'];
    } elseif(isset($ai['promptFeedback']['blockReason'])){
        throw new Exception("Content was blocked: " . $ai['promptFeedback']['blockReason']);
    } else {
        error_log("Gemini API Response: " . json_encode($ai));
        throw new Exception("Invalid API response format");
    }
}
?>
