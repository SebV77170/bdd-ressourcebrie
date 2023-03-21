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
            $titre = 'Tickets de caisse';
            include("includes/header.php");
            $page = 3;
            include("includes/nav.php");
            ?>
            
            
            <?php
            if($_SESSION['admin'] >= 1){
            ?>
        
        <?php
            $lien = $_GET['url'];
        ?>
        
        <div id="ticket">
            <p class="paraph"><iframe src="<?php echo $lien;?>" frameborder=0 width=800 height=300></iframe></p>
        </div>
        
        <a href='bilanticketDeCaisse.php' class='stdbouton'>Retour</a>

        
        
        <?php
            }else{
                echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
            }
            include('includes/footer.php');
            ?>
    </body>
</html>