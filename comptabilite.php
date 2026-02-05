<?php
require('actions/users/securityAction.php');
require('actions/db.php');
require('actions/comptabilite/bilanHelpers.php');
require('actions/comptabilite/sessionCaisseHelpers.php');

$columns = [];
$dateColumn = null;
$ecartColumn = null;
$montantReelColumn = null;
$montantReelCarteColumn = null;
$montantReelChequeColumn = null;
$montantReelVirementColumn = null;
$errors = [];
$results = [];
$combinedRows = [];
$bilanTotals = [];
$years = [];
$selectedYear = null;

try {
    $columns = getSessionCaisseColumns($db);
    $columnNames = array_column($columns, 'Field');
    $dateColumn = findSessionCaisseDateColumn($columnNames);
    $ecartColumn = findSessionCaisseEcartColumn($columnNames);
    $montantReelColumn = findSessionCaisseMontantReelColumn($columnNames);
    $montantReelCarteColumn = in_array('montant_reel_carte', $columnNames, true) ? 'montant_reel_carte' : null;
    $montantReelChequeColumn = in_array('montant_reel_cheque', $columnNames, true) ? 'montant_reel_cheque' : null;
    $montantReelVirementColumn = in_array('montant_reel_virement', $columnNames, true) ? 'montant_reel_virement' : null;

    if (!$ecartColumn) {
        $errors[] = "La colonne 'ecart' est introuvable dans la table session_caisse.";
    }

    if (!$montantReelColumn) {
        $errors[] = "La colonne 'montant_reel' est introuvable dans la table session_caisse.";
    }
    if (!$montantReelCarteColumn) {
        $errors[] = "La colonne 'montant_reel_carte' est introuvable dans la table session_caisse.";
    }
    if (!$montantReelChequeColumn) {
        $errors[] = "La colonne 'montant_reel_cheque' est introuvable dans la table session_caisse.";
    }
    if (!$montantReelVirementColumn) {
        $errors[] = "La colonne 'montant_reel_virement' est introuvable dans la table session_caisse.";
    }

    if ($dateColumn) {
        $years = getSessionCaisseYears($db, $dateColumn);

        if (isset($_GET['year'])) {
            $selectedYear = (int) $_GET['year'];
        } elseif (!empty($years)) {
            $selectedYear = (int) $years[0];
        } else {
            $selectedYear = (int) date('Y');
        }

        if ($selectedYear) {
            $results = getSessionCaisseRowsForYear($db, $dateColumn, $selectedYear);
            $bilanTotals = getBilanTotalsForYear($db, $selectedYear);
        }
    } else {
        $errors[] = "Aucune colonne de date n'a été trouvée pour filtrer par année.";
    }
} catch (Exception $exception) {
    $errors[] = 'Impossible de récupérer les sessions de caisse : ' . $exception->getMessage();
}

$recap = $ecartColumn ? buildEcartRecap($results, $ecartColumn) : [
    'negative' => 0,
    'positive' => 0,
    'count' => 0,
];

if ($selectedYear) {
    foreach ($bilanTotals as $bilan) {
        $dateKey = $bilan['date_key'] ?? null;
        if (!$dateKey) {
            continue;
        }

        $combinedRows[$dateKey] = [
            'date' => $bilan['date_label'] ?? $dateKey,
            'montant_reel_espece' => $bilan['montant_encaisse_espece'] ?? null,
            'montant_encaisse_espece' => $bilan['montant_encaisse_espece'] ?? null,
            'montant_reel_carte' => $bilan['montant_encaisse_carte'] ?? null,
            'montant_encaisse_carte' => $bilan['montant_encaisse_carte'] ?? null,
            'montant_reel_cheque' => $bilan['montant_encaisse_cheque'] ?? null,
            'montant_encaisse_cheque' => $bilan['montant_encaisse_cheque'] ?? null,
            'montant_reel_virement' => $bilan['montant_encaisse_virement'] ?? null,
            'montant_encaisse_virement' => $bilan['montant_encaisse_virement'] ?? null,
            'ecart' => null,
            'date_key' => $dateKey,
        ];
    }

    foreach ($results as $row) {
        $sessionDate = parseDateValueToDateTime($row[$dateColumn] ?? null);
        if (!$sessionDate) {
            continue;
        }

        $dateKey = $sessionDate->format('Y-m-d');
        if (!isset($combinedRows[$dateKey])) {
            $combinedRows[$dateKey] = [
                'date' => $sessionDate->format('d/m/Y'),
                'montant_reel_espece' => null,
                'montant_encaisse_espece' => null,
                'montant_reel_carte' => null,
                'montant_encaisse_carte' => null,
                'montant_reel_cheque' => null,
                'montant_encaisse_cheque' => null,
                'montant_reel_virement' => null,
                'montant_encaisse_virement' => null,
                'ecart' => null,
                'date_key' => $dateKey,
            ];
        }

        $combinedRows[$dateKey]['montant_reel_espece'] = $row[$montantReelColumn] ?? $combinedRows[$dateKey]['montant_reel_espece'] ?? null;
        $combinedRows[$dateKey]['montant_reel_carte'] = $montantReelCarteColumn ? ($row[$montantReelCarteColumn] ?? $combinedRows[$dateKey]['montant_reel_carte'] ?? null) : $combinedRows[$dateKey]['montant_reel_carte'] ?? null;
        $combinedRows[$dateKey]['montant_reel_cheque'] = $montantReelChequeColumn ? ($row[$montantReelChequeColumn] ?? $combinedRows[$dateKey]['montant_reel_cheque'] ?? null) : $combinedRows[$dateKey]['montant_reel_cheque'] ?? null;
        $combinedRows[$dateKey]['montant_reel_virement'] = $montantReelVirementColumn ? ($row[$montantReelVirementColumn] ?? $combinedRows[$dateKey]['montant_reel_virement'] ?? null) : $combinedRows[$dateKey]['montant_reel_virement'] ?? null;
        $combinedRows[$dateKey]['ecart'] = $row[$ecartColumn] ?? null;
    }

    $combinedRows = array_values($combinedRows);
    usort($combinedRows, function (array $a, array $b): int {
        return strcmp($b['date_key'], $a['date_key']);
    });
}

function formatMontantValue($value): string
{
    if ($value === null || $value === '') {
        return '';
    }

    return number_format(((float) $value) / 100, 2, ',', ' ') . ' €';
}
?>

<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body class="corps">
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Comptabilité';
            include("includes/header.php");
            $page = 5;
            include("includes/nav.php");
        ?>

        <?php if($_SESSION['admin'] >= 1): ?>
            <div class="doc">
                <ul class="doc_ul">
                    <a class="doc_lien" href="administration.php"><li class="doc_li" id="bleu">Retour à l'administration</li></a>
                </ul>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert" style="margin: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($years) && $dateColumn): ?>
                <div style="text-align: center;">
                    <form method="GET" class="formulaire-mois">
                        <label for="year">Année :</label>
                        <select name="year" id="year">
                            <?php foreach ($years as $year): ?>
                                <option value="<?= (int) $year ?>" <?= (int) $year === $selectedYear ? 'selected' : '' ?>>
                                    <?= (int) $year ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">Afficher</button>
                    </form>
                </div>
            <?php endif; ?>

            <div style="text-align: center; margin: 30px; padding: 20px; background-color: #f9f9f9; border: 2px solid #007BFF; border-radius: 10px;">
                <h3 style="color: #007BFF;">Récapitulatif annuel</h3>
                <table class="tableau" style="width: 80%; margin: 0 auto; border-collapse: collapse; background-color: #ffffff;">
                    <tr class="ligne" style="background-color: #007BFF; color: #ffffff;">
                        <th class="cellule_tete" style="padding: 10px; border: 1px solid #007BFF;">Total des écarts négatifs</th>
                        <th class="cellule_tete" style="padding: 10px; border: 1px solid #007BFF;">Total des écarts positifs</th>
                        <th class="cellule_tete" style="padding: 10px; border: 1px solid #007BFF;">Nombre de sessions</th>
                    </tr>
                    <tr class="ligne" style="text-align: center;">
                        <td class="colonne" style="padding: 10px; border: 1px solid #007BFF;"><?= formatEcartValue($recap['negative']) ?></td>
                        <td class="colonne" style="padding: 10px; border: 1px solid #007BFF;"><?= formatEcartValue($recap['positive']) ?></td>
                        <td class="colonne" style="padding: 10px; border: 1px solid #007BFF;"><?= $recap['count'] ?></td>
                    </tr>
                </table>
            </div>

            <?php if ($ecartColumn && $dateColumn && $selectedYear): ?>
                <div style="text-align: center; margin-bottom: 20px;">
                    <a class="stdbouton" href="actions/comptabilite/generateComptabilitePdf.php?year=<?= (int) $selectedYear ?>">
                        Télécharger le PDF des sessions avec écarts
                    </a>
                </div>
            <?php endif; ?>

            <?php if (!empty($combinedRows)): ?>
                <table class="tableau">
                    <tr class="ligne">
                        <th class="cellule_tete">Date</th>
                        <th class="cellule_tete">Montant réel espèce</th>
                        <th class="cellule_tete">Montant encaissé espèce</th>
                        <th class="cellule_tete">Montant réel carte</th>
                        <th class="cellule_tete">Montant encaissé carte</th>
                        <th class="cellule_tete">Montant réel chèque</th>
                        <th class="cellule_tete">Montant encaissé chèque</th>
                        <th class="cellule_tete">Montant réel virement</th>
                        <th class="cellule_tete">Montant encaissé virement</th>
                    </tr>
                    <?php foreach ($combinedRows as $row): ?>
                        <tr class="ligne">
                            <td class="colonne"><?= htmlspecialchars((string) ($row['date'] ?? '')) ?></td>
                            <td class="colonne"><?= htmlspecialchars(formatMontantValue($row['montant_reel_espece'] ?? null)) ?></td>
                            <td class="colonne"><?= htmlspecialchars(formatMontantValue($row['montant_encaisse_espece'] ?? null)) ?></td>
                            <td class="colonne"><?= htmlspecialchars(formatMontantValue($row['montant_reel_carte'] ?? null)) ?></td>
                            <td class="colonne"><?= htmlspecialchars(formatMontantValue($row['montant_encaisse_carte'] ?? null)) ?></td>
                            <td class="colonne"><?= htmlspecialchars(formatMontantValue($row['montant_reel_cheque'] ?? null)) ?></td>
                            <td class="colonne"><?= htmlspecialchars(formatMontantValue($row['montant_encaisse_cheque'] ?? null)) ?></td>
                            <td class="colonne"><?= htmlspecialchars(formatMontantValue($row['montant_reel_virement'] ?? null)) ?></td>
                            <td class="colonne"><?= htmlspecialchars(formatMontantValue($row['montant_encaisse_virement'] ?? null)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p style="text-align: center;">Aucune donnée de session ou de bilan trouvée pour cette année.</p>
            <?php endif; ?>
        <?php else: ?>
            <p>Vous n'êtes pas administrateur, veuillez contacter le webmaster svp.</p>
        <?php endif; ?>

        <?php include('includes/footer.php'); ?>
    </body>
</html>
