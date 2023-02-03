<?php
require('actions/users/securityAction.php');
?>


<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body>
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Erreur';
            include("includes/header.php");
            $page = 0;
            include("includes/nav.php");
            ?>
            
            
            <?php
            if($_SESSION['admin'] >= 1){
                if(isset($_GET['message'])):
            ?>
        <h1><?=$_GET['message']?></h1>
        
        <?php
                else:
                    echo '<h1>Une erreur s\'est produite</h1>';
                endif;
            }else{
                echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
            }
            include('includes/footer.php');
            ?>
    </body>
</html>