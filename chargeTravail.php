<?php
require('actions/users/securityAction.php');
require('actions/db.php');
require('actions/comptabilite/bilanHelpers.php');

$annee = isset($_GET['annee']) ? (int) $_GET['annee'] : (int) date('Y');
if ($annee < 2000 || $annee > 2100) {
    $annee = (int) date('Y');
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
                <ul class="doc_ul">
                    <a class="doc_lien" href="bilan.php"><li class="doc_li" id="bleu">Retour aux bilans</li></a>
                </ul>
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
                        <p><strong>Heures des salariés :</strong> <?= $chargeTravail['employee_hours'] ?> h</p>
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
