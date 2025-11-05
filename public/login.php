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
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Log in</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    /* Page / background */
    :root{
      --bg-1: #0f172a;
      --bg-2: #0b1220;
      --card-bg: rgba(255,255,255,0.03);
      --glass: rgba(255,255,255,0.04);
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
      font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background: linear-gradient(180deg, var(--bg-1), var(--bg-2));
      color: #e6eef6;
      padding:24px;
    }

    /* container */
    .wrap {
      width: 100%;
      max-width: 720px;  /* increased from 640px */
      margin: auto;
    }

    header.brand{
      display:flex;
      align-items:center;
      gap:10px;
      margin-bottom:10px;
    }
    .logo{
      width:70px;height:70px;border-radius:10px;
      background:linear-gradient(135deg,var(--accent),var(--accent-2));
      box-shadow: 0 6px 18px rgba(91,111,255,0.18), inset 0 -6px 12px rgba(255,255,255,0.06);
      display:flex;align-items:center;justify-content:center;font-weight:700;color:#03203b;
      font-family:monospace;
    }
    header h1{font-size:1.15rem;margin:0 0 0.1rem 0;}
    header p{margin:0;color:var(--muted);font-size:0.9rem}

    /* Card */
    .card{
      background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
      border:5px solid rgba(255,255,255,0.03);
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
      gap:48px;  /* increased from 12px */
      margin-bottom:8px;
    }
    .field label{
      width:120px;  /* decreased from 180px to account for larger gap */
      text-align:left;  /* changed from right to left align */
      font-weight: 600;
      font-size: 0.95rem;
      color: #eaf0ff;
      flex-shrink: 0;  /* prevent label from shrinking */
    }
    .control{
      position:relative;
      flex: 1;  /* takes remaining space */
      min-width: 0;        /* allow the input to shrink inside the flex container */
      box-sizing: border-box;
    }
    /* Input fields */
    input[type="email"], input[type="password"] {
      width: 100%;
      box-sizing: border-box; /* include padding/border in width calculations */
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

    /* show password button */
    .toggle-pw{
      position:absolute;
      right:12px;  /* increased from 8px */
      top:50%;
      transform:translateY(-50%);
      background:transparent;border:0;color:var(--muted);
      padding:8px; /* increased from 6px */border-radius:8px;cursor:pointer;font-size:0.9rem;
    }

    /* Primary button */
    .btn{
      display:inline-flex;align-items:center;justify-content:center;gap:8px;
      background: linear-gradient(90deg,var(--accent),var(--accent-2));
      color:#03203b;border:0;padding:12px;border-radius:10px;font-weight:700;cursor:pointer;
      box-shadow: 0 8px 24px rgba(91,111,255,0.18);
    }
    .btn:active{transform:translateY(1px)}
    .btn.secondary{
      background:transparent;color:var(--accent);border:1px solid rgba(107,140,255,0.14);
      box-shadow:none;font-weight:600;
    }

    .helper{ text-align:center;margin-top:12px;color:var(--muted);font-size:0.95rem }
    .helper a{ color:inherit;text-decoration:underline; color: #cfe7ff; }
  </style>
</head>
<body>
  <div class="wrap">
    <header class="brand">
      <div class="logo">
        <img src="img/gymbot.png" alt="Logo" style="width:100%;height:100%;object-fit:cover;border-radius:5px;">
</div>
      <div>
        <h1>GymBOT</h1>
        <p>Gym Guide</p>
      </div>
    </header>

    <?php if ($already): ?>
      <div class="card" style="margin-bottom:12px;">
        <div class="meta">
          <div>You are logged in as <strong><?= htmlspecialchars($already['email']) ?></strong>.</div>
          <form method="post" action="logout.php" style="margin:0;">
            <button type="submit" class="btn secondary">Log out</button>
          </form>
        </div>
      </div>
    <?php endif; ?>

    <div class="card">
      <?php if($err):?><div class="error"><?=htmlspecialchars($err)?></div><?php endif;?>

      <form method="post" autocomplete="off" novalidate>
        <div class="field">
          <label for="email">Email</label>
          <div class="control">
            <input id="email" type="email" name="email" required placeholder="you@example.com" />
          </div>
        </div>

        <div class="field">
          <label for="pw">Password</label>
          <div class="control">
            <input id="pw" type="password" name="password" required placeholder="••••••••" />
            <button type="button" class="toggle-pw" aria-label="Toggle password" onclick="togglePw('pw', this)">Show</button>
          </div>
        </div>

        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:6px;">
          <button type="submit" class="btn">Log in</button>
        </div>

        <p class="helper">No account? <a href="signup.php">Sign up</a></p>
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
    // optional: focus first field
    document.getElementById('email')?.focus();
  </script>
</body>
</html>
