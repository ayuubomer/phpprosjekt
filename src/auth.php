<?php
// src/auth.php
function auth_start(): void {
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start([
      'cookie_httponly' => true,
      'cookie_samesite' => 'Lax',
      'cookie_secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
    ]);
  }
}

function login_user(string $email): void {
  auth_start();
  session_regenerate_id(true);
  $_SESSION['user'] = ['email' => $email];
}

function logout_user(): void {
  auth_start();
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
  }
  session_destroy();
}

function current_user(): ?array {
  auth_start();
  return $_SESSION['user'] ?? null;
}

function require_login(): void {
  if (!current_user()) {
    header('Location: /PHPPROSJEKT/public/login.php');
    exit;
  }
}
