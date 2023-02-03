<?php
require('actions/users/securityAction.php');
require('actions/objets/recupDBvendus.php');

require('actions/objets/getSommePrixVendus.php');


?>

<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body>
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
                
                <fieldset>
            
                    <label for="tri">Trier par : </label>
                    <select id="tri" name="tri">
                        <option value="nom">Nom</option>
                        <option value="categorie">Catégorie</option>
                        <option value="prix">Prix</option>
                        <option value="timestamp">Date d'ajout</option>
                    </select>
                
                </fieldset>
            
                <input type="submit" name="validate" value="Trier">
        </form>
        
        <table>
            <tr>
                <th>Categories</th>
                <th>Prix total</th>
                <th>Pourcentage</th>
            </tr>
            
        <?php
        
        foreach($LesSommes as list($categorie, $prix_total_par_cat)){
            $prix_total_par_cat_euro = $prix_total_par_cat/100;
            $pourcentage = round((($prix_total_par_cat_euro * 100) / $prix_total_obj_collecte_kg),1);
            echo '<tr>
                        
                            <td>'.$categorie.'</td>
                            <td>'.$prix_total_par_cat_euro.' €</td>
                            <td>'.$pourcentage.'%</td>         
                          </tr>'  ;
        }
        ?>
           
            
        </table>
        
        
        <table>
            <tr>
    
                <th>Nom</th>
                <th>Nom du vendeur</th>
                <th>Catégorie</th>
                <th>Sous-Catégorie</th>
                <th>Date de vente</th>
                <th>Prix en €</th>
            </tr>
        
        <?php foreach($getObjets as list($nom, $nom_vendeur, $type, $souscat, $date_vente, $timestamp, $prix)){
            
                        $prixeuro = $prix/100;
        
                        echo '<tr>
                        
                            
                            <td>'.$nom.'</td>
                            <td>'.$nom_vendeur.'</td>
                            <td>'.$type.'</td>
                            <td>'.$souscat.'</td>
                            <td>'.$date_vente.'</td>
                            <td>'.$prixeuro.'€</td>
                            
                            
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