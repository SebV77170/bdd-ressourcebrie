<?php
require('actions/users/securityAction.php');
require('actions/db.php'); // ← pour récupérer la liste des années

?>


<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body class="corps">
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Bilan Oeuvres';
            include("includes/header.php");
            $page = 1;
            include("includes/nav.php");
            ?>
            
            
            <?php
            if($_SESSION['admin'] >= 1){
            ?>

    <?php
// Récupère dynamiquement les années présentes dans la table (date_achat est au format ISO texte "YYYY-...").
$yearsStmt = $db->query("
    SELECT DISTINCT LEFT(`date_achat`, 4) AS y
    FROM `objets_vendus`
    WHERE `date_achat` IS NOT NULL AND `date_achat` <> ''
      AND LEFT(`date_achat`,4) REGEXP '^[0-9]{4}$'
    ORDER BY y DESC
");
$availableYears = array_column($yearsStmt->fetchAll(PDO::FETCH_ASSOC), 'y');

// Année sélectionnée (GET)
$selectedYear = null;
if (isset($_GET['annee']) && $_GET['annee'] !== '') {
    $candidate = filter_var($_GET['annee'], FILTER_VALIDATE_INT);
    if ($candidate !== false && in_array((string)$candidate, $availableYears, true)) {
        $selectedYear = (string)$candidate;
    }
}
?>

<!-- Formulaire: passe ?annee=YYYY en GET (auto-submit au changement) -->
<div class="container" style="max-width:720px;margin:16px auto 0;">
  <form method="get" class="jeuchamp" id="filtre-annee-form" style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;">
    <label for="annee" class="champ" style="min-width:160px;">Filtrer par année :</label>
    <select id="annee" name="annee" class="input" onchange="this.form.submit()">
      <option value="">Toutes les années</option>
      <?php foreach ($availableYears as $y): ?>
        <option value="<?= htmlspecialchars($y, ENT_QUOTES) ?>"
          <?= ($selectedYear === (string)$y) ? 'selected' : '' ?>>
          <?= htmlspecialchars($y, ENT_QUOTES) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <noscript><button type="submit" class="input inputsubmit">Appliquer</button></noscript>
  </form>
</div>



    <div class="container">
        <table class="table table-striped mt-3">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prix Total</th>
                </tr>
            </thead>
            <tbody>
           <?php require('actions/objets/prixoeuvreAction.php'); ?>
           </tbody>
        </table>
    </div>

        
         
        <?php
            }else{
                echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
            }
            include('includes/footer.php');
            ?>
    </body>
</html>