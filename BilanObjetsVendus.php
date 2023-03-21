<?php
require('actions/users/securityAction.php');
require('actions/objets/recupDBvendus.php');

require('actions/objets/getSommePrixVendus.php');


?>

<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body class="corps">
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Encaissement';
            include("includes/header.php");
            $page = 3;
            include("includes/nav.php");
            ?>
            
            
            <?php
            if($_SESSION['admin'] >= 1){
            ?>
            
             
        
        
        <?php if(isset($message)){
            echo '<p style="text-align: center;">'.$message.'</p>';
        }
        ?>
        
        <p style="text-align: center;"> Prix Total d'objets <b>vendus</b> toute catégorie confondue : <?php
        $prix_total_obj_collecte_kg = $prix_total_obj_collecte['prix_total']/100;
        echo $prix_total_obj_collecte_kg.' €';
        ?> </p>
        
        <form method="get">
                
                <fieldset class="jeuchamp">
            
                    <label class="champ" for="tri">Trier par : </label>
                    <select id="tri" name="tri">
                        <option value="nom">Nom</option>
                        <option value="categorie">Catégorie</option>
                        <option value="prix">Prix</option>
                        <option value="timestamp">Date d'ajout</option>
                    </select>
                
                </fieldset>
            
                <input type="submit" class="input inputsubmit" name="validate" value="Trier">
        </form>
        
        <table class="tableau">
            <tr class="ligne">
                <th class="cellule_tete">Categories</th>
                <th class="cellule_tete">Prix total</th>
                <th class="cellule_tete">Pourcentage</th>
            </tr>
            
        <?php
        
        foreach($LesSommes as list($categorie, $prix_total_par_cat)){
            $prix_total_par_cat_euro = $prix_total_par_cat/100;
            $pourcentage = round((($prix_total_par_cat_euro * 100) / $prix_total_obj_collecte_kg),1);
            echo '<tr class="ligne">
                        
                            <td class="colonne">'.$categorie.'</td>
                            <td class="colonne">'.$prix_total_par_cat_euro.' €</td>
                            <td class="colonne">'.$pourcentage.'%</td>         
                          </tr>'  ;
        }
        ?>
           
            
        </table>
        
        
        <table class="tableau">
            <tr class="ligne">
    
                <th class="cellule_tete">Nom</th>
                <th class="cellule_tete">Nom du vendeur</th>
                <th class="cellule_tete">Catégorie</th>
                <th class="cellule_tete">Sous-Catégorie</th>
                <th class="cellule_tete">Date de vente</th>
                <th class="cellule_tete">Prix en €</th>
            </tr>
        
        <?php foreach($getObjets as list($nom, $nom_vendeur, $type, $souscat, $date_vente, $timestamp, $prix)){
            
                        $prixeuro = $prix/100;
        
                        echo '<tr class="ligne">
                        
                            
                            <td class="colonne">'.$nom.'</td>
                            <td class="colonne">'.$nom_vendeur.'</td>
                            <td class="colonne">'.$type.'</td>
                            <td class="colonne">'.$souscat.'</td>
                            <td class="colonne">'.$date_vente.'</td>
                            <td class="colonne">'.$prixeuro.'€</td>
                            
                            
                          </tr>'  ;
        }
        ?>
        </table>
        
        <?php
            }else{
                echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
            }
            include('includes/footer.php');
            ?>
    </body>
</html>