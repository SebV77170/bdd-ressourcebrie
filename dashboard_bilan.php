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

// Récupération des données pour les recettes, poids et ventes par mois de l'année en cours (N)
$sqlMonthly = 'SELECT MONTH(STR_TO_DATE(date, "%d/%m/%Y")) AS month, 
                      SUM(prix_total) AS total_recette, 
                      SUM(poids) AS total_poids, 
                      SUM(nombre_vente) AS total_ventes 
               FROM bilan 
               WHERE YEAR(STR_TO_DATE(date, "%d/%m/%Y")) = :year 
               GROUP BY MONTH(STR_TO_DATE(date, "%d/%m/%Y")) 
               ORDER BY MONTH(STR_TO_DATE(date, "%d/%m/%Y"))';
$stmtMonthly = $db->prepare($sqlMonthly);
$stmtMonthly->execute(['year' => $currentYear]);
$monthlyData = $stmtMonthly->fetchAll(PDO::FETCH_ASSOC);

// Récupération des données pour les recettes, poids et ventes par mois de l'année précédente (N-1)
$stmtMonthlyPrevious = $db->prepare($sqlMonthly);
$stmtMonthlyPrevious->execute(['year' => $previousYear]);
$monthlyDataPrevious = $stmtMonthlyPrevious->fetchAll(PDO::FETCH_ASSOC);

// Préparer les données pour les graphiques
$months = [];
$monthlyRecettes = [];
$monthlyRecettesPrevious = [];
$monthlyPoids = [];
$monthlyPoidsPrevious = [];
$monthlyVentes = [];
$monthlyVentesPrevious = [];

// Remplir les données pour l'année N
for ($i = 1; $i <= 12; $i++) {
    $months[] = DateTime::createFromFormat('!m', $i)->format('F'); // Convertir le numéro du mois en nom
    $data = array_filter($monthlyData, fn($d) => $d['month'] == $i);
    $monthlyRecettes[] = $data ? round(current($data)['total_recette'] / 100, 2) : null; // Conversion en euros
    $monthlyPoids[] = $data ? round(current($data)['total_poids'] / 1000, 2) : null; // Conversion en tonnes
    $monthlyVentes[] = $data ? current($data)['total_ventes'] : null;
}

// Remplir les données pour l'année N-1
for ($i = 1; $i <= 12; $i++) {
    $data = array_filter($monthlyDataPrevious, fn($d) => $d['month'] == $i);
    $monthlyRecettesPrevious[] = $data ? round(current($data)['total_recette'] / 100, 2) : 0; // Conversion en euros
    $monthlyPoidsPrevious[] = $data ? round(current($data)['total_poids'] / 1000, 2) : 0; // Conversion en tonnes
    $monthlyVentesPrevious[] = $data ? current($data)['total_ventes'] : 0;
}

// Calculer le panier moyen pour l'année N et N-1
$monthlyPanierMoyen = [];
$monthlyPanierMoyenPrevious = [];

for ($i = 0; $i < 12; $i++) {
    $recette = $monthlyRecettes[$i] ?? 0;
    $ventes = $monthlyVentes[$i] ?? 0;
    $monthlyPanierMoyen[] = $ventes > 0 ? round($recette / $ventes, 2) : null;

    $recettePrevious = $monthlyRecettesPrevious[$i] ?? 0;
    $ventesPrevious = $monthlyVentesPrevious[$i] ?? 0;
    $monthlyPanierMoyenPrevious[] = $ventesPrevious > 0 ? round($recettePrevious / $ventesPrevious, 2) : 0;
}

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
            $titre = 'Tableau de bord des bilans';
            include("includes/header.php");
            $page = 3;
            include("includes/nav.php");
            if ($_SESSION['admin'] >= 1) {
        ?>

        <!-- Résumé des ventes -->
        <div class="container">
            
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
                    <div style="overflow-x: auto;">
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
                                        <?= $previousYearData['total_ventes'] > 0 ? ($comparisonData['total_ventes'] >= 0 ? '+' : '-') . abs(round(($comparisonData['total_ventes'] / $previousYearData['total_ventes']) * 100, 2)) : 0 ?>%
                                    </td>
                                </tr>
                                <tr>
                                    <td>Total Poids (T)</td>
                                    <td><?= $previousYearData['total_poids'] ?> T</td>
                                    <td><?= $yearData['total_poids'] ?> T</td>
                                    <td><?= $comparisonData['total_poids'] >= 0 ? '+' : '' ?><?= $comparisonData['total_poids'] ?> T</td>
                                    <td style="color: <?= $comparisonData['total_poids'] >= 0 ? 'green' : 'red' ?>;">
                                        <?= $previousYearData['total_poids'] > 0 ? ($comparisonData['total_poids'] >= 0 ? '+' : '-') . abs(round(($comparisonData['total_poids'] / $previousYearData['total_poids']) * 100, 2)) : 0 ?>%
                                    </td>
                                </tr>
                                <tr>
                                    <td>Total Recette (€)</td>
                                    <td><?= $previousYearData['total_recette'] ?> €</td>
                                    <td><?= $yearData['total_recette'] ?> €</td>
                                    <td><?= $comparisonData['total_recette'] >= 0 ? '+' : '' ?><?= $comparisonData['total_recette'] ?> €</td>
                                    <td style="color: <?= $comparisonData['total_recette'] >= 0 ? 'green' : 'red' ?>;">
                                        <?= $previousYearData['total_recette'] > 0 ? ($comparisonData['total_recette'] >= 0 ? '+' : '-') . abs(round(($comparisonData['total_recette'] / $previousYearData['total_recette']) * 100, 2)) : 0 ?>%
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="charts-container">
                <!-- Graphique des recettes -->
                <div class="cadre">
                    <h2>Graphique des Recettes (Année <?= $currentYear ?> et <?= $previousYear ?>)</h2>
                    <canvas id="recetteChart"></canvas>
                </div>

                <!-- Graphique des poids -->
                <div class="cadre">
                    <h2>Graphique des Poids (Année <?= $currentYear ?> et <?= $previousYear ?>)</h2>
                    <canvas id="poidsChart"></canvas>
                </div>

                <!-- Graphique des ventes -->
                <div class="cadre">
                    <h2>Graphique des Ventes (Année <?= $currentYear ?> et <?= $previousYear ?>)</h2>
                    <canvas id="ventesChart"></canvas>
                </div>

                <!-- Graphique du panier moyen -->
                <div class="cadre">
                    <h2>Graphique du Panier Moyen (Année <?= $currentYear ?> et <?= $previousYear ?>)</h2>
                    <canvas id="panierMoyenChart"></canvas>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Préparer les données pour les graphiques
            const labels = <?= json_encode($months) ?>; // Mois
            const dataCurrentYearRecettes = <?= json_encode($monthlyRecettes) ?>; // Recettes année N
            const dataPreviousYearRecettes = <?= json_encode($monthlyRecettesPrevious) ?>; // Recettes année N-1
            const dataCurrentYearPoids = <?= json_encode($monthlyPoids) ?>; // Poids année N
            const dataPreviousYearPoids = <?= json_encode($monthlyPoidsPrevious) ?>; // Poids année N-1
            const dataCurrentYearVentes = <?= json_encode($monthlyVentes) ?>; // Ventes année N
            const dataPreviousYearVentes = <?= json_encode($monthlyVentesPrevious) ?>; // Ventes année N-1
            const dataCurrentYearPanierMoyen = <?= json_encode($monthlyPanierMoyen) ?>; // Panier moyen année N
            const dataPreviousYearPanierMoyen = <?= json_encode($monthlyPanierMoyenPrevious) ?>; // Panier moyen année N-1

            // Configuration des graphiques
            const createChart = (ctx, labelCurrent, dataCurrent, labelPrevious, dataPrevious, yLabel) => {
                return new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: labelPrevious,
                                data: dataPrevious,
                                borderColor: 'rgba(255, 99, 132, 1)',
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                borderWidth: 2,
                                tension: 0.4
                            },
                            {
                                label: labelCurrent,
                                data: dataCurrent,
                                borderColor: 'rgba(75, 192, 192, 1)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderWidth: 2,
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.raw + ' ' + yLabel;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Mois'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: yLabel
                                },
                                beginAtZero: true
                            }
                        }
                    }
                });
            };

            // Initialiser les graphiques
            createChart(document.getElementById('recetteChart').getContext('2d'), 'Recettes <?= $currentYear ?> (€)', dataCurrentYearRecettes, 'Recettes <?= $previousYear ?> (€)', dataPreviousYearRecettes, '€');
            createChart(document.getElementById('poidsChart').getContext('2d'), 'Poids <?= $currentYear ?> (T)', dataCurrentYearPoids, 'Poids <?= $previousYear ?> (T)', dataPreviousYearPoids, 'T');
            createChart(document.getElementById('ventesChart').getContext('2d'), 'Ventes <?= $currentYear ?>', dataCurrentYearVentes, 'Ventes <?= $previousYear ?>', dataPreviousYearVentes, 'Ventes');
            createChart(document.getElementById('panierMoyenChart').getContext('2d'), 'Panier Moyen <?= $currentYear ?> (€)', dataCurrentYearPanierMoyen, 'Panier Moyen <?= $previousYear ?> (€)', dataPreviousYearPanierMoyen, '€');
        </script>

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

            canvas {
                max-width: 100%;
                height: auto;
            }

            /* Responsive layout for charts */
            .charts-container {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                gap: 20px;
            }

            .charts-container .cadre {
                flex: 1 1 calc(33.333% - 20px); /* Three charts per row on large screens */
            }

            @media (max-width: 1024px) {
                .charts-container .cadre {
                    flex: 1 1 calc(50% - 20px); /* Two charts per row on medium screens */
                }
            }

            @media (max-width: 768px) {
                .charts-container .cadre {
                    flex: 1 1 100%; /* One chart per row on small screens */
                }
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