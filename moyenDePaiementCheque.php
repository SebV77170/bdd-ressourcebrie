<?php
require('actions/users/securityAction.php');
require('actions/objets/moyenDePaiementChequeAction.php');

?>

<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body class="corps">
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
                
                <fieldset class="jeuchamp">
                    
                    <label class="champ" for="paiement">Moyen de Paiement : </label>
                    <select id="paiement" name="paiement">
                        <option value="cheque">Chèque</option>
                    </select>
                    
                    <label class="champ" for="numero">Numéro du chèque : </label>
                    <input class="input"type="text" name="numero">
                    
                    <label class="champ" for="banque">Banque : </label>
                    <input class="input"type="text" name="banque">

                  
                
                </fieldset>
                
                <?php if(isset($message)){
                ?>
                <p style='text-align: center; color: red;'>ATTENTION : <?=$message?></p>
                <?php
                }
                ?>
            
                <input type="submit" class="input inputsubmit" name="validate" value="Valider">
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