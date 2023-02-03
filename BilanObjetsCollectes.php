<?php
require('actions/users/securityAction.php');
require('actions/objets/insertObjetDsDb.php');
require('actions/objets/recupDb.php');
require('actions/objets/getSommePoids.php');
require('actions/objets/miseAJourDb.php');

?>


<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body>
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Objets collectés';
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
        
        <p style="text-align: center;"> Poids Total d'objets <b>collectés</b> toute catégorie confondue : <?php
        $poids_total_obj_collecte_kg = $poids_total_obj_collecte['poids_total']/1000;
        echo $poids_total_obj_collecte_kg.' Kg';
        ?> </p>
        
        <form method="get">
                
                <fieldset>
            
                    <label for="tri">Trier par : </label>
                    <select id="tri" name="tri">
                        <option value="nom">Nom</option>
                        <option value="categorie">Catégorie</option>
                        <option value="poids">Poids</option>
                        <option value="timestamp">Date d'ajout</option>
                    </select>
                
                </fieldset>
            
                <input type="submit" name="validate" value="Trier">
        </form>
        
        <table>
            <tr>
                <th>Categories</th>
                <th>Poids total</th>
                <th>Pourcentage</th>
            </tr>
            
        <?php
        
        foreach($LesSommes as list($categorie, $poids_total_par_cat)){
            $poids_total_par_cat_kg = $poids_total_par_cat/1000;
            $pourcentage = round((($poids_total_par_cat_kg * 100) / $poids_total_obj_collecte_kg),1);
            echo '<tr>
                        
                            <td>'.$categorie.'</td>
                            <td>'.$poids_total_par_cat_kg.' kg</td>
                            <td>'.$pourcentage.'%</td> 

                          </tr>'  ;
        }
        ?>
            
            
        </table>
        
        
        <table>
            <tr>
                <th>Id</th>
                <th>flux</th>
                <th>Catégorie</th>
                <th>Sous-Catégorie</th>
                <th>Précision</th>
                <th>Poids en gramme</th>
                <th>Date d'insertion</th>
                
            </tr>
        
        <?php foreach($getObjets as list($id, $nom, $type, $souscat, $poids, $date, $timestamp, $flux)){
                    
        
                        echo '<tr>
                        
                            <td>'.$id.'</td>
                            <td>'.$flux.'</td>
                            <td>'.$type.'</td>
                            <td>'.$souscat.'</td>
                            <td>'.$nom.'</td>
                            <td>'.$poids.'</td>
                            <td>'.$date.'</td>
                            
                            <td><a href="modifObjet.php?id='.$id.'">Modifier</a></td>
                            
                            <td><a href="actions/objets/supprObjetAction.php?id='.$id.'">Supprimer</a></td>
                            
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