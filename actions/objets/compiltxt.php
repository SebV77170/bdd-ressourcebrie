<?php
require('../../app/models/compil_tickets.php');

if (!isset($_GET['date'])) {
    die('Date manquante (format attendu : YYYY-MM-DD)');
}

$date = $_GET['date'];

try {
    $compil = new compil_tickets($date);
    $compil->compile();
} catch (Throwable $e) {
    // Pour éviter le 500 silencieux et voir l’erreur pendant les tests
    echo "Erreur : " . htmlspecialchars($e->getMessage());
}
