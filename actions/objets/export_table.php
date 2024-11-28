<?php

require('../db.php');


// Nom de la table à exporter
if ($_GET['type'] == 'OV') {
    $table = 'objets_vendus';
    $sql = "SELECT * FROM $table WHERE `date_achat` >= '2024-01-01 00:00:00' AND `date_achat` < '2025-01-01 00:00:00'";
} elseif ($_GET['type'] == 'OC') {
    $table = 'objets_collectes';
    $sql = "SELECT * FROM $table WHERE `date` >= '2024-01-01 00:00:00' AND `date` < '2025-01-01 00:00:00'";
}



// Préparation de la requête pour récupérer les données de la table
$stmt = $db->query($sql);

// Définir le nom du fichier CSV
$filename = $table . '_export_' . date('Y-m-d') . '.csv';

// En-têtes pour le téléchargement du fichier CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Ouvrir un fichier en mémoire pour l'écriture
$output = fopen('php://output', 'w');

// Ajouter l'UTF-8 BOM pour Excel
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

// Récupérer les en-têtes des colonnes
$columns = array_keys($stmt->fetch(PDO::FETCH_ASSOC));
fputcsv($output, $columns);

// Réinitialiser le curseur du résultat
$stmt->execute();

// Boucler à travers les enregistrements et les écrire dans le fichier CSV
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Encoder chaque valeur en UTF-8
    $row = array_map(function($value) {
        return mb_convert_encoding($value, 'UTF-8', 'auto');
    }, $row);
    fputcsv($output, $row);
}

// Fermer le fichier en mémoire
fclose($output);
?>
