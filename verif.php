<?php
require('actions/users/securityAction.php');
require('actions/objets/currencyToDecimalFct.php');
require('actions/objets/moyenDePaiementAction.php');
require('actions/objets/moyenDePaiementCarteAction.php');
require('actions/objets/moyenDePaiementChequeAction.php');
require('actions/objets/moyenDePaiementMixteAction.php');

?>

<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body class="corps">
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Insérez le moyen de paiement';
            include("includes/header.php");
            $page = 2;
            include("includes/nav.php");
            ?>
            
            
            <?php
            if($_SESSION['admin'] >= 1){
            ?>

        <h2 style='text-align: center;'>Etes-vous certains de vouloir valider cette vente de <?= $_GET['prix']?> € en <?=$_GET['mp']?> ?</h2>

        <form method="post">
                
                <?php if(($_GET['mp'])=='espèces'):?>
                <input type="submit" class="input inputsubmit" name="validateespece" value="Valider">

                <?php elseif(($_GET['mp'])=='carte'):?>
                <input type="submit" class="input inputsubmit" name="validatecarte" value="Valider">

                <?php elseif(($_GET['mp'])=='chèque'):?>
                <input type="submit" class="input inputsubmit" name="validatecheque" value="Valider">

                <?php elseif(($_GET['mp'])=='mixte'):?>
                <h2 style='text-align: center;'>Comment répartissez-vous les dépenses ?</h2>
                <fieldset class="jeuchamp">
                    <label class="champ" for="espece">Montant en espèce : </label>
                    <input class="input"type="text" name="espece">
                    
                    <label class="champ" for="carte">Montant en carte : </label>
                    <input class="input"type="text" name="carte">
                    
                    <label class="champ" for="cheque">Montant en chèque : </label>
                    <input class="input"type="text" name="cheque">
                    <input type="submit" class="input inputsubmit" name="validatemixte" value="Valider">
                </fieldset>
                <?php endif;?>

                <?php if(isset($message)):?>
                <div class="alert alert-danger" role="alert">
                <?=$message?>
                </div>
                <?php endif;?>

            

       
                <?php
                if(isset($_GET['id_modif'])):
                ?>
                    <a href="objetsVendus.php?id_temp_vente=<?=$_GET['id_temp_vente']?>&id_modif=<?=$_GET['id_modif']?>&modif=<?=$_GET['modif']?>#tc" class="stdbouton">Retour</a>
                <?php
                else:
                ?>
                    <a href="objetsVendus.php?id_temp_vente=<?=$_GET['id_temp_vente']?>&modif=<?=$_GET['modif']?>#tc" class="stdbouton">Retour</a>
                <?php
                endif;
                ?>
        </form>
        
       
        
        
        <?php
            }else{
                echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
            }
            include('includes/footer.php');
            ?>
    </body>
</html>