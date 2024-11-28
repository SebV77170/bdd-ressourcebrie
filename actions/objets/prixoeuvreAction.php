<?php
require('actions/db.php');

    // Requête pour obtenir les noms distincts
    $sql1 = "SELECT DISTINCT nom FROM objets_vendus WHERE souscat = 'oeuvre'";
    $stmt = $db->query($sql1);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $nom = $row['nom'];

        // Calculer la somme des prix pour le nom actuel
        $sumSql = "SELECT SUM(prix) AS totalPrix FROM objets_vendus WHERE nom = :nom AND souscat = 'oeuvre'";
        $sumStmt = $db->prepare($sumSql);
        $sumStmt->execute(['nom' => $nom]);

        if ($sumRow = $sumStmt->fetch(PDO::FETCH_ASSOC)) {
            $totalPrix = $sumRow['totalPrix'];

            // Afficher le résultat
            echo "<tr><td>" . htmlspecialchars($nom) . "</td><td>" . htmlspecialchars($totalPrix/100) . "€</td></tr>";        }
    }

?>
