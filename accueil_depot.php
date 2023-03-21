<?php
require('actions/users/securityAction.php');
require('actions/objets/insertObjetDsDb.php');
?>


<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body class="corps">
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Collecte';
            include("includes/header.php");
            $page = 1;
            include("includes/nav.php");
            ?>
            
            
            <?php
            if($_SESSION['admin'] >= 1){
            ?>
        
        

        
        
        <div class="doc">
            <ul class="doc_ul">
                <a class="doc_lien" href="depot.php"><li class="doc_li" id="bleu">Faire un dépot rapide (peu d'objet)</li></a>
                <a class="doc_lien" href="accueil_depot.php"><li class="doc_li" id="vert">Débuter ou reprendre un dépot</li></a>
                
            </ul>
        </div>
        
        
        
        <?php
            }else{
                echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
            }
            include('includes/footer.php');
            ?>
    </body>
</html>