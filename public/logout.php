<?php
/*  vi bruker logout funkjsonen for å slette sessionen og sende brukeren til login.php */

// Inkluderer autentiseringsfunksjoner
require __DIR__ . '/../src/auth.php';
/* Utfører utlogging - sletter sesjon og brukerdata */
logout_user();
header('Location: /PHPPROSJEKT/public/login.php');// Videresender til login-siden
exit;// Stopper ytterligere kjøring av kode
