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
            $titre = 'BDD 2.0';
            include("includes/header.php");
            $page = 1;
            include("includes/nav.php");
            ?>
            
            
            <?php
            if($_SESSION['admin'] >= 1){
            ?>
        
        

        <h1>Bienvenue <?php echo $_SESSION['prenom']; ?></h1>
        
        <div class="doc">
            <ul>
                <a href="depot.php"><li id="bleu">Faire un dépot</li></a>
                <!--<a href="accueil_depot.php"><li id="vert">Débuter ou reprendre un dépot</li></a>-->
                <a href="accueil_vente.php"><li id="bleu">Débuter ou reprendre une vente</li></a>
                <a href="bilan.php"><li id="bleu">Regarder les bilans</li></a>
                <a href="reparation.php"><li id="bleu">Liste des réparations à faire</li></a>
                <a href="cloturejournee.php"><li id="bleu">Cloturer la journée</li></a>
                <a href="actions/users/logoutAction.php"><li id="bleu">Se déconnecter</li></a>
                
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