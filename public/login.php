<?php
require __DIR__ . '/../src/auth.php';
require __DIR__ . '/../src/database.php';

auth_start();
$already = current_user();
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if (!filter_var($email, FILTER_VALIDATE_EMAIL))         $err = 'Please enter a valid email.';
  else {
    $stmt = db()->prepare('SELECT id, email, password_hash FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) $err = 'Invalid credentials.';
    else { login_user_id((int)$user['id'], $user['email']); header('Location: /PHPPROSJEKT/public/index.php'); exit; }
  }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Log in</title>
<style>
:root{color-scheme:light dark}body{font-family:system-ui,sans-serif;margin:2rem auto;max-width:520px;padding:0 12px}
h1{margin:0 0 16px}.card{border:1px solid #d0d0d0;border-radius:12px;padding:20px}form{display:grid;gap:14px}
.field{display:grid;grid-template-columns:140px 1fr;align-items:center;gap:10px}label{font-weight:600}
input[type="email"],input[type="password"]{width:93%;padding:10px;border:1px solid #c9c9c9;border-radius:10px;font:inherit}
button[type="submit"]{padding:12px;border:0;border-radius:10px;font:600 1rem/1 system-ui,sans-serif;cursor:pointer}
.error{color:#b00020;margin-bottom:10px}a{text-decoration:none}
</style></head><body>
<h1>Log in</h1>

<?php if ($already): ?>
  <div class="card" style="margin-bottom:12px;">
    You are logged in as <strong><?= htmlspecialchars($already['email']) ?></strong>.
    <div style="margin-top:8px;"><a href="logout.php">Log out</a> to switch account.</div>
  </div>
<?php endif; ?>

<div class="card">
  <?php if($err):?><div class="error"><?=htmlspecialchars($err)?></div><?php endif;?>
  <form method="post">
    <div class="field"><label for="email">Email</label><input id="email" type="email" name="email" required></div>
    <div class="field"><label for="pw">Password</label><input id="pw" type="password" name="password" required></div>
    <button type="submit">Log in</button>
  </form>
</div>
<p class="helper" style="text-align:center;margin-top:12px;">No account? <a href="signup.php">Sign up</a></p>
</body></html>
