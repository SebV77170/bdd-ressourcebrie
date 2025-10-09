<?php
require('actions/users/securityAction.php');
require('actions/db.php');

/**
 * Build a normalized key for a category name so that close names are grouped
 * together.
 */
function normalizeCategoryKey(string $category): string
{
    $normalized = iconv('UTF-8', 'ASCII//TRANSLIT', $category);
    if ($normalized === false) {
        $normalized = $category;
    }

    $normalized = strtolower($normalized);
    $normalized = preg_replace('/[^a-z0-9]+/u', '', $normalized);

    if ($normalized === '') {
        return 'non_renseigne';
    }

    $synonyms = [
        'electromenager' => 'electromenager',
        'electromenagers' => 'electromenager',
        'electromenage' => 'electromenager',
        'electromanager' => 'electromenager',
        'vetements' => 'vetement',
        'vetement' => 'vetement',
        'veterements' => 'vetement',
    ];

    return $synonyms[$normalized] ?? $normalized;
}

/**
 * Extract the year from a date stored in different textual formats.
 */
function extractYear(?string $rawDate): ?int
{
    if ($rawDate === null) {
        return null;
    }

    $rawDate = trim($rawDate);
    if ($rawDate === '') {
        return null;
    }

    $formats = [
        'Y-m-d H:i:s',
        'Y-m-d H:i',
        'Y-m-d',
        'd/m/Y H:i:s',
        'd/m/Y H:i',
        'd/m/Y',
        'd-m-Y',
        'd-m-Y H:i',
        'm/d/Y',
        'd.m.Y',
    ];

    foreach ($formats as $format) {
        $dateTime = DateTime::createFromFormat($format, $rawDate);
        if ($dateTime instanceof DateTime) {
            return (int) $dateTime->format('Y');
        }
    }

    if (ctype_digit($rawDate) && strlen($rawDate) >= 10) {
        $timestamp = (int) substr($rawDate, 0, 10);
        $dateTime = (new DateTime())->setTimestamp($timestamp);
        return (int) $dateTime->format('Y');
    }

    if (preg_match('/(19|20)\d{2}/', $rawDate, $matches)) {
        return (int) $matches[0];
    }

    return null;
}

$statement = $db->query('SELECT id, nom, categorie, souscat, poids, date, flux FROM objets_collectes');
$objetsCollectes = $statement->fetchAll(PDO::FETCH_ASSOC);

$years = [];
foreach ($objetsCollectes as $index => $objet) {
    $year = extractYear($objet['date']);
    $objetsCollectes[$index]['_year'] = $year;
    if ($year !== null) {
        $years[$year] = true;
    }
}

$availableYears = array_keys($years);
rsort($availableYears);

$selectedYear = null;
if (isset($_GET['annee']) && $_GET['annee'] !== '') {
    $candidateYear = filter_var($_GET['annee'], FILTER_VALIDATE_INT);
    if ($candidateYear !== false && in_array($candidateYear, $availableYears, true)) {
        $selectedYear = $candidateYear;
    }
}

$filteredCollectes = array_filter($objetsCollectes, static function (array $objet) use ($selectedYear): bool {
    if ($selectedYear === null) {
        return true;
    }

    return $objet['_year'] === $selectedYear;
});

$totalWeightGrams = 0;
$categories = [];

foreach ($filteredCollectes as $objet) {
    $poids = (float) $objet['poids'];
    $totalWeightGrams += $poids;

    $categoryKey = normalizeCategoryKey($objet['categorie'] ?? '');
    $label = trim((string) ($objet['categorie'] ?? ''));
    if ($label === '') {
        $label = 'Non renseigné';
    }

    if (!isset($categories[$categoryKey])) {
        $categories[$categoryKey] = [
            'total' => 0.0,
            'labels' => [],
        ];
    }

    $categories[$categoryKey]['total'] += $poids;
    $categories[$categoryKey]['labels'][$label] = ($categories[$categoryKey]['labels'][$label] ?? 0) + 1;
}

foreach ($categories as $key => $category) {
    arsort($category['labels']);
    $categories[$key]['display'] = (string) array_key_first($category['labels']);
}

usort($categories, static function (array $a, array $b): int {
    return $b['total'] <=> $a['total'];
});

$chartLabels = [];
$chartData = [];

foreach ($categories as $category) {
    $chartLabels[] = $category['display'];
    $chartData[] = round($category['total'] / 1000, 3);
}

$totalWeightKg = $totalWeightGrams / 1000;
?>


<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body class="corps">
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Objets collectés';
            include("includes/header.php");
            $page = 3;
            include("includes/nav.php");
            ?>


            <?php
            if($_SESSION['admin'] >= 1){
            ?>


        <?php if(isset($message)){
            echo '<p style="text-align: center;">'.$message.'</p>';
        }
        ?>

        <section style="text-align: center;">
            <p>Poids total d'objets <strong>collectés</strong> pour
                <?php echo $selectedYear === null ? 'toutes les années' : 'l&#039;année '.$selectedYear; ?> :
                <strong><?php echo number_format($totalWeightKg, 2, ',', ' '); ?> kg</strong>
            </p>
        </section>

        <form method="get" class="jeuchamp" style="margin: 1.5rem auto; max-width: 420px;">
            <label class="champ" for="annee">Filtrer par année :</label>
            <select id="annee" name="annee" class="input">
                <option value="">Toutes les années</option>
                <?php foreach ($availableYears as $yearOption): ?>
                    <option value="<?php echo htmlspecialchars((string) $yearOption, ENT_QUOTES); ?>" <?php echo $selectedYear === (int) $yearOption ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars((string) $yearOption, ENT_QUOTES); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="input inputsubmit" style="margin-top: 1rem;">Actualiser</button>
        </form>

        <?php if ($totalWeightGrams === 0): ?>
            <p style="text-align: center;">Aucune donnée disponible pour la période sélectionnée.</p>
        <?php else: ?>
            <div style="max-width: 960px; margin: 0 auto 2rem;">
                <table class="tableau">
                    <tr class="ligne">
                        <th class="cellule_tete">Catégorie</th>
                        <th class="cellule_tete">Poids total (kg)</th>
                        <th class="cellule_tete">Répartition</th>
                    </tr>
                    <?php foreach ($categories as $category):
                        $poidsKg = $category['total'] / 1000;
                        $pourcentage = $totalWeightGrams > 0 ? ($poidsKg * 100) / $totalWeightKg : 0;
                    ?>
                        <tr class="ligne">
                            <td class="colonne"><?php echo htmlspecialchars($category['display'], ENT_QUOTES); ?></td>
                            <td class="colonne"><?php echo number_format($poidsKg, 2, ',', ' '); ?></td>
                            <td class="colonne"><?php echo number_format($pourcentage, 1, ',', ' '); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <div style="max-width: 640px; margin: 0 auto 3rem;">
                <canvas id="collectesPie"></canvas>
            </div>
        <?php endif; ?>

        <?php
            }else{
                echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
            }
            include('includes/footer.php');
            ?>

        <?php if ($totalWeightGrams > 0): ?>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                const collectesLabels = <?php echo json_encode($chartLabels, JSON_UNESCAPED_UNICODE); ?>;
                const collectesData = <?php echo json_encode($chartData, JSON_UNESCAPED_UNICODE); ?>;

                const ctx = document.getElementById('collectesPie');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: collectesLabels,
                            datasets: [{
                                data: collectesData,
                                backgroundColor: [
                                    '#f94144', '#f3722c', '#f8961e', '#f9844a', '#f9c74f',
                                    '#90be6d', '#43aa8b', '#4d908e', '#577590', '#277da1'
                                ],
                            }]
                        },
                        options: {
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (context) => {
                                            const value = Number(context.parsed ?? 0);
                                            return `${context.label}: ${value.toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} kg`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            </script>
        <?php endif; ?>
    </body>
</html>
