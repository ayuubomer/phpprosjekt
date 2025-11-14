<?php
require __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$apiKey = $_ENV['GEMINI_API_KEY'];
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . urlencode($apiKey);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
echo "<h3>Available Models:</h3><ul>";
foreach ($data['models'] ?? [] as $model) {
  if (in_array('generateContent', $model['supportedGenerationMethods'] ?? [])) {
    echo "<li><strong>{$model['name']}</strong></li>";
  }
}
echo "</ul>";