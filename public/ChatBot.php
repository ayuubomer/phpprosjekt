<?php
// public/ChatBot.php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

header('Content-Type: application/json; charset=utf-8');

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$apiKey = $_ENV['GEMINI_API_KEY'] ?? null; // unified name
if (!$apiKey) {
  http_response_code(500);
  echo json_encode(['error' => 'API key missing']);
  exit;
}

// Optional health check without breaking JSON:
if (isset($_GET['health'])) {
  echo json_encode(['status' => 'ok', 'hasKey' => true]);
  exit;
}

// Read input (JSON/POST/GET)
$raw = file_get_contents('php://input') ?: '';
$in  = json_decode($raw, true);
$userMessage = trim($in['message'] ?? ($_POST['message'] ?? ($_GET['message'] ?? '')));

if ($userMessage === '') {
  echo json_encode(['error' => 'Provide "message" via JSON/POST/GET']);
  exit;
}

$model = 'gemini-2.0-flash';
$payload = [
  'systemInstruction' => [
    'parts' => [[ 'text' => 'You are a concise, helpful assistant. Keep answers short unless asked.' ]]
  ],
  'contents' => [[ 'parts' => [[ 'text' => $userMessage ]]]],
  'generationConfig' => [
    'temperature' => 0.7,
    'maxOutputTokens' => 512
  ],
];

$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_POST => true,
  CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
  CURLOPT_POSTFIELDS => json_encode($payload),
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT => 30,
]);
$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
  echo json_encode(['error' => "cURL error: $err"]);
  exit;
}

$data = json_decode($response, true);
$text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

if (!$text) {
  echo json_encode([
    'error' => 'No text returned (maybe blocked by safety filters).',
    'debug' => $data
  ]);
  exit;
}

echo json_encode(['reply' => $text]);
