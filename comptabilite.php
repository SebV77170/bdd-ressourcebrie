<?php
require('actions/users/securityAction.php');
require('actions/db.php');
require('actions/comptabilite/sessionCaisseHelpers.php');

$columns = [];
$dateColumn = null;
$ecartColumn = null;
$errors = [];
$results = [];
$years = [];
$selectedYear = null;

try {
    $columns = getSessionCaisseColumns($db);
    $columnNames = array_column($columns, 'Field');
    $dateColumn = findSessionCaisseDateColumn($columnNames);
    $ecartColumn = findSessionCaisseEcartColumn($columnNames);

    if (!$ecartColumn) {
        $errors[] = "La colonne 'ecart' est introuvable dans la table session_caisse.";
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

            <?php if (!empty($results) && !empty($columns)): ?>
                <table class="tableau">
                    <tr class="ligne">
                        <?php foreach ($columns as $column): ?>
                            <th class="cellule_tete"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $column['Field']))) ?></th>
                        <?php endforeach; ?>
                    </tr>
                    <?php foreach ($results as $row): ?>
                        <tr class="ligne">
                            <?php foreach ($columns as $column): ?>
                                <?php $value = $row[$column['Field']] ?? ''; ?>
                                <td class="colonne"><?= htmlspecialchars((string) $value) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p style="text-align: center;">Aucune session de caisse trouvée pour cette année.</p>
            <?php endif; ?>
        <?php else: ?>
            <p>Vous n'êtes pas administrateur, veuillez contacter le webmaster svp.</p>
        <?php endif; ?>

        <?php include('includes/footer.php'); ?>
    </body>
</html>
