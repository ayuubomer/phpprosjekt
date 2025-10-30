<?php // public/index.php ?>
<?php
require __DIR__ . '/../src/auth.php';
require_login();
$user = current_user();
?>
<div style="max-width:820px;margin:1rem auto;display:flex;justify-content:space-between;align-items:center;">
  <div>Logged in as <?= htmlspecialchars($user['email']) ?></div>
  
  <a href="logout.php">Log out</a>
</div>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>PHP + Gemini Chat</title>
  <style>
    :root { color-scheme: light dark; }
    body { font-family: system-ui, sans-serif; max-width: 820px; margin: 24px auto; padding: 0 12px; }
    h1 { margin: 0 0 12px; font-size: 1.25rem; }
    #log { border: 1px solid #ccc; border-radius: 10px; padding: 12px; min-height: 240px; white-space: pre-wrap; }
    form { display: flex; gap: 8px; margin-top: 12px; }
    input, button { padding: 10px; font: inherit; }
    input { flex: 1; border: 1px solid #ccc; border-radius: 10px; }
    button { border: 0; border-radius: 10px; cursor: pointer; }
    .msg-user { font-weight: 600; }
    .msg-bot { white-space: pre-wrap; }
    .error { color: #b00020; }
  </style>
</head>
<body>
  <h1>Chatbot (PHP + Gemini)</h1>
  <div id="log" aria-live="polite"></div>

  <form id="chatForm">
    <input id="message" name="message" autocomplete="off" placeholder="Say something…" />
    <button type="submit">Send</button>
  </form>

  <script>
    const log = document.getElementById('log');
    const form = document.getElementById('chatForm');
    const input = document.getElementById('message');

    const append = (role, text) => {
      const div = document.createElement('div');
      div.className = role === 'user' ? 'msg-user' : (role === 'error' ? 'error' : 'msg-bot');
      div.textContent = (role === 'user' ? 'You: ' : role === 'error' ? 'Error: ' : 'Bot: ') + text;
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
