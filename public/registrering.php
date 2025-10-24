<?php 
/* Hvis brukeren ikke har en konto, kan de registrere seg her. Siden skal inneholde et skjema der brukeren kan skrive inn ønsket brukernavn og passord. 
Når skjemaet sendes inn, skal PHP-koden validere at begge feltene er fylt ut. Hvis ett eller begge feltene er tomme, 
skal en feilmelding vises. Hvis begge feltene er fylt ut, skal en suksessmelding vises med brukernavnet. */
$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = 'Begge feltene må fylles ut.';
    } else {
        echo "Registrering vellykket! Velkommen, " . htmlspecialchars($username) . "!";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registreringsside</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .error { color: red; }
        .register-form { max-width: 300px; margin: auto; padding: 1em; border: 1px solid #ccc; border-radius: 1em; }
        .register-form div { margin-bottom: 1em; }
        .register-form label { margin-bottom: .5em; color: #333333; }
        .register-form input { border: 1px solid #ccc; padding: .5em; width: 100%; }
        .register-form button { padding: 0.7em; color: #fff; background-color: #0056b3; border: none; border-radius: .3em; cursor: pointer; }
        .register-form button:hover { background-color: #022b56ff; }
    </style>
</head>
<body>
    <div class="register-form">
        <h2>Registrer deg</h2>
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div>
                <label for="username">Ønsket brukernavn:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Ønsket passord:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <button type="submit">Registrer</button>
            </div>
            <div>
                <p>Allerede har en konto? <a href="index.php">Logg inn her</a></p>
        </form>
    </div>
</body>
</html>
