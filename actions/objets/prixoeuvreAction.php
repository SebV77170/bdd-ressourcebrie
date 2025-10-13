<?php
require('actions/db.php');

$annee = isset($_GET['annee']) && $_GET['annee'] !== '' ? (int)$_GET['annee'] : null;


    // Requête pour obtenir les noms distincts
    $sql1 = "SELECT DISTINCT nom FROM objets_vendus WHERE souscat = 'oeuvre'";
    $stmt = $db->query($sql1);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $nom = $row['nom'];

        // Calculer la somme des prix pour le nom actuel
        $sumSql = "SELECT SUM(prix) AS totalPrix
                    FROM `objets_vendus`
                    WHERE LEFT(`date_achat`,4) = :annee
                      AND `nom` = :nom
                    AND LOWER(TRIM(REPLACE(REPLACE(`souscat`, 'Œ', 'oe'), 'œ', 'oe')))
                        IN ('oeuvre', 'oeuvres') ORDER BY `timestamp` DESC";
        $sumStmt = $db->prepare($sumSql);
        $sumStmt->execute(['nom' => $nom, 'annee' => $annee]);

        if ($sumRow = $sumStmt->fetch(PDO::FETCH_ASSOC)) {
            $totalPrix = $sumRow['totalPrix'];

            // Afficher le résultat
            echo "<tr><td>" . htmlspecialchars($nom) . "</td><td>" . htmlspecialchars($totalPrix/100) . "€</td></tr>";        }
    }

?>
