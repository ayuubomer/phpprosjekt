<?php
require __DIR__ . '/../src/auth.php';
require __DIR__ . '/../src/database.php';

auth_start();

// Redirect if already logged in
if (current_user()) {
    header('Location: /PHPPROSJEKT/public/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $password2) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare('INSERT INTO users (email, password_hash) VALUES (?, ?)');
            $stmt->execute([$email, $hash]);

            $id = (int)db()->lastInsertId();
            login_user_id($id, $email);

            header('Location: /PHPPROSJEKT/public/index.php');
            exit;

        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $error = 'That email is already registered.';
            } else {
                // Generic safe error message
                $error = 'An unexpected error occurred. Please try again later.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Sign Up</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    :root{
      --bg-1: #0f172a;
      --bg-2: #0b1220;
      --accent: #6b8cff;
      --accent-2: #5ce6c8;
      --muted: #9aa4b2;
      --danger: #f44336;
    }
    html,body{height:100%}
    body{
      margin:0;
      min-height:100%;
      display:flex;
      align-items:center;
      justify-content:center;
      font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background: linear-gradient(180deg, var(--bg-1), var(--bg-2));
      color: #e6eef6;
      padding:24px;
    }

    .wrap {
      width: 100%;
      max-width: 720px;
      margin: auto;
    }

    header.brand{
      display:flex;
      align-items:flex-start;
      gap:10px;
      margin-bottom:10px;
    }
    .logo{
      width:70px;height:70px;border-radius:10px;
      overflow:hidden;
      display:flex;align-items:center;justify-content:center;
      box-shadow: 0 6px 18px rgba(91,111,255,0.18), inset 0 -6px 12px rgba(255,255,255,0.06);
    }
    .logo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      border-radius: 10px;
    }
    header h1{font-size:1.15rem;margin:0 0 0.1rem 0;}
    header p{margin:0;color:var(--muted);font-size:0.9rem}

    .card{
      background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
      border:1px solid rgba(255,255,255,0.03);
      backdrop-filter: blur(6px);
      border-radius:14px;
      padding:22px;
      box-shadow: 0 10px 30px rgba(2,6,23,0.5);
    }

    .meta{
      display:flex;
      justify-content:space-between;
      align-items:center;
      margin-bottom:14px;
      color:var(--muted);
      font-size:0.95rem;
    }

    .error{
      color:var(--danger);
      background: rgba(244,67,54,0.06);
      padding:8px 10px;border-radius:8px;margin-bottom:12px;border:1px solid rgba(244,67,54,0.08);
    }

    form{display:grid;gap:12px}

    .field{
      display:flex;
      flex-direction:row;
      align-items:center;
      gap:48px;
      margin-bottom:8px;
    }
    .field label{
      width:120px;
      text-align:left;
      font-weight: 600;
      font-size: 0.95rem;
      color: #eaf0ff;
      flex-shrink: 0;
    }
    .control{
      position:relative;
      flex: 1;
      min-width: 0;
      box-sizing: border-box;
    }
    input[type="email"], input[type="password"] {
      width: 100%;
      box-sizing: border-box;
      padding: 14px;
      border-radius: 12px;
      border: 1px solid rgba(255,255,255,0.06);
      background: linear-gradient(180deg, rgba(255,255,255,0.015), transparent);
      color: inherit;
      font-size: 1.1rem;
      transition: box-shadow .18s, border-color .12s, transform .08s;
      outline: none;
    }
    input::placeholder{color:rgba(230,238,246,0.45)}
    input:focus{
      box-shadow: 0 6px 18px rgba(107,140,255,0.12);
      border-color: rgba(107,140,255,0.9);
      transform: translateY(-1px);
    }

    .toggle-pw{
      position:absolute;
      right:12px;
      top:50%;
      transform:translateY(-50%);
      background:transparent;border:0;color:var(--muted);
      padding:8px;border-radius:8px;cursor:pointer;font-size:0.9rem;
    }

    .btn{
      display:inline-flex;align-items:center;justify-content:center;gap:8px;
      background: linear-gradient(90deg,var(--accent),var(--accent-2));
      color:#03203b;border:0;padding:12px;border-radius:10px;font-weight:700;cursor:pointer;
      box-shadow: 0 8px 24px rgba(91,111,255,0.18);
    }
    .btn:active{transform:translateY(1px)}
    .helper{ text-align:center;margin-top:12px;color:var(--muted);font-size:0.95rem }
    .helper a{ color: #cfe7ff; text-decoration:underline; }
  </style>
</head>
<body>
  <div class="wrap">
    <header class="brand">
      <div class="logo">
        <img src="/PHPPROSJEKT/public/img/gymbot.png" alt="GymBOT">
      </div>
      <div class="brand-text">
        <h1 style="margin:0 0 0.1rem 0;">Create account</h1>
        <p>GymBOT - Gym Guide</p>
      </div>
    </header>

    <div class="card">
      <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" autocomplete="off" novalidate>
        <div class="field">
          <label for="email">Email</label>
          <div class="control">
            <input id="email" type="email" name="email" required placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>
        </div>

        <div class="field">
          <label for="pw">Password</label>
          <div class="control">
            <input id="pw" type="password" name="password" required placeholder="••••••••">
            <button type="button" class="toggle-pw" aria-label="Toggle password" onclick="togglePw('pw', this)">Show</button>
          </div>
        </div>

        <div class="field">
          <label for="pw2">Confirm</label>
          <div class="control">
            <input id="pw2" type="password" name="password2" required placeholder="••••••••">
            <button type="button" class="toggle-pw" aria-label="Toggle password confirm" onclick="togglePw('pw2', this)">Show</button>
          </div>
        </div>

        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:6px;">
          <button type="submit" class="btn">Sign up</button>
        </div>

        <p class="helper">Already have an account? <a href="login.php">Log in</a></p>
      </form>
    </div>
  </div>

  <script>
    function togglePw(id, btn){
      const el = document.getElementById(id);
      if(!el) return;
      if(el.type === 'password'){ el.type = 'text'; btn.textContent = 'Hide'; }
      else { el.type = 'password'; btn.textContent = 'Show'; }
    }
    document.getElementById('email')?.focus();
  </script>
</body>
</html>
