<?php
require('actions/users/securityAction.php');
?>

<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body class="corps">
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Administration';
            include("includes/header.php");
            $page = 5;
            include("includes/nav.php");
        ?>

        <?php if($_SESSION['admin'] >= 1): ?>
            <div class="doc">
                <ul class="doc_ul">
                    <a class="doc_lien" href="comptabilite.php"><li class="doc_li" id="bleu">Comptabilité</li></a>
                </ul>
            </div>
        <?php else: ?>
            <p>Vous n'êtes pas administrateur, veuillez contacter le webmaster svp.</p>
        <?php endif; ?>

        <?php include('includes/footer.php'); ?>
    </body>
</html>
