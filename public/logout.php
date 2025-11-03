<?php
require __DIR__ . '/../src/auth.php';
logout_user();
header('Location: /PHPPROSJEKT/public/login.php');
exit;
