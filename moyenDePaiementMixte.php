<?php
require('actions/users/securityAction.php');
require('actions/objets/moyenDePaiementMixteAction.php');

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
                        <option value="mixte">Mixte</option>
                    </select>
                    
                    <label for="espece">Montant en espèce : </label>
                    <input type="text" name="espece">
                    
                    <label for="carte">Montant en carte : </label>
                    <input type="text" name="carte">
                    
                    <label for="transaction">Numéro de transaction (OBLIGATOIRE SI PAIEMENT EN CARTE):  </label>
                    <input type="text" name="transaction">
                    
                    <label for="cheque">Montant en chèque : </label>
                    <input type="text" name="cheque">
                    
                    <label for="numero">Numéro du chèque : </label>
                    <input type="text" name="numero">
                    
                    <label for="banque">Banque : </label>
                    <input type="text" name="banque">
                
                </fieldset>
                
                <?php if(isset($message)){
                ?>
                <p style='text-align: center; color: red;'>ATTENTION : <?=$message?></p>
                <?php
                }
                ?>
            
                <input type="submit" name="validate" value="Valider">
                <?php
                if(isset($_GET['id_modif'])):
                ?>
                    <a href="moyenDePaiement.php?prix=<?=$_GET['prix']?>&nbrObjet=<?=$_GET['nbrObjet']?>&id_temp_vente=<?=$_GET['id_temp_vente']?>&id_modif=<?=$_GET['id_modif']?>&modif=<?=$_GET['modif']?>" class="stdbouton">Retour</a>
                <?php
                else:
                ?>
                    <a href="moyenDePaiement.php?prix=<?=$_GET['prix']?>&nbrObjet=<?=$_GET['nbrObjet']?>&id_temp_vente=<?=$_GET['id_temp_vente']?>&modif=<?=$_GET['modif']?>" class="stdbouton">Retour</a>
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