<?php
require('actions/users/securityAction.php');
require('actions/objets/moyenDePaiementCarteAction.php');

?>

<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body>
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Insérez le numéro du chèque';
            include("includes/header.php");
            $page = 2;
            include("includes/nav.php");
            ?>
            
            
            <?php
            if($_SESSION['admin'] >= 1){
            ?>
        
        <h2 style='text-align: center;'>Prix total = <?= $_GET['prix']?> €</h2>

        <form method="post">
                
                <fieldset>
                    
                    <label for="paiement">Moyen de Paiement : </label>
                    <select id="paiement" name="paiement">
                        <option value="carte">Carte</option>
                    </select>
                    
                    <label for="numero">Numéro du transaction : </label>
                    <input type="text" name="numerotransac">
                    
                
                </fieldset>
                
                <?php if(isset($message)){
                ?>
                <p style='text-align: center; color: red;'>ATTENTION : <?=$message?></p>
                <?php
                }
                ?>
            
                <input type="submit" name="validate" value="Valider">
                <a href="moyenDePaiement.php?prix=<?=$_GET['prix']?>&nbrObjet=<?=$_GET['nbrObjet']?>&id_temp_vente=<?=$_GET['id_temp_vente']?>" class="stdbouton">Retour</a>
                
        </form>
        
        
        <?php
            }else{
                echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
            }
            include('includes/footer.php');
            ?>
    </body>
</html>