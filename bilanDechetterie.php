<?php
require('actions/users/securityAction.php');
require('actions/db.php');
require('app/bootstrap.php');

// Année sélectionnée ou année en cours par défaut
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Suppression d'un apport (réservée admin niveau 2)
if (isset($_GET['delete_id']) && $_SESSION['admin'] >= 2) {
    $deleteId = intval($_GET['delete_id']);

    $deleteStmt = $db->prepare('DELETE FROM dechet WHERE id = ?');
    $deleteStmt->execute([$deleteId]);

    header('Location: bilanDechetterie.php?year=' . $selectedYear);
    exit;
}

// Insertion d'un nouvel apport
if (isset($_POST['validate'])) {
    if (!empty($_POST['date']) && !empty($_POST['poids'])) {
        $poids = $_POST['poids'];
        $date = convertDateFRenDateUS($_POST['date']);

        $insertObjet = $db->prepare('INSERT INTO dechet(date, poids) VALUES(?, ?)');
        $insertObjet->execute([$date, $poids]);

        header('Location: bilanDechetterie.php?year=' . $selectedYear);
        exit;
    } else {
        $message = "Veuillez remplir tous les champs svp, merci.";
    }
}

// Récupération des données filtrées par année
$sql = 'SELECT id, date, poids 
        FROM dechet
        WHERE YEAR(date) = :year
        ORDER BY date DESC';

$sth = $db->prepare($sql);
$sth->execute(['year' => $selectedYear]);
$results = $sth->fetchAll();

?>

<!DOCTYPE HTML>
<html lang="fr-FR">
    <?php include("includes/head.php"); ?>
    <body class="corps">
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Bilan des apports en dechetterie';
            include("includes/header.php");
            $page = 3;
            include("includes/nav.php");
        ?>

        <?php if ($_SESSION['admin'] >= 1): ?>

            <form method="POST" action="bilanDechetterie.php?year=<?= $selectedYear ?>">
                <fieldset class="jeuchamp">
                    <h2>Ajouter un apport</h2>

                    <label class="champ" for="date">Date</label>
                    <input type="text" name="date" id="date" placeholder="dd-mm-YYYY">

                    <label class="champ" for="poids">Poids en kilogramme</label>
                    <input type="text" name="poids" id="poids">

                    <input type="submit" class="input inputsubmit" name="validate" value="Insérer">
                </fieldset>
            </form>

            <?php if (isset($message)): ?>
                <h1 style="text-align:center;"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></h1>
            <?php endif; ?>

            <div style="text-align:center; margin:20px;">
                <form method="GET">
                    <label for="year">Année :</label>
                    <select name="year" id="year">
                        <?php for ($y = date('Y') - 5; $y <= date('Y') + 1; $y++): ?>
                            <option value="<?= $y ?>" <?= ($y == $selectedYear) ? 'selected' : '' ?>>
                                <?= $y ?>
                            </option>
                        <?php endfor; ?>
                    </select>

                    <button type="submit">Filtrer</button>
                </form>
            </div>

            <h2 style="text-align:center;">Données pour l'année <?= $selectedYear ?></h2>

            <?php
            $totalPoids = 0;
            foreach ($results as $row) {
                $totalPoids += $row['poids'];
            }
            ?>

            <p style="text-align:center; font-weight:bold; font-size:18px;">
                Total pour l'année : <?= number_format($totalPoids, 0, ',', ' ') ?> kg
            </p>

            <table class="tableau">
                <tr class="ligne">
                    <th class="cellule_tete">Date</th>
                    <th class="cellule_tete">Poids</th>
                    <?php if ($_SESSION['admin'] >= 2): ?>
                        <th class="cellule_tete">Action</th>
                    <?php endif; ?>
                </tr>

                <?php foreach ($results as $row): ?>
                    <?php
                        $id = $row['id'];
                        $date = $row['date'];
                        $poids = $row['poids'];
                        $dateAffichee = date('d/m/Y', strtotime($date));
                    ?>
                    <tr class="ligne">
                        <td class="colonne"><?= htmlspecialchars($dateAffichee, ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="colonne"><?= htmlspecialchars($poids, ENT_QUOTES, 'UTF-8') ?> kg</td>

                        <?php if ($_SESSION['admin'] >= 2): ?>
                            <td class="colonne">
                                <a href="bilanDechetterie.php?year=<?= $selectedYear ?>&delete_id=<?= $id ?>"
                                   onclick="return confirm('Voulez-vous vraiment supprimer cette ligne ?');">
                                    Supprimer
                                </a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </table>

        <?php else: ?>
            Vous n'êtes pas administrateur, veuillez contacter le webmaster svp
        <?php endif; ?>

        <?php include('includes/footer.php'); ?>
    </body>
</html>