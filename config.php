<?php
// Debug visible uniquement pour TON IP (sinon: on logge seulement)
$devIp = '89.85.182.188'; // <-- mets ton IP publique

if ((($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '') === $devIp)) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL); // ou E_ALL & ~E_DEPRECATED pour cacher les "Deprecated"
} else {
    ini_set('display_errors', '0');          // rien à l'écran pour les visiteurs
    ini_set('log_errors', '1');              // log actifs
    ini_set('error_log', __DIR__.'/../logs/php-errors.log'); // ajuste le chemin
    error_reporting(E_ALL);                  // on logge tout
}
