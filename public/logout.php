<?php
/*  vi bruker logout funkjsonen for å slette sessionen og sende brukeren til login.php */
require __DIR__ . '/../src/auth.php';
logout_user();
header('Location: /PHPPROSJEKT/public/login.php');
exit;
