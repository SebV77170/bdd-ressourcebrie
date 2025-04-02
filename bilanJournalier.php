<?php
require('actions/users/securityAction.php');
require('actions/db.php');

// Récupération du mois et de l'année sélectionnés ou par défaut le mois et l'année en cours
$selectedMonth = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Préparation de la requête SQL pour filtrer par mois et année
$sql = 'SELECT date, timestamp, nombre_vente, poids, prix_total, prix_total_espece, prix_total_cheque, prix_total_carte, prix_total_virement 
        FROM bilan 
        WHERE MONTH(STR_TO_DATE(date, "%d/%m/%Y")) = :month AND YEAR(STR_TO_DATE(date, "%d/%m/%Y")) = :year 
        ORDER BY timestamp DESC';
$sth = $db->prepare($sql);
$sth->execute(['month' => $selectedMonth, 'year' => $selectedYear]);
$results = $sth->fetchAll();

// Fonction pour obtenir le nom du mois
function getMonthName($month) {
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    $formatter->setPattern('MMMM');
    return ucfirst($formatter->format(mktime(0, 0, 0, $month, 1)));
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
            $titre = 'Bilans journaliers';
            include("includes/header.php");
            $page = 3;
            include("includes/nav.php");
        ?>
        
        <?php if ($_SESSION['admin'] >= 1): ?>
        
        <!-- Formulaire de sélection du mois et de l'année -->
        <div style="text-align: center;">
            <form method="GET" class="formulaire-mois">
                <label for="month">Mois :</label>
                <select name="month" id="month">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m == $selectedMonth ? 'selected' : '' ?>>
                        <?= getMonthName($m) ?>
                    </option>
                    <?php endfor; ?>
                </select>
                
                <label for="year">Année :</label>
                <select name="year" id="year">
                    <?php for ($y = date('Y') - 5; $y <= date('Y') + 5; $y++): ?>
                    <option value="<?= $y ?>" <?= $y == $selectedYear ? 'selected' : '' ?>>
                        <?= $y ?>
                    </option>
                    <?php endfor; ?>
                </select>
                
                <button type="submit">Afficher</button>
            </form>
        </div>

        <!-- Tableau récapitulatif des totaux avec un style distinct -->
        <?php
            $totals = [
            'nombre_vente' => 0,
            'poids' => 0,
            'prix_total' => 0,
            'prix_total_espece' => 0,
            'prix_total_cheque' => 0,
            'prix_total_carte' => 0,
            'prix_total_virement' => 0,
            ];

            foreach ($results as $row) {
            $totals['nombre_vente'] += $row['nombre_vente'];
            $totals['poids'] += $row['poids'];
            $totals['prix_total'] += $row['prix_total'];
            $totals['prix_total_espece'] += $row['prix_total_espece'];
            $totals['prix_total_cheque'] += $row['prix_total_cheque'];
            $totals['prix_total_carte'] += $row['prix_total_carte'];
            $totals['prix_total_virement'] += $row['prix_total_virement'];
            }

            // Conversion des valeurs en unités lisibles
            $totals['poids'] = round($totals['poids'] / 1000000, 1); // Conversion des grammes en tonnes (T) et arrondi à 1 chiffre après la virgule
            $totals['prix_total'] /= 100;
            $totals['prix_total_espece'] /= 100;
            $totals['prix_total_cheque'] /= 100;
            $totals['prix_total_carte'] /= 100;
            $totals['prix_total_virement'] /= 100;
        ?>
        <div style="text-align: center; margin: 30px; padding: 20px; background-color: #f9f9f9; border: 2px solid #007BFF; border-radius: 10px;">
            <h3 style="color: #007BFF;">Récapitulatif du mois</h3>
            <table class="tableau" style="width: 80%; margin: 0 auto; border-collapse: collapse; background-color: #ffffff;">
            <tr class="ligne" style="background-color: #007BFF; color: #ffffff;">
            <th class="cellule_tete" style="padding: 10px; border: 1px solid #007BFF;">Total Nombre de Ventes</th>
            <th class="cellule_tete" style="padding: 10px; border: 1px solid #007BFF;">Total Poids (T)</th>
            <th class="cellule_tete" style="padding: 10px; border: 1px solid #007BFF;">Total Recette (€)</th>
            <th class="cellule_tete" style="padding: 10px; border: 1px solid #007BFF;">Total Espèces (€)</th>
            <th class="cellule_tete" style="padding: 10px; border: 1px solid #007BFF;">Total Chèques (€)</th>
            <th class="cellule_tete" style="padding: 10px; border: 1px solid #007BFF;">Total Carte (€)</th>
            <th class="cellule_tete" style="padding: 10px; border: 1px solid #007BFF;">Total Virement (€)</th>
            </tr>
            <tr class="ligne" style="text-align: center;">
            <td class="colonne" style="padding: 10px; border: 1px solid #007BFF;"><?= $totals['nombre_vente'] ?></td>
            <td class="colonne" style="padding: 10px; border: 1px solid #007BFF;"><?= $totals['poids'] ?> T</td>
            <td class="colonne" style="padding: 10px; border: 1px solid #007BFF;"><?= $totals['prix_total'] ?> €</td>
            <td class="colonne" style="padding: 10px; border: 1px solid #007BFF;"><?= $totals['prix_total_espece'] ?> €</td>
            <td class="colonne" style="padding: 10px; border: 1px solid #007BFF;"><?= $totals['prix_total_cheque'] ?> €</td>
            <td class="colonne" style="padding: 10px; border: 1px solid #007BFF;"><?= $totals['prix_total_carte'] ?> €</td>
            <td class="colonne" style="padding: 10px; border: 1px solid #007BFF;"><?= $totals['prix_total_virement'] ?> €</td>
            </tr>
            </table>
        </div>
        
        <table class="tableau">
            <tr class="ligne">
                <th class="cellule_tete">Date</th>
                <th class="cellule_tete">Nombre de vente</th>
                <th class="cellule_tete">Poids</th>
                <th class="cellule_tete">Recette Totale</th>
                <th class="cellule_tete">Espèces</th>
                <th class="cellule_tete">Chèques</th>
                <th class="cellule_tete">Carte</th>
                <th class="cellule_tete">Virement</th>
            </tr>
        
        <?php foreach ($results as list($date, $timestamp, $nombre, $poids, $total, $espece, $cheque, $carte, $virement)): ?>
            <?php
                $poids = $poids / 1000;
                $total = $total / 100;
                $espece = $espece / 100;
                $cheque = $cheque / 100;
                $carte = $carte / 100;
                $virement = $virement / 100;
                $format_us = implode('-', array_reverse(explode('/', $date)));
            ?>
            <tr class="ligne">
                <td class="colonne"><?= $date ?></td>
                <td class="colonne"><?= $nombre ?></td>
                <td class="colonne"><?= $poids ?> kg</td>
                <td class="colonne"><?= $total ?> €</td>
                <td class="colonne"><?= $espece ?> €</td>
                <td class="colonne"><?= $cheque ?> €</td>
                <td class="colonne"><?= $carte ?> €</td>
                <td class="colonne"><?= $virement ?> €</td>
                <td class="colonne"><a href="actions/objets/update_db_bilan_manuel.php?date=<?= $date ?>">Mise à jour</a></td>
                <td class="colonne"><a href="actions/objets/compiltxt.php?date=<?= $format_us ?>">Compiler les tickets du jour</a></td>
            </tr>
        <?php endforeach; ?>
        </table>
        
        <?php else: ?>
            <p>Vous n'êtes pas administrateur, veuillez contacter le webmaster svp.</p>
        <?php endif; ?>
        
        <?php include('includes/footer.php'); ?>
    </body>
</html>