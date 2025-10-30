<?php
// public/signup.php (placeholder; session-only)
require __DIR__ . '/../src/auth.php';

auth_start();

// If already logged in, go to chat
if (current_user()) {
  header('Location: /PHPPROSJEKT/public/index.php');
  exit;
}

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $password2 = $_POST['password2'] ?? '';

  // Placeholder validation (no DB)
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $err = 'Please enter a valid email.';
  } elseif (strlen($password) < 8) {
    $err = 'Password must be at least 8 characters.';
  } elseif ($password !== $password2) {
    $err = 'Passwords do not match.';
  } else {
    login_user($email);
    header('Location: /PHPPROSJEKT/public/index.php');
    exit;
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Sign up (placeholder)</title>
  <style>
    :root { color-scheme: light dark; }
    body { font-family: system-ui, sans-serif; margin: 2rem auto; max-width: 520px; padding: 0 12px; }
    h1 { margin: 0 0 16px; }
    .card { border: 1px solid #d0d0d0; border-radius: 12px; padding: 20px; }
    form { display: grid; gap: 14px; }
    .field { display: grid; grid-template-columns: 140px 1fr; align-items: center; gap: 10px; }
    label { font-weight: 600; }
    input[type="email"], input[type="password"] {
      width: 93%; padding: 10px; border: 1px solid #c9c9c9; border-radius: 10px; font: inherit;
    }
    button[type="submit"] { padding: 12px; border: 0; border-radius: 10px; font: 600 1rem/1 system-ui, sans-serif; cursor: pointer; }
    .error { color: #b00020; margin-bottom: 10px; }
     a { text-decoration: none; }


  </style>
</head>
<body>
  <h1>Create account</h1>
  <div class="card">
    <?php if ($err): ?><div class="error"><?= htmlspecialchars($err) ?></div><?php endif; ?>
    <form method="post" action="">
      <div class="field">
        <label for="email">Email</label>
        <input id="email" type="email" name="email" required>
      </div>

      <div class="field">
        <label for="pw">Password</label>
        <input id="pw" type="password" name="password" required minlength="8">
      </div>

      <div class="field">
        <label for="pw2">Confirm password</label>
        <input id="pw2" type="password" name="password2" required minlength="8">
      </div>

      <button type="submit">Sign up</button>
    </form>
  </div>

  <p>Already have an account? <a href="login.php">Log in</a></p>
</body>
</html>
