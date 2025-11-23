<?php
// Sjekker om pdo_mysql-utvidelsen er aktiv
var_dump(extension_loaded('pdo_mysql'));

// Viser tilgjengelige PDO-drivere
print_r(PDO::getAvailableDrivers());
