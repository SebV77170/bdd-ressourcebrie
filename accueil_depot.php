<?php
require('actions/users/securityAction.php');
require('actions/objets/insertObjetDsDb.php');
?>


<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body>
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
            <ul>
                <a href="depot.php"><li id="bleu">Faire un dépot rapide (peu d'objet)</li></a>
                <a href="accueil_depot.php"><li id="vert">Débuter ou reprendre un dépot</li></a>
                
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