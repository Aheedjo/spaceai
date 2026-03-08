<?php
/**
 * List available Gemini models (run from CLI: php public/api/list-models.php)
 */
require_once __DIR__ . '/../../config.php';

$api_key = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';

if($api_key === 'YOUR_GEMINI_API_KEY_HERE' || empty($api_key)){
    die("Please set your GEMINI_API_KEY in config.php\n");
}

echo "Fetching available models...\n\n";

$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . urlencode($api_key);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json"
    ],
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);

if($curl_error){
    die("CURL Error: " . $curl_error . "\n");
}

if($http_code !== 200){
    die("API returned status code: {$http_code}\nResponse: {$response}\n");
}

$data = json_decode($response, true);

if(isset($data['models'])){
    echo "Available models:\n";
    echo str_repeat("=", 60) . "\n";
    foreach($data['models'] as $model){
        $name = $model['name'] ?? 'Unknown';
        $display = $model['displayName'] ?? $name;
        $supported = isset($model['supportedGenerationMethods']) ? implode(', ', $model['supportedGenerationMethods']) : 'N/A';
        echo "Name: {$name}\n";
        echo "Display: {$display}\n";
        echo "Supported Methods: {$supported}\n";
        echo str_repeat("-", 60) . "\n";
    }
} else {
    echo "Response:\n";
    print_r($data);
}
?>
