<?php
// src/auth.php

/**
 * Autentiseringsfunksjoner for brukerinnlogging og sesjonshåndtering
 * 
 * Denne filen inneholder alle nødvendige funksjoner for:
 * - Starte og konfigurere PHP-sesjoner
 * - Logge inn og ut brukere
 * - Sjekke om bruker er innlogget
 * - Beskytte sider som krever innlogging
 */

/**
 * Starter PHP-sesjon med sikkerhetskonfigurasjon
 * 
 * Konfigurerer sesjonen med:
 * - httponly: Forhindrer JavaScript-tilgang til cookies (XSS-beskyttelse)
 * - samesite: Beskytter mot CSRF-angrep
 * - secure: Krever HTTPS i produksjon (hvis tilgjengelig)
 * 
 * @return void
 */
function auth_start(): void {
  // Sjekker om sesjon allerede er startet for å unngå feil
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start([
      'cookie_httponly' => true, // Blokkerer JavaScript-tilgang til session cookie
      'cookie_samesite' => 'Lax', // CSRF-beskyttelse (tillater GET fra eksterne sider)
      'cookie_secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'), // Krever HTTPS hvis tilgjengelig
    ]);
  }
}

/**
 * Logger inn en bruker med bruker-ID og e-post
 * 
 * Regenererer sesjons-ID for å forhindre session fixation-angrep
 * Lagrer brukerdata i $_SESSION['user']
 * 
 * @param int $id Brukerens unike ID fra databasen
 * @param string $email Brukerens e-postadresse
 * @return void
 */
function login_user_id(int $id, string $email): void {
  auth_start(); // Sikrer at sesjon er startet
  session_regenerate_id(true); // Regenererer sesjons-ID (sikkerhet mot session fixation)
  $_SESSION['user'] = ['id' => $id, 'email' => $email]; // Lagrer brukerdata i sesjon
}

/**
 * Bakoverkompatibel funksjon for innlogging uten bruker-ID
 * 
 * Denne funksjonen er valgfri og brukes kun hvis eldre kode
 * fortsatt kaller login_user() uten ID-parameter.
 * 
 * @param string $email Brukerens e-postadresse
 * @return void
 * @deprecated Bruk login_user_id() i stedet
 */
function login_user(string $email): void {
  // Setter en midlertidig ID = 0 for bakoverkompatibilitet
  login_user_id(0, $email);
}

/**
 * Logger ut brukeren og sletter all sesjonsdata
 * 
 * Prosessen:
 * 1. Tømmer $_SESSION-arrayen
 * 2. Sletter session cookie fra brukerens nettleser
 * 3. Ødelegger sesjonen på serveren
 * 
 * @return void
 */
function logout_user(): void {
  auth_start(); // Sikrer at sesjon er startet
  $_SESSION = []; // Tømmer alle sesjonsvariabler
  
  // Sletter session cookie fra nettleseren hvis cookies er aktivert
  if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params(); // Henter cookie-parametere
    setcookie(
      session_name(),      // Cookie-navn (vanligvis PHPSESSID)
      '',                  // Tom verdi (sletter cookie)
      time() - 42000,      // Utløpstid i fortiden (sletter cookie)
      $p['path'],          // Cookie-path
      $p['domain'],        // Cookie-domene
      $p['secure'],        // Secure-flagg (kun HTTPS)
      $p['httponly']       // HttpOnly-flagg (blokkerer JavaScript)
    );
  }
  
  session_destroy(); // Ødelegger sesjonen på serveren
}

/**
 * Henter innlogget bruker fra sesjonen
 * 
 * @return array|null Returnerer brukerdata ['id' => int, 'email' => string] eller null hvis ikke innlogget
 */
function current_user(): ?array {
  auth_start(); // Sikrer at sesjon er startet
  return $_SESSION['user'] ?? null; // Returnerer brukerdata eller null
}

/**
 * Krever at brukeren er innlogget for å få tilgang til siden
 * 
 * Hvis brukeren ikke er innlogget, blir de automatisk
 * videresendt til login-siden.
 * 
 * Bruk denne funksjonen øverst på sider som krever innlogging.
 * 
 * @return void
 */
function require_login(): void {
  // Sjekker om bruker er innlogget
  if (!current_user()) {
    // Videresender til login-siden hvis ikke innlogget
    header('Location: /PHPPROSJEKT/public/login.php');
    exit; // Stopper videre kjøring av kode
  }
}
