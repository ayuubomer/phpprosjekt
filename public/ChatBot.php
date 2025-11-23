<?php
// Backend-endepunkt for chatbot: mottar meldinger, henter historikk, kaller Gemini API


require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/auth.php';
require __DIR__ . '/../src/database.php';

use Dotenv\Dotenv;

/**
 * Laster miljøvariabler fra .env (API key, DB-detaljer osv.)
 */
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

/**
 * Sørger for at bruker må være innlogget for å bruke chatbotten.
 * Hvis ikke → redirect til login.php
 */
require_login();

/**
 * Svarformatet skal være JSON.
 */
header('Content-Type: application/json; charset=utf-8');

$user = current_user();
$uid  = (int)$user['id'];   // Henter bruker-ID fra session


// 1) CLEAR CHAT-HISTORY (?action=clear)

if (($_GET['action'] ?? '') === 'clear') {

  try {
    error_log("Clear chat requested by user: $uid");

    // Finn hvor mange meldinger som finnes
    $checkStmt = db()->prepare('SELECT COUNT(*) AS count FROM messages WHERE user_id=?');
    $checkStmt->execute([$uid]);
    $count = $checkStmt->fetch()['count'];

    // Slett chat-historikken
    $stmt = db()->prepare('DELETE FROM messages WHERE user_id=?');
    $stmt->execute([$uid]);

    $deleted = $stmt->rowCount();

    echo json_encode([
      'ok'     => true,
      'found'  => $count,
      'deleted'=> $deleted
    ]);

  } catch (Exception $e) {
    echo json_encode([
      'ok'    => false,
      'error' => $e->getMessage()
    ]);
  }

  exit;
}



// 2) HANDLE MESSAGE FROM USER

// Leser JSON-body fra fetch()
$raw = file_get_contents('php://input') ?: '';
$in  = json_decode($raw, true);

// Leser meldingen brukeren sendte
$userMessage = trim($in['message'] ?? '');

if ($userMessage === '') {
  echo json_encode(['error' => 'Empty message']);
  exit;
}



// 3) LOAD CHAT HISTORY (max 20 messages for context)


$stmt = db()->prepare(
  'SELECT role, text
   FROM messages
   WHERE user_id=?
   ORDER BY ts ASC, id ASC
   LIMIT 20'
);

$stmt->execute([$uid]);
$rows = $stmt->fetchAll();

/**
 * Gemini forventer format:
 * [
 *   ['role'=>'user',  'parts'=>[['text'=>'Hi']]],
 *   ['role'=>'model', 'parts'=>[['text'=>'Hello']]],
 * ]
 */
$contents = [];

foreach ($rows as $r) {
  // I vår DB heter rollen "assistant", men Gemini forventer "model"
  $gRole = $r['role'] === 'user' ? 'user' : 'model';

  $contents[] = [
    'role'  => $gRole,
    'parts' => [['text' => $r['text']]]
  ];
}

// Legg til dagens melding fra bruker
$contents[] = [
  'role'  => 'user',
  'parts' => [['text' => $userMessage]]
];


// 4) GEMINI API CALL


$apiKey = $_ENV['GEMINI_API_KEY'] ?? null;

if (!$apiKey) {
  echo json_encode(['error' => 'API key missing']);
  exit;
}

$model = 'gemini-2.0-flash';
$url   = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($apiKey);

// Payload til Gemini API
$payload = [
  'contents' => $contents,
  'generationConfig' => [
    'temperature' => 0.7,
    'maxOutputTokens' => 512,
  ],
];

// === Kjør API-kall via cURL ===
$ch = curl_init($url);

curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
  CURLOPT_POSTFIELDS     => json_encode($payload),
  CURLOPT_TIMEOUT        => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);

curl_close($ch);



// 5) ERROR HANDLING


if ($curlErr) {
  echo json_encode(['error' => "cURL error: $curlErr"]);
  exit;
}

if ($httpCode !== 200) {
  $errData = json_decode($response, true);
  $msg = $errData['error']['message'] ?? 'Unknown API error';

  echo json_encode([
    'error' => "API returned HTTP $httpCode: $msg"
  ]);
  exit;
}


// 6) EXTRACT MODEL RESPONSE + SAVE TO DB

$data = json_decode($response, true);

$text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

if (!$text) {
  echo json_encode(['error' => 'No text in response', 'debug' => $data]);
  exit;
}

$now = time();

// Lagre brukerens melding
$stmt = db()->prepare(
  'INSERT INTO messages (user_id, role, text, ts)
   VALUES (?, ?, ?, ?)'
);
$stmt->execute([$uid, 'user', $userMessage, $now]);

// Lagre AI-svar
$stmt->execute([$uid, 'assistant', $text, $now]);



// 7) SEND SVARET TILBAKE TIL FRONTEND
echo json_encode(['reply' => $text]);
