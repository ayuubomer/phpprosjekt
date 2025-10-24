<?php
/* Lag en innloggingsside for en nettside ved hjelp av PHP og HTML. Siden skal inneholde et skjema der brukeren kan skrive inn brukernavn og passord. Når skjemaet sendes inn, skal PHP-koden validere at begge feltene er fylt ut. Hvis ett eller begge feltene er tomme, skal en feilmelding vises. Hvis begge feltene er fylt ut, skal en velkomstmelding vises med brukernavnet. */
$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = 'Begge feltene må fylles ut.';
    } else {
        echo "Velkommen, " . htmlspecialchars($username) . "!";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Innloggingsside</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .error { color: red; }
        .login-form { max-width: 300px; margin: auto; padding: 1em; border: 1px solid #ccc; border-radius: 1em; }
        .login-form div { margin-bottom: 1em; }
        .login-form label { margin-bottom: .5em; color: #333333; }
        .login-form input { border: 1px solid #ccc; padding: .5em; width: 100%; }
        .login-form button { padding: 0.7em; color: #fff; background-color: #007BFF; border: none; border-radius: .3em; cursor: pointer; }
        .login-form button:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <div class="login-form">
        <h2>Logg inn</h2>
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div>
                <label for="username">Brukernavn:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Passord:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <button type="submit">Logg inn</button>
            </div>
        </form>
    </div>
</body>
</html>