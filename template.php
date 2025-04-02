<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body class="corps">
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Bilan des Tickets de caisse';
            include("includes/header.php");
            $page = 3;
            include("includes/nav.php");
            if($_SESSION['admin'] >= 1){
        ?>

<!-- Corps de page -->

        <?php
        }else{
            echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
        }
        include('includes/footer.php');
        ?>
    </body>
</html>