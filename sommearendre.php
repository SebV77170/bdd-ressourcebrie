 <?php
require('actions/users/securityAction.php');
require('actions/objets/moyenDePaiementAction.php');

?>

<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body class="corps">
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Somme à rendre';
            include("includes/header.php");
            $page = 2;
            include("includes/nav.php");
            ?>
            
            
            <?php
            if($_SESSION['admin'] >= 1){
            ?>
        
        
        <?php
        if(isset($_GET['prix'])){

        ?>
        
        <h2 style='text-align: center;'>Vous devez rendre <?= $_GET['prix']?> € au client, merci.</h2>
        
        <?php
        }else{
            echo 'Le montant indiqué dans la page précédente n\'est pas correct';
        }
        ?>
        
        <a href='ticketdecaisseapresvente.php?id_ticket=<?=$_GET['id_ticket']?>' class='stdbouton'>OK</a>

        
        
        
        <?php
            }else{
                echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
            }
            include('includes/footer.php');
            ?>
    </body>
</html>
 
 