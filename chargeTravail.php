<?php
require('actions/users/securityAction.php');
require('actions/db.php');
require('actions/comptabilite/bilanHelpers.php');

$annee = isset($_GET['annee']) ? (int) $_GET['annee'] : (int) date('Y');
if ($annee < 2000 || $annee > 2100) {
    $annee = (int) date('Y');
}

$yearsStmt = $db->query("SELECT DISTINCT YEAR(start) AS annee FROM events WHERE start IS NOT NULL ORDER BY annee DESC");
$availableYears = array_values(array_filter(
    array_map(static fn($row) => isset($row['annee']) ? (int) $row['annee'] : null, $yearsStmt->fetchAll(PDO::FETCH_ASSOC)),
    static fn($year) => $year !== null && $year >= 2000 && $year <= 2100
));

if ($availableYears === []) {
    $availableYears = [$annee];
} elseif (!in_array($annee, $availableYears, true)) {
    $annee = $availableYears[0];
}

$chargeTravail = getChargeTravailStatsForYear($db, $annee);
?>

<!DOCTYPE HTML>
<html lang="fr-FR">
    <?php include('includes/head.php'); ?>
    <body class="corps">
        <?php
            $lineheight = 'uneligne';
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Charge de travail';
            include('includes/header.php');
            $page = 3;
            include('includes/nav.php');
        ?>

        <?php if ($_SESSION['admin'] >= 1) { ?>
            <div class="doc">
                <form method="get" class="jeuchamp" id="filtre-annee-form" style="display:flex;gap:.75rem;align-items:center;justify-content:center;flex-wrap:wrap;">
                    <label for="annee" class="champ">Année :</label>
                    <select id="annee" name="annee" class="input" onchange="this.form.submit()">
                        <?php foreach ($availableYears as $yearOption) { ?>
                            <option value="<?= $yearOption ?>" <?= $yearOption === $annee ? 'selected' : '' ?>><?= $yearOption ?></option>
                        <?php } ?>
                    </select>
                </form>
            </div>

            <div class="container">
                <div class="resume">
                    <div class="cadre">
                        <h2>Charge de travail <?= htmlspecialchars((string) $annee, ENT_QUOTES, 'UTF-8') ?></h2>
                        <p><strong>Période :</strong> du <?= date('d/m/Y', strtotime($chargeTravail['period_start'])) ?> au <?= date('d/m/Y', strtotime($chargeTravail['period_end'])) ?></p>
                        <p><strong>Jours totaux d'activité :</strong> <?= $chargeTravail['total_activity_days'] ?></p>
                        <p><strong>Jours de vente :</strong> <?= $chargeTravail['total_sales_days'] ?></p>
                        <p><strong>Jours de collecte :</strong> <?= $chargeTravail['total_collection_days'] ?></p>
                        <p><strong>Heures de bénévolat :</strong> <?= $chargeTravail['benevolat_hours'] ?> h</p>
                        <p><strong>Heures des salariées - total (status 1 et 2) :</strong> <?= $chargeTravail['employee_hours'] ?> h</p>
                        <p><strong>Heures des coordinatrices (status 1) :</strong> <?= $chargeTravail['employee_hours_coordinatrice'] ?> h</p>
                        <p><strong>Heures des agents d'entretien (status 2) :</strong> <?= $chargeTravail['employee_hours_agent_entretien'] ?> h</p>
                    </div>
                </div>
            </div>
        <?php } else {
            echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
        }

        include('includes/footer.php');
        ?>
    </body>
</html>
