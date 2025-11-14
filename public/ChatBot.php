<?php

// public/ChatBot.php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/auth.php';
require __DIR__ . '/../src/database.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

require_login();

header('Content-Type: application/json; charset=utf-8');

$user = current_user();
$uid = (int)$user['id'];

// --- Handle "clear" action -----------------------------------------------
if (($_GET['action'] ?? '') === 'clear') {
  try {
    error_log("Clear action triggered for user ID: $uid");
    
    // First, check how many messages exist
    $checkStmt = db()->prepare('SELECT COUNT(*) as count FROM messages WHERE user_id=?');
    $checkStmt->execute([$uid]);
    $count = $checkStmt->fetch()['count'];
    error_log("Found $count messages for user $uid");
    
    // Now delete them
    $stmt = db()->prepare('DELETE FROM messages WHERE user_id=?');
    $stmt->execute([$uid]);
    $deleted = $stmt->rowCount();
    error_log("Deleted $deleted messages");
    
    echo json_encode(['ok' => true, 'deleted' => $deleted, 'found' => $count]);
  } catch (Exception $e) {
    error_log("Clear error: " . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
  }
  exit;
}

// --- Handle chat message -------------------------------------------------
$raw = file_get_contents('php://input') ?: '';
$in = json_decode($raw, true);
$userMessage = trim($in['message'] ?? '');

if ($userMessage === '') {
  echo json_encode(['error' => 'Empty message']);
  exit;
}

// --- Load previous messages for context (chat history) ------------------
$stmt = db()->prepare('SELECT role, text FROM messages WHERE user_id=? ORDER BY ts ASC, id ASC LIMIT 20');
$stmt->execute([$uid]);
$rows = $stmt->fetchAll();

$contents = [];
foreach ($rows as $r) {
  $role = $r['role'] === 'user' ? 'user' : 'model'; // Gemini uses 'model' for assistant
  $contents[] = ['role' => $role, 'parts' => [['text' => $r['text']]]];
}

// Add current user message
$contents[] = ['role' => 'user', 'parts' => [['text' => $userMessage]]];

// --- Gemini API call -----------------------------------------------------
$apiKey = $_ENV['GEMINI_API_KEY'] ?? null;
if (!$apiKey) {
  echo json_encode(['error' => 'API key missing']);
  exit;
}

$model = 'gemini-2.0-flash'; // Use this stable model
$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($apiKey);

$payload = [
  'contents' => $contents,
  'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 512],
];

$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
  CURLOPT_POSTFIELDS => json_encode($payload),
  CURLOPT_TIMEOUT => 30,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

// Error handling
if ($curlErr) {
  echo json_encode(['error' => "cURL error: $curlErr"]);
  exit;
}

if ($httpCode !== 200) {
  $errorData = json_decode($response, true);
  $errorMsg = $errorData['error']['message'] ?? 'Unknown API error';
  echo json_encode(['error' => "API returned HTTP $httpCode: $errorMsg"]);
  exit;
}

$data = json_decode($response, true);
$text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

if (!$text) {
  echo json_encode(['error' => 'No text in response', 'debug' => $data]);
  exit;
}

// --- Save both user + assistant messages to database --------------------
$now = time();
$stmt = db()->prepare('INSERT INTO messages (user_id, role, text, ts) VALUES (?, ?, ?, ?)');
$stmt->execute([$uid, 'user', $userMessage, $now]);
$stmt->execute([$uid, 'assistant', $text, $now]);

echo json_encode(['reply' => $text]);
