<?php // public/index.php ?>
<?php
require __DIR__ . '/../src/auth.php';
require_login();
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>PHP + Gemini Chat</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    :root {
      --bg-1: #0f172a;
      --bg-2: #0b1220;
      --accent: #6b8cff;
      --accent-2: #5ce6c8;
      --muted: #9aa4b2;
      --danger: #f44336;
    }
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
    .chat-wrap {
      width: 100%;
      max-width: 720px;
      margin: auto;
    }
    .card {
      background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
      border: 1px solid rgba(255,255,255,0.03);
      border-radius: 14px;
      box-shadow: 0 10px 30px rgba(2,6,23,0.5);
      padding: 0 0 22px 0;
      backdrop-filter: blur(6px);
    }
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
    .logout-btn {
      background: transparent;
      color: var(--accent);
      border: 1px solid rgba(107,140,255,0.14);
      border-radius: 8px;
      padding: 8px 14px;
      font-weight: 600;
      cursor: pointer;
      transition: background .14s;
    }
    .logout-btn:hover { background: rgba(107,140,255,0.08); }

    .chat-log {
      min-height: 320px;
      max-height: 380px;
      overflow-y: auto;
      padding: 22px;
      display: flex;
      flex-direction: column;
      gap: 14px;
    }
    .msg-user {
      align-self: flex-end;
      background: linear-gradient(90deg, var(--accent-2), var(--accent));
      color: #03203b;
      padding: 12px 16px;
      border-radius: 16px 16px 4px 16px;
      max-width: 70%;
      font-weight: 600;
      word-break: break-word;
    }
    .msg-bot {
      align-self: flex-start;
      background: rgba(255,255,255,0.04);
      color: #e6eef6;
      padding: 12px 16px;
      border-radius: 16px 16px 16px 4px;
      max-width: 70%;
      word-break: break-word;
    }
    .error {
      color: var(--danger);
      background: rgba(244,67,54,0.06);
      padding: 8px 10px;
      border-radius: 8px;
      border: 1px solid rgba(244,67,54,0.08);
      margin: 0 22px;
    }
    .chat-form-wrap {
      padding: 0 22px;
    }
    form#chatForm {
      display: flex;
      gap: 8px;
      margin-top: 10px;
    }
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
    input#message:focus {
      border-color: var(--accent);
      box-shadow: 0 6px 18px rgba(107,140,255,0.12);
    }
    button[type="submit"] {
      background: linear-gradient(90deg,var(--accent),var(--accent-2));
      color: #03203b;
      border: 0;
      border-radius: 10px;
      padding: 0 22px;
      font-weight: 700;
      font-size: 1.05rem;
      cursor: pointer;
      box-shadow: 0 8px 24px rgba(91,111,255,0.18);
      transition: background .13s;
    }
    button[type="submit"]:active { transform: translateY(1px); }
    .chatbot-logo {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 42px;
      height: 42px;
      border-radius: 50%;

      color: #03203b;
      font-size: 1.3rem;
      font-weight: bold;
      margin-right: 12px;
      box-shadow: 0 2px 8px rgba(91,111,255,0.10);
    }
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
      <div class="chat-header">
        <div class="user">
          <span class="chatbot-logo">
            <img src="img/gymbot.png" alt="Logo" style="width:100px;height:100%;object-fit:cover;border-radius:50%;">
          </span>
          Logged in as <?= htmlspecialchars($user['email']) ?>
        </div>
        <form method="post" action="logout.php" style="margin:0;">
          <button type="submit" class="logout-btn">Log out</button>
        </form>
      </div>
      <div id="log" class="chat-log" aria-live="polite"></div>
      <div class="chat-form-wrap">
        <form id="chatForm" autocomplete="off">
          <input id="message" name="message" autocomplete="off" placeholder="Say something…" />
          <button type="submit">Send</button>
        </form>
      </div>
    </div>
  </div>
  <script>
    const log = document.getElementById('log');
    const form = document.getElementById('chatForm');
    const input = document.getElementById('message');

    const append = (role, text) => {
      const div = document.createElement('div');
      div.className = role === 'user' ? 'msg-user' : (role === 'error' ? 'error' : 'msg-bot');
      div.textContent = text;
      log.appendChild(div);
      log.scrollTop = log.scrollHeight;
    };

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const text = input.value.trim();
      if (!text) return;

      append('user', text);
      input.value = '';
      try {
        const res = await fetch('ChatBot.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ message: text })
        });
        const data = await res.json();
        if (!res.ok || data.error) throw new Error(data.error || `HTTP ${res.status}`);
        append('bot', data.reply || '(no reply)');
      } catch (err) {
        append('error', String(err.message || err));
      }
    });
  </script>
</body>
</html>
