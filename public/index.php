<?php // public/index.php ?>
<?php
// Inkluderer autentiseringsfunksjoner og krever at brukeren er logget inn
require __DIR__ . '/../src/auth.php';
require_login();
$user = current_user(); // Henter informasjon om den påloggede brukeren
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>PHP + Gemini Chat</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    /* CSS-variabler for farger og tema */
    :root {
      --bg-1: #0f172a;      /* Mørk bakgrunnsfarge 1 */
      --bg-2: #0b1220;      /* Mørk bakgrunnsfarge 2 */
      --accent: #6b8cff;    /* Primær blå farge */
      --accent-2: #5ce6c8;  /* Sekundær turkis farge */
      --muted: #9aa4b2;     /* Dempet grå tekstfarge */
      --danger: #f44336;    /* Rød feilmeldingsfarge */
    }

    /* Body styling - gradient bakgrunn og sentrert layout */
    body {
      min-height: 100vh;
      margin: 0;
      background: linear-gradient(180deg, var(--bg-1), var(--bg-2));
      color: #e6eef6;
      font-family: Inter, system-ui, sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }

    /* Container for chat-boksen */
    .chat-wrap {
      width: 100%;
      max-width: 720px;
      margin: auto;
    }

    /* Hovedkort med glassmorfisme-effekt */
    .card {
      background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
      border: 1px solid rgba(255,255,255,0.03);
      border-radius: 14px;
      box-shadow: 0 10px 30px rgba(2,6,23,0.5);
      padding: 0 0 22px 0;
      backdrop-filter: blur(6px); /* Glassmorfisme-effekt */
    }

    /* Header-seksjon med brukerinfo og knapper */
    .chat-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 18px 22px 10px 22px;
      border-bottom: 1px solid rgba(255,255,255,0.04);
    }

    .chat-header .user {
      color: var(--muted);
      font-size: 1rem;
    }

    /* Primær knappestil - brukes for alle knapper (Send, Clear, Log out) */
    .btn, 
    button[type="submit"],
    #clearBtn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(90deg, var(--accent), var(--accent-2)); /* Gradient fra blå til turkis */
      color: #03203b;
      border: 0;
      border-radius: 10px;
      padding: 12px 15px;
      font-weight: 700;
      font-size: 1.05rem;
      cursor: pointer;
      box-shadow: 0 8px 24px rgba(91,111,255,0.18);
      transition: transform .1s;
    }

    /* Knapp-animasjon ved klikk */
    .btn:active, 
    button[type="submit"]:active,
    #clearBtn:active {
      transform: translateY(1px);
    }

    /* Chat-logg der meldinger vises */
    .chat-log {
      min-height: 320px;
      max-height: 380px;
      overflow-y: auto; /* Scroll hvis for mange meldinger */
      padding: 22px;
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    /* Brukermelding - vises til høyre med gradient bakgrunn */
    .msg-user {
      align-self: flex-end;
      background: linear-gradient(90deg, var(--accent-2), var(--accent));
      color: #03203b;
      padding: 12px 16px;
      border-radius: 16px 16px 4px 16px; /* Rundede hjørner, nedre høyre spiss */
      max-width: 70%;
      font-weight: 600;
      word-break: break-word;
    }

    /* Bot-melding - vises til venstre med gjennomsiktig bakgrunn */
    .msg-bot {
      align-self: flex-start;
      background: rgba(255,255,255,0.04);
      color: #e6eef6;
      padding: 12px 16px;
      border-radius: 16px 16px 16px 4px; /* Rundede hjørner, nedre venstre spiss */
      max-width: 70%;
      word-break: break-word;
    }

    /* Feilmelding-stil */
    .error {
      color: var(--danger);
      background: rgba(244,67,54,0.06);
      padding: 8px 10px;
      border-radius: 8px;
      border: 1px solid rgba(244,67,54,0.08);
      margin: 0 22px;
    }

    /* Container for chat-skjemaet */
    .chat-form-wrap {
      padding: 0 22px;
    }

    /* Skjema-layout - input og send-knapp side ved side */
    form#chatForm {
      display: flex;
      gap: 8px;
      margin-top: 10px;
    }

    /* Tekstinput-felt */
    input#message {
      flex: 1;
      padding: 14px;
      border-radius: 10px;
      border: 1px solid rgba(255,255,255,0.06);
      background: linear-gradient(180deg, rgba(255,255,255,0.015), transparent);
      color: inherit;
      font-size: 1.1rem;
      box-sizing: border-box;
      outline: none;
    }

    /* Focus-effekt på input-feltet */
    input#message:focus {
      border-color: var(--accent);
      box-shadow: 0 6px 18px rgba(107,140,255,0.12);
    }

    /* Logo-container for chatbot-bilde */
    .chatbot-logo {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 42px;
      height: 42px;
      border-radius: 50%; /* Sirkulær form */
      color: #03203b;
      font-size: 1.3rem;
      font-weight: bold;
      margin-right: 12px;
      box-shadow: 0 2px 8px rgba(91,111,255,0.10);
    }

    /* Responsive design for mobile enheter */
    @media (max-width: 600px) {
      .chat-wrap, .card { max-width: 100%; }
      .chat-header, .chat-log, .chat-form-wrap { padding-left: 10px; padding-right: 10px; }
      .chat-log { min-height: 180px; }
    }
  </style>
</head>
<body>
  <div class="chat-wrap">
    <div class="card">
      <!-- Header med logo, brukernavn og knapper -->
      <div class="chat-header">
        <div class="user">
          <span class="chatbot-logo">
            <img src="img/gymbot.png" alt="Logo" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
          </span>
          Logged in as <?= htmlspecialchars($user['email']) ?>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
          <!-- Knapp for å tømme chat-historikk -->
          <button id="clearBtn" type="button" class="btn">Clear Chat</button>
          <!-- Skjema for utlogging -->
          <form method="post" action="logout.php" style="margin:0;display:inline;">
            <button type="submit" class="btn">Log out</button>
          </form>
        </div>
      </div>

      <!-- Chat-logg der meldinger vises -->
      <div id="log" class="chat-log" aria-live="polite"></div>

      <!-- Skjema for å sende nye meldinger -->
      <div class="chat-form-wrap">
        <form id="chatForm" autocomplete="off">
          <input id="message" name="message" autocomplete="off" placeholder="Say something…" />
          <button type="submit">Send</button>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Henter HTML-elementer
    const log = document.getElementById('log');
    const form = document.getElementById('chatForm');
    const input = document.getElementById('message');

    // Funksjon for å legge til melding i chat-loggen
    const append = (role, text) => {
      const div = document.createElement('div');
      // Setter CSS-klasse basert på hvem som sender meldingen
      div.className = role === 'user' ? 'msg-user' : (role === 'error' ? 'error' : 'msg-bot');
      div.textContent = text;
      log.appendChild(div);
      log.scrollTop = log.scrollHeight; // Scroller ned til siste melding
    };

    // Event listener for å sende melding
    form.addEventListener('submit', async (e) => {
      e.preventDefault(); // Hindrer standard form-innsending
      const text = input.value.trim();
      if (!text) return; // Ikke send tomme meldinger

      append('user', text); // Vis brukermelding umiddelbart
      input.value = ''; // Tøm input-feltet

      try {
        // Send POST-forespørsel til ChatBot.php
        const res = await fetch('ChatBot.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ message: text })
        });
        const data = await res.json();

        // Håndter feil fra API
        if (!res.ok || data.error) throw new Error(data.error || `HTTP ${res.status}`);
        
        // Vis bot-svar
        append('bot', data.reply || '(no reply)');
      } catch (err) {
        // Vis feilmelding hvis noe går galt
        append('error', String(err.message || err));
      }
    });

    // Event listener for "Clear Chat"-knappen
    document.getElementById('clearBtn').addEventListener('click', async () => {
      try {
        console.log('Clearing chat history...');
        
        // Send GET-forespørsel for å tømme chat-historikk
        const res = await fetch('ChatBot.php?action=clear', {
          method: 'GET',
          headers: { 'Content-Type': 'application/json' }
        });
        
        if (!res.ok) {
          throw new Error(`HTTP ${res.status}`);
        }
        
        const data = await res.json();
        console.log('Clear response:', data);
        
        if (data.ok) {
          log.innerHTML = ''; // Tøm chat-loggen visuelt
          alert(`Cleared ${data.deleted} messages from database`);
        } else {
          alert('Clear failed: ' + (data.error || 'Unknown error'));
        }
      } catch (err) {
        console.error('Clear error:', err);
        alert('Failed to clear chat: ' + err.message);
      }
    });
  </script>
</body>
</html>
