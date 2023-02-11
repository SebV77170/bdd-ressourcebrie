<?php
require('actions/users/securityAction.php');
require('actions/objets/objetsVendusAction.php');
require('actions/objets/ticketDeCaisseAction.php');
require('actions/objets/compteObjetDsTCtemp.php');
require('actions/objets/getPoidsTotal.php');
require('actions/objets/getDBVenteTemp.php');
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
            $page = 2;
            include("includes/nav.php");
            include("includes/nav_vente.php");
            ?>
            
            
            <?php
            if($_SESSION['admin'] >= 1){
            ?>
            
            
            
     <!--Formulaire de vente-->       
                
             <form classe="vente" method="post">
                
                <fieldset>
            
                    <label for="nom">Nom ou description sommaire de l'objet : </label>
                    <input type="text" name="nom">

                    <input type="hidden" name="modif" value=<?=$_GET['modif']?>>
                    <?php
                        if(isset($_GET['id_modif'])):
                        ?>
                        <input type="hidden" name="id_modif" value=<?=$_GET['id_modif']?>>
                        <?php
                        endif;
                    ?>
            
                    <label for="type">Catégorie : </label>
                    <select id="type" name="type">
                        <option value="">Sélectionner une catégorie</option>
                        
                        <!--Va chercher les catégories dans la table categories-->
                        
                        <?php
                            $result = $db->prepare('SELECT * FROM categories WHERE parent_id = "parent"');
                            $result->execute();
                            
                            while($row = $result->fetch(PDO::FETCH_BOTH)){
                                ?><option value="<?php echo $row['category'];?>"><?php echo $row['category'];?></option>
                                <?php
                            }
                            
                        ?>
                    </select>
                    
                    <!--Attention, id importante sub-category-dropdown car liée au script en bas du fichier, ceci afin de liée catégories et sous catégories-->
                    
                    <label for="SUBCATEGORY">Sous-catégorie :</label>
                    <select id="sub-category-dropdown" name="souscategorie">
                        <option value="">Sélectionner une sous-catégorie</option>
                    </select>
                    
                    <button type="button" onclick="getValue();">Ajouter une sous-catégorie</button>
                    
            
                    <label for="prix">Prix: </label>
                    <input type="prix" name="prix">
                
                </fieldset>
            
                <input type="submit" name="validate" value="Vendre">
                
        </form>
        
        <p style='text-align: center;'>Nom du vendeur : <?=$_SESSION['nom']?></p>
        
        
        <!--information sur le nombre d'objets contenu dans le ticket de caisse temporaire, compte les entrées dans la table ticketdecaissetemp-->
        <p style="text-align: center;"> Nombre d'objet : <?php
        if(isset($NbrObjetDeTC)){
        echo $NbrObjetDeTC;
        }else{
            echo 0;        }
        ?> </p>
        
        <!--Affichage en directe du future ticket de caisse-->
        
        <table>
            <tr>
                <th>Nom</th>
                <th>Catégorie</th>
                <th>Sous-Catégorie</th>
                <th>Prix en €</th>
            </tr>
        
        <?php foreach($getObjets as list($id, $nom, $categorie, $souscat, $prix)){
            
            $prixeuro = $prix/100;
            if(isset($_GET['id_modif'])):
                echo '<tr>
                
                    
                    <td>'.$nom.'</td>
                    <td>'.$categorie.'</td>
                    <td>'.$souscat.'</td>
                    <td>'.$prixeuro.'€</td>
                    <td><a href="actions/objets/supprObjetDeTC.php?id='.$id.'&id_temp_vente='.$_GET['id_temp_vente'].'&id_modif='.$_GET['id_modif'].'&modif='.$_GET['modif'].'">Supprimer</a></td>
                    
                    
                    </tr>'  ;
            else:
                echo '<tr>
                
                    
                    <td>'.$nom.'</td>
                    <td>'.$categorie.'</td>
                    <td>'.$souscat.'</td>
                    <td>'.$prixeuro.'€</td>
                    <td><a href="actions/objets/supprObjetDeTC.php?id='.$id.'&id_temp_vente='.$_GET['id_temp_vente'].'&modif='.$_GET['modif'].'">Supprimer</a></td>
                    
                    
                    </tr>'  ;
            endif;
        }
        ?>
        </table>

        <p style="text-align: center;"> Prix Total : <?php
        $getTotalEnEuros = $getTotal['prix_total']/100;
        echo $getTotalEnEuros.'€';
        ?> </p>
        
        <?php 
        if($NbrObjetDeTC > 0):
            if(isset($_GET['id_modif'])):
            ?>
                <a href="moyenDePaiement.php?prix=<?=$getTotalEnEuros?>&nbrObjet=<?=$NbrObjetDeTC?>&id_temp_vente=<?=$_GET['id_temp_vente']?>&id_modif=<?=$_GET['id_modif']?>&modif=<?=$_GET['modif']?>" class="stdbouton">Valider</a>
            <?php
            else:
            ?>
                <a href="moyenDePaiement.php?prix=<?=$getTotalEnEuros?>&nbrObjet=<?=$NbrObjetDeTC?>&id_temp_vente=<?=$_GET['id_temp_vente']?>&modif=<?=$_GET['modif']?>" class="stdbouton">Valider</a>
            <?php
            endif;
        endif;
        ?>
        
        <?php
        if($_GET['modif']==1):
        ?>
            <a href="actions/objets/annulemodif.php?id_temp_vente=<?=$_GET['id_temp_vente']?>&id_modif=<?=$_GET['id_modif']?>" class="stdbouton">Annuler Modification </a>
        <?php
        else:
        ?>
            <a href="actions/objets/annulerVenteAction.php?id_temp_vente=<?=$_GET['id_temp_vente']?>" class="stdbouton">Annuler </a>
        <?php
        endif;
        ?>
        <!-- Script Jquery pour dérouler des sous catégories à partir des catégories-->
        
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
        <script>
            $(document).ready(function() {
                $('#type').on('change',function(){
                    var category_id = this.value;
                    $.ajax({
                        url:"actions/objets/get-subcat.php",
                        type:"POST",
                        data:{
                            category_id: category_id 
                        },
                        cache: false,
                        success: function(result){
                            $("#sub-category-dropdown").html(result);
                        }
                    });
                });
            });
        </script>
        <!--Le script ci-dessous permet de récupérer la valeure de la catégorie pour la passer dans la page ajoutsouscat directement, évitant à l'utilisateur de saisir de nouveau la catégorie        -->
        <script>
            function getValue() {
            // Sélectionner l'élément input et récupérer sa valeur
            var input = document.getElementById("type").value;
            // Afficher la valeur
            document.location.href='ajoutsouscat.php?from=vente&id_temp_vente=<?=$_GET['id_temp_vente']?>&cat='+input;
            }    
        </script>
        
        <?php
            }else{
                echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
            }
            include('includes/footer.php');
            ?>
            
    </body>
</html>