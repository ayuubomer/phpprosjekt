<?php
require __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

// Laster inn .env-variabler
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Henter API-nøkkel fra miljøvariabler
$apiKey = $_ENV['GEMINI_API_KEY'] ?? null;

// Stopper hvis nøkkel mangler
if (!$apiKey) {
    die("Missing API key");
}

// API-endepunkt for å hente tilgjengelige modeller
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . urlencode($apiKey);

// Kjør request med cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// Tolker JSON-respons
$data = json_decode($response, true);

// Enkel HTML-utskrift
echo "<h3>Available Models:</h3><ul>";

// Viser kun modeller som støtter generateContent
foreach ($data['models'] ?? [] as $model) {
    if (in_array('generateContent', $model['supportedGenerationMethods'] ?? [])) {
        echo "<li><strong>{$model['name']}</strong></li>";
    }
}

echo "</ul>";
