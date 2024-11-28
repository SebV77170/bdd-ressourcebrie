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
            $titre = 'Bilan Oeuvres';
            include("includes/header.php");
            $page = 1;
            include("includes/nav.php");
            ?>
            
            
            <?php
            if($_SESSION['admin'] >= 1){
            ?>



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