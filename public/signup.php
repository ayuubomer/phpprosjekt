<?php
// filepath: c:\xampp\htdocs\phpprosjekt\public\signup.php

// Inkluderer nødvendige filer for autentisering og database
require __DIR__ . '/../src/auth.php';
require __DIR__ . '/../src/database.php';

// Starter sesjon for brukerautentisering
auth_start();

// Videresender til hovedsiden hvis brukeren allerede er logget inn
if (current_user()) {
    header('Location: /PHPPROSJEKT/public/index.php');
    exit;
}

// Variabel for feilmeldinger
$error = '';

// Håndterer registrering når skjemaet sendes inn
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Henter og renser input fra skjemaet
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? ''; // Bekreft passord

    // Validerer e-postadresse
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } 
    // Sjekker at passord er minst 8 tegn langt
    elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } 
    // Sjekker at begge passord-felt matcher
    elseif ($password !== $password2) {
        $error = 'Passwords do not match.';
    } 
    // Alle valideringer er bestått - opprett bruker
    else {
        try {
            // Hash passordet for sikker lagring
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Setter inn ny bruker i databasen
            $stmt = db()->prepare('INSERT INTO users (email, password_hash) VALUES (?, ?)');
            $stmt->execute([$email, $hash]);

            // Henter ID for den nyopprettede brukeren
            $id = (int)db()->lastInsertId();
            
            // Logger inn brukeren automatisk
            login_user_id($id, $email);

            // Videresender til hovedsiden
            header('Location: /PHPPROSJEKT/public/index.php');
            exit;

        } catch (PDOException $e) {
            // Håndterer spesifikk feil hvis e-posten allerede er registrert
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $error = 'That email is already registered.';
            } else {
                // Generisk feilmelding for andre database-feil
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
    /* CSS-variabler for fargetema */
    :root{
      --bg-1: #0f172a;      /* Mørk bakgrunnsfarge 1 */
      --bg-2: #0b1220;      /* Mørk bakgrunnsfarge 2 */
      --accent: #6b8cff;    /* Primær blå farge */
      --accent-2: #5ce6c8;  /* Sekundær turkis farge */
      --muted: #9aa4b2;     /* Dempet grå tekstfarge */
      --danger: #f44336;    /* Rød feilmeldingsfarge */
    }

    /* Basis layout - fullskjerm høyde */
    html,body{height:100%}
    body{
      margin:0;
      min-height:100%;
      display:flex;
      align-items:center;
      justify-content:center;
      font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background: linear-gradient(180deg, var(--bg-1), var(--bg-2)); /* Gradient bakgrunn */
      color: #e6eef6;
      padding:24px;
    }

    /* Hovedcontainer - responsiv bredde */
    .wrap {
      width: 100%;
      max-width: 720px;
      margin: auto;
    }

    /* Header med logo og tittel */
    header.brand{
      display:flex;
      align-items:flex-start;
      gap:10px;
      margin-bottom:10px;
    }
    
    /* Logo-container */
    .logo{
      width:70px;
      height:70px;
      border-radius:10px;
      overflow:hidden;
      display:flex;
      align-items:center;
      justify-content:center;
      box-shadow: 0 6px 18px rgba(91,111,255,0.18), inset 0 -6px 12px rgba(255,255,255,0.06);
    }
    
    /* Logo-bilde styling */
    .logo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      border-radius: 10px;
    }
    
    /* Header tekst-styling */
    header h1{font-size:1.15rem;margin:0 0 0.1rem 0;}
    header p{margin:0;color:var(--muted);font-size:0.9rem}

    /* Hovedkort med glassmorfisme-effekt */
    .card{
      background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
      border:1px solid rgba(255,255,255,0.03);
      backdrop-filter: blur(6px); /* Glassmorfisme-effekt */
      border-radius:14px;
      padding:22px;
      box-shadow: 0 10px 30px rgba(2,6,23,0.5);
    }

    /* Metadata-seksjon */
    .meta{
      display:flex;
      justify-content:space-between;
      align-items:center;
      margin-bottom:14px;
      color:var(--muted);
      font-size:0.95rem;
    }

    /* Feilmelding-styling */
    .error{
      color:var(--danger);
      background: rgba(244,67,54,0.06);
      padding:8px 10px;
      border-radius:8px;
      margin-bottom:12px;
      border:1px solid rgba(244,67,54,0.08);
    }

    /* Skjema-layout med grid */
    form{display:grid;gap:12px}

    /* Input-felt layout - label og input side ved side */
    .field{
      display:flex;
      flex-direction:row;
      align-items:center;
      gap:48px; /* Stor gap mellom label og input */
      margin-bottom:8px;
    }
    
    /* Label-styling - venstrejustert */
    .field label{
      width:120px;
      text-align:left;
      font-weight: 600;
      font-size: 0.95rem;
      color: #eaf0ff;
      flex-shrink: 0; /* Forhindrer at label krymper */
    }
    
    /* Container for input-felt med relativ posisjonering (for "Show"-knapp) */
    .control{
      position:relative;
      flex: 1; /* Tar opp resterende plass */
      min-width: 0; /* Tillater krymping i flexbox */
      box-sizing: border-box;
    }
    
    /* Input-felt styling */
    input[type="email"], input[type="password"] {
      width: 100%;
      box-sizing: border-box; /* Inkluderer padding/border i bredde */
      padding: 14px;
      border-radius: 12px;
      border: 1px solid rgba(255,255,255,0.06);
      background: linear-gradient(180deg, rgba(255,255,255,0.015), transparent);
      color: inherit;
      font-size: 1.1rem;
      transition: box-shadow .18s, border-color .12s, transform .08s;
      outline: none;
    }
    
    /* Placeholder-tekst styling */
    input::placeholder{color:rgba(230,238,246,0.45)}
    
    /* Focus-effekt på input-felt */
    input:focus{
      box-shadow: 0 6px 18px rgba(107,140,255,0.12);
      border-color: rgba(107,140,255,0.9);
      transform: translateY(-1px); /* Heving-effekt */
    }

    /* "Show/Hide" passord-knapp */
    .toggle-pw{
      position:absolute;
      right:12px;
      top:50%;
      transform:translateY(-50%);
      background:transparent;
      border:0;
      color:var(--muted);
      padding:8px;
      border-radius:8px;
      cursor:pointer;
      font-size:0.9rem;
    }

    /* Primær knapp-stil (Sign up) */
    .btn{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:8px;
      background: linear-gradient(90deg,var(--accent),var(--accent-2)); /* Gradient fra blå til turkis */
      color:#03203b;
      border:0;
      padding:12px;
      border-radius:10px;
      font-weight:700;
      cursor:pointer;
      box-shadow: 0 8px 24px rgba(91,111,255,0.18);
    }
    
    /* Klikk-effekt på knapp */
    .btn:active{transform:translateY(1px)}
    
    /* Hjelpetekst nederst (Log in-lenke) */
    .helper{ 
      text-align:center;
      margin-top:12px;
      color:var(--muted);
      font-size:0.95rem 
    }
    .helper a{ 
      color:#cfe7ff; 
      text-decoration:underline; 
    }
  </style>
</head>
<body>
  <div class="wrap">
    <!-- Header med logo og tittel -->
    <header class="brand">
      <div class="logo">
        <img src="/PHPPROSJEKT/public/img/gymbot.png" alt="GymBOT">
      </div>
      <div class="brand-text">
        <h1 style="margin:0 0 0.1rem 0;">Create account</h1>
        <p>GymBOT - Gym Guide</p>
      </div>
    </header>

    <!-- Registreringsskjema -->
    <div class="card">
      <!-- Viser feilmelding hvis registrering feiler -->
      <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" autocomplete="off" novalidate>
        <!-- E-post input-felt -->
        <div class="field">
          <label for="email">Email</label>
          <div class="control">
            <!-- Beholder e-post hvis validering feiler -->
            <input id="email" type="email" name="email" required placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>
        </div>

        <!-- Passord input-felt med "Show/Hide"-knapp -->
        <div class="field">
          <label for="pw">Password</label>
          <div class="control">
            <input id="pw" type="password" name="password" required placeholder="••••••••">
            <button type="button" class="toggle-pw" aria-label="Toggle password" onclick="togglePw('pw', this)">Show</button>
          </div>
        </div>

        <!-- Bekreft passord input-felt med "Show/Hide"-knapp -->
        <div class="field">
          <label for="pw2">Confirm</label>
          <div class="control">
            <input id="pw2" type="password" name="password2" required placeholder="••••••••">
            <button type="button" class="toggle-pw" aria-label="Toggle password confirm" onclick="togglePw('pw2', this)">Show</button>
          </div>
        </div>

        <!-- Submit-knapp -->
        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:6px;">
          <button type="submit" class="btn">Sign up</button>
        </div>

        <!-- Lenke til innloggingsside -->
        <p class="helper">Already have an account? <a href="login.php">Log in</a></p>
      </form>
    </div>
  </div>

  <script>
    // JavaScript-funksjon for å vise/skjule passord
    function togglePw(id, btn){
      const el = document.getElementById(id);
      if(!el) return;
      // Bytter mellom 'password' og 'text' input-type
      if(el.type === 'password'){ 
        el.type = 'text'; 
        btn.textContent = 'Hide'; 
      }
      else { 
        el.type = 'password'; 
        btn.textContent = 'Show'; 
      }
    }
    // Setter automatisk fokus på e-post-feltet når siden lastes
    document.getElementById('email')?.focus();
  </script>
</body>
</html>
