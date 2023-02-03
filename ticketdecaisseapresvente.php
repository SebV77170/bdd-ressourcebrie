<?php
require('actions/users/securityAction.php');
require('actions/objets/getTicket.php');
require('actions/objets/envoieTicketAction.php');
?>

<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body>
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
        
        
        <div id="ticket">
            <p><iframe src="<?php echo $lien;?>" frameborder=0 width=800 height=300></iframe></p>
        </div>
        
         <form method="post">
                
                <fieldset>
            
                    <label for="mail">Email du client : </label>
                    <input type="text" name="mail">
                    
                    <label for="ok">Inscription mailing (cochez si le client est ok)</label>
                    <input type="checkbox" name="ok">
            
                
                </fieldset>
                
                <?php if(isset($message)){
                ?>
                <p style='text-align: center; color: red;'>ATTENTION : <?=$message?></p>
                <?php
                }
                ?>
            
                <input type="submit" name="validate" value="Envoyer">
                
                
            </form>
        
        <a href='accueil_vente.php' class='stdbouton'>Retour</a>

        
        
        <?php
            }else{
                echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
            }
            include('includes/footer.php');
            ?>
    </body>
</html>