<?php
require('actions/users/securityAction.php');
require('actions/db.php');

// Récupération de la date actuelle
$currentDate = date('Y-m-d');

// Récupération des données pour l'année en cours (N) jusqu'à la date actuelle
$currentYear = date('Y');
$sqlYear = 'SELECT SUM(nombre_vente) AS total_ventes, SUM(poids) AS total_poids, SUM(prix_total) AS total_recette 
            FROM bilan 
            WHERE YEAR(STR_TO_DATE(date, "%d/%m/%Y")) = :year AND STR_TO_DATE(date, "%d/%m/%Y") <= :currentDate';
$stmtYear = $db->prepare($sqlYear);
$stmtYear->execute(['year' => $currentYear, 'currentDate' => $currentDate]);
$yearData = $stmtYear->fetch(PDO::FETCH_ASSOC);

// Récupération des données pour l'année précédente (N-1) jusqu'à la même date
$previousYear = $currentYear - 1;
$previousYearDate = date('Y-m-d', strtotime('-1 year', strtotime($currentDate)));
$stmtPreviousYear = $db->prepare($sqlYear);
$stmtPreviousYear->execute(['year' => $previousYear, 'currentDate' => $previousYearDate]);
$previousYearData = $stmtPreviousYear->fetch(PDO::FETCH_ASSOC);

// Récupération des données pour le mois en cours
$currentMonth = date('m');
$sqlMonth = 'SELECT SUM(nombre_vente) AS total_ventes, SUM(poids) AS total_poids, SUM(prix_total) AS total_recette 
             FROM bilan 
             WHERE MONTH(STR_TO_DATE(date, "%d/%m/%Y")) = :month AND YEAR(STR_TO_DATE(date, "%d/%m/%Y")) = :year';
$stmtMonth = $db->prepare($sqlMonth);
$stmtMonth->execute(['month' => $currentMonth, 'year' => $currentYear]);
$monthData = $stmtMonth->fetch(PDO::FETCH_ASSOC);

// Récupération des données pour la semaine en cours
$currentWeekStart = date('Y-m-d', strtotime('monday this week'));
$currentWeekEnd = date('Y-m-d', strtotime('sunday this week'));
$sqlWeek = 'SELECT SUM(nombre_vente) AS total_ventes, SUM(poids) AS total_poids, SUM(prix_total) AS total_recette 
            FROM bilan 
            WHERE STR_TO_DATE(date, "%d/%m/%Y") BETWEEN :weekStart AND :weekEnd';
$stmtWeek = $db->prepare($sqlWeek);
$stmtWeek->execute(['weekStart' => $currentWeekStart, 'weekEnd' => $currentWeekEnd]);
$weekData = $stmtWeek->fetch(PDO::FETCH_ASSOC);

// Récupération des données pour la journée en cours
$currentDay = date('Y-m-d');
$sqlDay = 'SELECT SUM(nombre_vente) AS total_ventes, SUM(poids) AS total_poids, SUM(prix_total) AS total_recette 
           FROM bilan 
           WHERE STR_TO_DATE(date, "%d/%m/%Y") = :day';
$stmtDay = $db->prepare($sqlDay);
$stmtDay->execute(['day' => $currentDay]);
$dayData = $stmtDay->fetch(PDO::FETCH_ASSOC);

// Conversion des données en unités lisibles
function formatData($data) {
    return [
        'total_ventes' => $data['total_ventes'] ?? 0,
        'total_poids' => isset($data['total_poids']) ? round($data['total_poids'] / 1000000, 1) : 0, // Conversion en tonnes (T)
        'total_recette' => isset($data['total_recette']) ? round($data['total_recette'] / 100, 2) : 0 // Conversion en €
    ];
}

$yearData = formatData($yearData);
$previousYearData = formatData($previousYearData);
$monthData = formatData($monthData);
$weekData = formatData($weekData);
$dayData = formatData($dayData);

// Calcul des différences entre N et N-1
$comparisonData = [
    'total_ventes' => $yearData['total_ventes'] - $previousYearData['total_ventes'],
    'total_poids' => $yearData['total_poids'] - $previousYearData['total_poids'],
    'total_recette' => $yearData['total_recette'] - $previousYearData['total_recette']
];
?>

<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body class="corps">
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Résumé des Ventes';
            include("includes/header.php");
            $page = 3;
            include("includes/nav.php");
            if ($_SESSION['admin'] >= 1) {
        ?>

        <!-- Résumé des ventes -->
        <div class="container">
            <h1 class="gros_titre">Résumé des Ventes</h1>
            
            <div class="resume">
                <!-- Résumé de l'année -->
                <div class="cadre">
                    <h2>Résumé de l'Année</h2>
                    <p><strong>Total Ventes :</strong> <?= $yearData['total_ventes'] ?></p>
                    <p><strong>Total Poids :</strong> <?= $yearData['total_poids'] ?> T</p>
                    <p><strong>Total Recette :</strong> <?= $yearData['total_recette'] ?> €</p>
                </div>

                <!-- Résumé du mois -->
                <div class="cadre">
                    <h2>Résumé du Mois</h2>
                    <p><strong>Total Ventes :</strong> <?= $monthData['total_ventes'] ?></p>
                    <p><strong>Total Poids :</strong> <?= $monthData['total_poids'] ?> T</p>
                    <p><strong>Total Recette :</strong> <?= $monthData['total_recette'] ?> €</p>
                </div>

                <!-- Résumé de la semaine -->
                <div class="cadre">
                    <h2>Résumé de la Semaine</h2>
                    <p><strong>Total Ventes :</strong> <?= $weekData['total_ventes'] ?></p>
                    <p><strong>Total Poids :</strong> <?= $weekData['total_poids'] ?> T</p>
                    <p><strong>Total Recette :</strong> <?= $weekData['total_recette'] ?> €</p>
                </div>

                <!-- Résumé de la journée -->
                <div class="cadre">
                    <h2>Résumé de la Journée</h2>
                    <p><strong>Total Ventes :</strong> <?= $dayData['total_ventes'] ?></p>
                    <p><strong>Total Poids :</strong> <?= $dayData['total_poids'] ?> T</p>
                    <p><strong>Total Recette :</strong> <?= $dayData['total_recette'] ?> €</p>
                </div>

                <!-- Tableau comparatif -->
                <div class="cadre">
                    <h2>Comparaison Année N et N-1 (jusqu'à <?= date('d/m/Y') ?>)</h2>
                    <table class="comparatif">
                        <thead>
                            <tr>
                                <th>Indicateur</th>
                                <th>Année <?= $previousYear ?></th>
                                <th>Année <?= $currentYear ?></th>
                                <th>Différence</th>
                                <th>Différence (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Total Ventes</td>
                                <td><?= $previousYearData['total_ventes'] ?></td>
                                <td><?= $yearData['total_ventes'] ?></td>
                                <td><?= $comparisonData['total_ventes'] >= 0 ? '+' : '' ?><?= $comparisonData['total_ventes'] ?></td>
                                <td style="color: <?= $comparisonData['total_ventes'] >= 0 ? 'green' : 'red' ?>;">
                                    <?= $previousYearData['total_ventes'] > 0 ? round(($comparisonData['total_ventes'] / $previousYearData['total_ventes']) * 100, 2) : 0 ?>%
                                </td>
                            </tr>
                            <tr>
                                <td>Total Poids (T)</td>
                                <td><?= $previousYearData['total_poids'] ?> T</td>
                                <td><?= $yearData['total_poids'] ?> T</td>
                                <td><?= $comparisonData['total_poids'] >= 0 ? '+' : '' ?><?= $comparisonData['total_poids'] ?> T</td>
                                <td style="color: <?= $comparisonData['total_poids'] >= 0 ? 'green' : 'red' ?>;">
                                    <?= $previousYearData['total_poids'] > 0 ? round(($comparisonData['total_poids'] / $previousYearData['total_poids']) * 100, 2) : 0 ?>%
                                </td>
                            </tr>
                            <tr>
                                <td>Total Recette (€)</td>
                                <td><?= $previousYearData['total_recette'] ?> €</td>
                                <td><?= $yearData['total_recette'] ?> €</td>
                                <td><?= $comparisonData['total_recette'] >= 0 ? '+' : '' ?><?= $comparisonData['total_recette'] ?> €</td>
                                <td style="color: <?= $comparisonData['total_recette'] >= 0 ? 'green' : 'red' ?>;">
                                    <?= $previousYearData['total_recette'] > 0 ? round(($comparisonData['total_recette'] / $previousYearData['total_recette']) * 100, 2) : 0 ?>%
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <style>
            .resume {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-around;
                margin-top: 20px;
            }

            .cadre {
                border: 1px solid #000;
                padding: 20px;
                width: 45%;
                margin: 10px;
                background-color: #f9f9f9;
                text-align: center;
                border-radius: 8px;
                box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
            }

            .cadre h2 {
                margin-bottom: 15px;
                font-size: 1.5em;
                color: #333;
            }

            .cadre p {
                margin: 5px 0;
                font-size: 1.2em;
            }

            .comparatif {
                width: 100%;
                margin: 0 auto;
                border-collapse: collapse;
                text-align: center;
            }

            .comparatif th, .comparatif td {
                border: 1px solid #ddd;
                padding: 10px;
            }

            .comparatif th {
                background-color: #f4f4f4;
                font-weight: bold;
            }

            .comparatif td {
                font-size: 1.1em;
            }

            .comparatif tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            .comparatif tr:hover {
                background-color: #f1f1f1;
            }
        </style>

        <?php
            } else {
                echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
            }
            include('includes/footer.php');
        ?>
    </body>
</html>