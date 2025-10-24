<?php
/*Denne siden fungerer som en enkel frontend til en chatbot. Den skal inneholde et skjema der brukeren kan skrive inn et spørsmål eller en melding til chatboten. Når skjemaet sendes inn, skal PHP-koden behandle meldingen og vise et forhåndsdefinert svar fra chatboten.*/
$response = '';
$userMessage = '';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Use null coalescing to avoid "Undefined array key" warnings
    $userMessage = trim($_POST['message'] ?? '');

    if (!empty($userMessage)) {
        // Forhåndsdefinert svar fra chatboten
        $response = "Chatbot svarer: Takk for meldingen din!";
    } else {
        $response = "Vennligst skriv inn en melding.";
    }
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .chat-form { max-width: 500px; margin: auto; padding: 1em; border: 1px solid #ccc; border-radius: 1em; }
        .chat-form div { margin-bottom: 1em; }
        .chat-form label { margin-bottom: .5em; color: #333333; }
        .chat-form textarea { border: 1px solid #ccc; padding: .5em; width: 100%; height: 100px; }
        .chat-form button { padding: 0.7em; color: #fff; background-color: #28a745; border: none; border-radius: .3em; cursor: pointer; }
        .chat-form button:hover { background-color: #218838; }
        .response { margin-top: 1em; padding: 1em; border: 1px solid #ccc; border-radius: .5em; background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="chat-form">
        <h2>Chat med vår Chatbot</h2>
        <form method="post" action="">
            <div>
                <label for="message">Skriv din melding:</label>
                <textarea id="message" name="message" required></textarea>
            </div>
            <div>
                <button type="submit">Send</button>
            </div>
        </form>
        <?php if (!empty($response)): ?>
            <div class="response"><?php echo htmlspecialchars($response); ?></div>
        <?php endif; ?>
    </div>
    <div style="text-align: center; margin-top: 20px;">
        <a href="index.php">Tilbake til innlogging</a>
    </div>
    
</body>
</html>


