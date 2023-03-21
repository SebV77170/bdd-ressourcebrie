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
            $titre = 'Insérez le moyen de paiement';
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
                        <option value="espece">Espèce</option>
                        <option value="carte">Carte</option>
                        <option value="cheque">Chèque</option>
                        <option value="mixte">Mixte</option>
                    </select>
                
                </fieldset>
                
                <h2 style='text-align: center;'>Le champ ci-dessous n'est plus obligatoire, il vous aide à rendre la monnaie si besoin.</h2>

                <fieldset class="jeuchamp">
            
                    <label class="champ" for="client">Montant donné par le client : (remplir seulement si paiement espèce) </label>
                    <input class="input"type="text" name="client">
                
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
                    <a href="objetsVendus.php?id_temp_vente=<?=$_GET['id_temp_vente']?>&id_modif=<?=$_GET['id_modif']?>&modif=<?=$_GET['modif']?>" class="stdbouton">Retour</a>
                <?php
                else:
                ?>
                    <a href="objetsVendus.php?id_temp_vente=<?=$_GET['id_temp_vente']?>&modif=<?=$_GET['modif']?>" class="stdbouton">Retour</a>
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