<?php
// public/logout.php
require __DIR__ . '/../src/auth.php';

logout_user(); // tøm sesjonen og slett cookie
header('Location: /PHPPROSJEKT/public/login.php'); // tilbake til login
exit;
?>