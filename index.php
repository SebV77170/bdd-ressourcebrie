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
            $titre = 'BDD 2.0';
            include("includes/header.php");
            $page = 1;
            include("includes/nav.php");
            ?>
            
            
            <?php
            if($_SESSION['admin'] >= 1){
            ?>
        
        

        <h1 class="gros_titre">Bienvenue <?php echo $_SESSION['prenom']; ?></h1>
        
        <div class="doc">
            <ul class="doc_ul">
                <a class="doc_lien" href="depot.php"><li class="doc_li" id="bleu">Faire un dépot</li></a>
                <!--<a class="doc_lien" href="accueil_depot.php"><li class="doc_li" id="vert">Débuter ou reprendre un dépot</li></a>-->
                <a class="doc_lien" href="accueil_vente.php"><li class="doc_li" id="bleu">Débuter ou reprendre une vente</li></a>
                <a class="doc_lien" href="bilan.php"><li class="doc_li" id="bleu">Regarder les bilans</li></a>
                <a class="doc_lien" href="reparation.php"><li class="doc_li" id="bleu">Liste des réparations à faire</li></a>
                <a class="doc_lien" href="cloturejournee.php"><li class="doc_li" id="bleu">Cloturer la journée</li></a>
                <a class="doc_lien" href="actions/users/logoutAction.php"><li class="doc_li" id="bleu">Se déconnecter</li></a>
                
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