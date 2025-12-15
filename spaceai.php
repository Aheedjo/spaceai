<?php
/**
 * SpaceAI API Endpoint
 * Handles chat requests using Google Gemini API
 */

// Suppress any output before JSON
ob_start();

// Set headers for JSON response FIRST (before any output)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Now require files (after headers are set)
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

// Clear any accidental output
ob_clean();

// Check authentication
if(!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(["success" => false, "answer" => "⚠️ You must log in first"]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get input
$raw_input = file_get_contents("php://input");
$input = json_decode($raw_input, true);

// Handle JSON decode errors
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["success" => false, "answer" => "⚠️ Invalid request format"]);
    exit();
}

$question = trim($input['question'] ?? "");

if(empty($question)){
    http_response_code(400);
    echo json_encode(["success" => false, "answer" => "⚠️ Please provide a question"]);
    exit();
}

// Content filtering
$banned = ["sex","porn","nude","kill","drugs","bomb","terrorism"];
foreach($banned as $word){
    if(stripos($question, $word) !== false){
        http_response_code(400);
        echo json_encode(["success" => false, "answer" => "⚠️ SpaceAI cannot answer inappropriate questions"]);
        exit();
    }
}

// Get Gemini API key
$api_key = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
$model = defined('GEMINI_MODEL') ? GEMINI_MODEL : 'gemini-1.5-flash';

if($api_key === 'YOUR_GEMINI_API_KEY_HERE' || empty($api_key)){
    http_response_code(500);
    echo json_encode(["success" => false, "answer" => "⚠️ Gemini API key not configured. Please set your API key in config.php"]);
    exit();
}

// Call Gemini API
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

// Save chat to database (if available)
if($db_available && $conn){
    try {
        $stmt = $conn->prepare("INSERT INTO messages (user_id, role, message) VALUES (?,?,?)");
        $stmt->execute([$user_id, "user", $question]);
        $stmt->execute([$user_id, "ai", $answer]);
    } catch(PDOException $e){
        error_log("Database error saving message: " . $e->getMessage());
        // Continue even if save fails
    }
} else {
    // Database not available - chat works but history won't be saved
    error_log("Chat message not saved - database unavailable");
}

echo json_encode(["success" => true, "answer" => $answer]);

/**
 * Call Google Gemini API
 * @param string $api_key Gemini API key
 * @param string $question User's question
 * @param string $model Gemini model to use
 * @return string AI response
 */
function callGeminiAPI($api_key, $question, $model = 'models/gemini-2.5-flash'){
    $data = [
        "contents" => [[
            "parts" => [["text" => $question]]
        ]],
        "generationConfig" => [
            "temperature" => 0.7,
            "maxOutputTokens" => 2048
        ]
    ];

    // Try different API versions and model formats
    // Format: https://generativelanguage.googleapis.com/v1beta/models/MODEL_NAME:generateContent
    // Model name may include "models/" prefix - strip it if present
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
    // curl_close() is deprecated in PHP 8.5+ - handle is automatically closed

    if($curl_error){
        throw new Exception("CURL Error: " . $curl_error);
    }

    if($http_code !== 200){
        $error_data = json_decode($response, true);
        $error_msg = $error_data['error']['message'] ?? "API returned status code: {$http_code}";
        
        // Log the actual URL and error for debugging
        error_log("Gemini API Error - URL: {$url}, Status: {$http_code}, Error: " . json_encode($error_data));
        
        // Try different model variants if not found
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
                        // Continue to next fallback
                        continue;
                    }
                }
            }
        }
        
        throw new Exception($error_msg);
    }

    $ai = json_decode($response, true);
    
    // Handle response format
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
