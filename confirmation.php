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
            $titre = 'Accueil Vente';
            include("includes/header.php");
            $page = 2;
            include("includes/nav.php");
            
            ?>
            
            
            <?php
            if($_SESSION['admin'] >= 1){
            ?>
          
          
          <p style="text-align: center;">Voulez-vous vraiment supprimer cette vente ?</p>
          
          
            <a class="stdbouton" href="actions/objets/supprVente.php?uuid_ticket=<?=$_GET['uuid_ticket']?>">Supprimer</a>
                            
                            
        
        <?php
            }else{
                echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
            }
            include('includes/footer.php');
            ?>
            
    </body>
</html>