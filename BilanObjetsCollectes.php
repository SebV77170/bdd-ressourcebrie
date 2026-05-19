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

$feedbackMessage = null;
$feedbackType = 'success';

if (isset($_POST['action_collecte'])) {
    if (!isset($_SESSION['admin']) || (int) $_SESSION['admin'] <= 1) {
        $feedbackMessage = 'Action refusée : seules les personnes administratrices peuvent modifier ou supprimer une saisie.';
        $feedbackType = 'error';
    } elseif ($_POST['action_collecte'] === 'delete' && isset($_POST['id'])) {
        $deleteStmt = $db->prepare('DELETE FROM objets_collectes WHERE id = ?');
        $deleteStmt->execute([(int) $_POST['id']]);
        $feedbackMessage = 'Saisie supprimée avec succès.';
    } elseif ($_POST['action_collecte'] === 'edit' && isset($_POST['id'])) {
        $id = (int) $_POST['id'];
        $nom = trim((string) ($_POST['nom'] ?? ''));
        $categorie = trim((string) ($_POST['categorie'] ?? ''));
        $souscat = trim((string) ($_POST['souscat'] ?? ''));
        $flux = trim((string) ($_POST['flux'] ?? ''));
        $poids = (float) ($_POST['poids'] ?? 0);

        if ($nom === '' || $categorie === '' || $poids <= 0) {
            $feedbackMessage = 'Modification impossible : nom, catégorie et poids sont obligatoires.';
            $feedbackType = 'error';
        } else {
            $updateStmt = $db->prepare('UPDATE objets_collectes SET nom = ?, categorie = ?, souscat = ?, flux = ?, poids = ? WHERE id = ?');
            $updateStmt->execute([$nom, $categorie, $souscat, $flux, $poids, $id]);
            $feedbackMessage = 'Saisie modifiée avec succès.';
        }
    }
}

$statement = $db->query('SELECT id, nom, categorie, souscat, poids, date, flux FROM objets_collectes ORDER BY date DESC, id DESC');
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
$selectedDay = '';
if (isset($_GET['annee']) && $_GET['annee'] !== '') {
    $candidateYear = filter_var($_GET['annee'], FILTER_VALIDATE_INT);
    if ($candidateYear !== false && in_array($candidateYear, $availableYears, true)) {
        $selectedYear = $candidateYear;
    }
}

$availableDays = [];
foreach ($objetsCollectes as $objet) {
    $dayValue = substr((string) ($objet['date'] ?? ''), 0, 10);
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dayValue)) {
        $availableDays[$dayValue] = true;
    }
}
$availableDays = array_keys($availableDays);
rsort($availableDays);

if (isset($_GET['jour']) && $_GET['jour'] !== '') {
    $candidateDay = trim((string) $_GET['jour']);
    if (in_array($candidateDay, $availableDays, true)) {
        $selectedDay = $candidateDay;
    }
}

$filteredCollectes = array_filter($objetsCollectes, static function (array $objet) use ($selectedYear, $selectedDay): bool {
    if ($selectedYear !== null && $objet['_year'] !== $selectedYear) {
        return false;
    }

    if ($selectedDay !== '') {
        return substr((string) ($objet['date'] ?? ''), 0, 10) === $selectedDay;
    }

    return true;
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
            if($_SESSION['admin'] > 1){
            ?>


        <?php if(isset($message)){
            echo '<p style="text-align: center;">'.$message.'</p>';
        }
        echo $feedbackMessage ? '<p class="feedback '.htmlspecialchars($feedbackType, ENT_QUOTES).'">'.htmlspecialchars($feedbackMessage, ENT_QUOTES).'</p>' : '';
        ?>

        <style>
            .collectes-panel { max-width: 1100px; margin: 0 auto 2rem; }
            .collectes-actions { display:flex; gap:.5rem; flex-wrap:wrap; }
            .collectes-list { overflow-x:auto; }
            .btn-small { padding:.4rem .7rem; border-radius:8px; border:none; cursor:pointer; }
            .btn-edit { background:#1f6feb; color:white; }
            .btn-delete { background:#d1242f; color:white; }
            .collectes-modal { position:fixed; inset:0; background:rgba(0,0,0,.55); display:none; align-items:flex-end; justify-content:center; z-index:2000; }
            .collectes-modal.open { display:flex; }
            .collectes-modal-card { width:min(700px,100%); background:#fff; border-radius:16px 16px 0 0; padding:1rem; max-height:90vh; overflow:auto; }
            @media (min-width: 768px) { .collectes-modal { align-items:center; } .collectes-modal-card { border-radius:16px; } }
            .feedback.success { color:#0c7a2f; text-align:center; }
            .feedback.error { color:#b42318; text-align:center; }
        </style>


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
            <label class="champ" for="jour" style="margin-top: .8rem;">Filtrer par jour :</label>
            <select id="jour" name="jour" class="input">
                <option value="">Tous les jours</option>
                <?php foreach ($availableDays as $dayOption): ?>
                    <option value="<?php echo htmlspecialchars($dayOption, ENT_QUOTES); ?>" <?php echo $selectedDay === $dayOption ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($dayOption, ENT_QUOTES); ?>
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


        <section class="collectes-panel">
            <h2 style="text-align:center;">Liste des saisies</h2>
            <div class="collectes-list">
                <table class="tableau">
                    <tr class="ligne">
                        <th class="cellule_tete">Date</th><th class="cellule_tete">Nom</th><th class="cellule_tete">Catégorie</th><th class="cellule_tete">Sous-cat.</th><th class="cellule_tete">Flux</th><th class="cellule_tete">Poids (g)</th><th class="cellule_tete">Actions</th>
                    </tr>
                    <?php foreach ($filteredCollectes as $objet): ?>
                        <tr class="ligne">
                            <td class="colonne"><?php echo htmlspecialchars((string) $objet['date'], ENT_QUOTES); ?></td>
                            <td class="colonne"><?php echo htmlspecialchars((string) $objet['nom'], ENT_QUOTES); ?></td>
                            <td class="colonne"><?php echo htmlspecialchars((string) $objet['categorie'], ENT_QUOTES); ?></td>
                            <td class="colonne"><?php echo htmlspecialchars((string) $objet['souscat'], ENT_QUOTES); ?></td>
                            <td class="colonne"><?php echo htmlspecialchars((string) $objet['flux'], ENT_QUOTES); ?></td>
                            <td class="colonne"><?php echo htmlspecialchars((string) $objet['poids'], ENT_QUOTES); ?></td>
                            <td class="colonne">
                                <div class="collectes-actions">
                                    <button type="button" class="btn-small btn-edit open-edit"
                                        data-id="<?php echo (int) $objet['id']; ?>"
                                        data-nom="<?php echo htmlspecialchars((string) $objet['nom'], ENT_QUOTES); ?>"
                                        data-categorie="<?php echo htmlspecialchars((string) $objet['categorie'], ENT_QUOTES); ?>"
                                        data-souscat="<?php echo htmlspecialchars((string) $objet['souscat'], ENT_QUOTES); ?>"
                                        data-flux="<?php echo htmlspecialchars((string) $objet['flux'], ENT_QUOTES); ?>"
                                        data-poids="<?php echo htmlspecialchars((string) $objet['poids'], ENT_QUOTES); ?>">Modifier</button>
                                    <form method="post" onsubmit="return confirm('Supprimer cette saisie ?');">
                                        <input type="hidden" name="action_collecte" value="delete">
                                        <input type="hidden" name="id" value="<?php echo (int) $objet['id']; ?>">
                                        <button class="btn-small btn-delete" type="submit">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </section>

        <div class="collectes-modal" id="editModal">
            <div class="collectes-modal-card">
                <h3>Modifier une saisie</h3>
                <form method="post" class="jeuchamp">
                    <input type="hidden" name="action_collecte" value="edit">
                    <input type="hidden" id="edit-id" name="id">
                    <label class="champ" for="edit-nom">Nom</label><input class="input" id="edit-nom" name="nom" required>
                    <label class="champ" for="edit-categorie">Catégorie</label><input class="input" id="edit-categorie" name="categorie" required>
                    <label class="champ" for="edit-souscat">Sous-catégorie</label><input class="input" id="edit-souscat" name="souscat">
                    <label class="champ" for="edit-flux">Flux</label><input class="input" id="edit-flux" name="flux">
                    <label class="champ" for="edit-poids">Poids (g)</label><input class="input" id="edit-poids" name="poids" type="number" step="0.01" min="0.01" required>
                    <div style="display:flex; gap:.6rem; margin-top:1rem;">
                        <button class="input inputsubmit" type="submit">Enregistrer</button>
                        <button class="input" type="button" id="closeModal">Annuler</button>
                    </div>
                </form>
            </div>
        </div>

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
        <script>
            const editModal = document.getElementById('editModal');
            document.querySelectorAll('.open-edit').forEach((button) => {
                button.addEventListener('click', () => {
                    document.getElementById('edit-id').value = button.dataset.id || '';
                    document.getElementById('edit-nom').value = button.dataset.nom || '';
                    document.getElementById('edit-categorie').value = button.dataset.categorie || '';
                    document.getElementById('edit-souscat').value = button.dataset.souscat || '';
                    document.getElementById('edit-flux').value = button.dataset.flux || '';
                    document.getElementById('edit-poids').value = button.dataset.poids || '';
                    editModal?.classList.add('open');
                });
            });
            document.getElementById('closeModal')?.addEventListener('click', () => editModal?.classList.remove('open'));
            editModal?.addEventListener('click', (e) => { if (e.target === editModal) editModal.classList.remove('open'); });
        </script>
    </body>
</html>
