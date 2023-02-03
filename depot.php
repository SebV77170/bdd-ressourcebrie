<?php
require('actions/db.php');
require('actions/users/securityAction.php');
require('actions/objets/insertObjetDsDb.php');
//Ces variables $tridepot et $limit modifie le fichier recupDb.php pour n'afficher seulement que les 3 dernières saisies sur la page depot.php.
$tridepot = 'timestamp DESC';
$limit = " LIMIT 3";
// Cette variable permet de rajouter dans la requete SQL de recupDb, de n'afficher que les dernières saisies de la personne loguée et du jour actuel
$date_actuelle = date('d/m/Y');
$where1 = 'WHERE (saisipar = "'.$_SESSION['nom'].' '.$_SESSION['prenom'].'") AND (date = "'.$date_actuelle.'")';

require('actions/objets/recupDb.php');

$where2 = 'WHERE date = "'.$date_actuelle.'"';

require('actions/objets/getSommePoids.php');

?>


<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body>
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Collecte';
            include("includes/header.php");
            $page = 1;
            include("includes/nav.php");
            ?>
            
            
            <?php
            if($_SESSION['admin'] >= 1){
            ?>
        
         <p style="text-align: center;"> Poids Total d'objets <b>collectés</b> toute catégorie confondue aujourd'hui, le <?=$date_actuelle?> : </br>
         <?php
        $poids_total_obj_collecte_kg = $poids_total_obj_collecte['poids_total']/1000;
        echo $poids_total_obj_collecte_kg.' Kg';
        ?> </p>

        <form method="post">
                
                <fieldset>
                    
                    <label for="flux">Type d'apport: </label>
                    <select id="flux" name="flux">
                        <option value="Apport">Apport volontaire</option>
                        <option value="Collecte">Collecte à domicile</option>
                        <option value="Porte-a-porte">En porte-à-porte</option>
                        <option value="Déchèterie">En déchèterie</option>
                    </select>
            
            
                    <label for="type">Catégorie : </label>
                    <select id="type" name="type">
                        <option value="">Sélectionner une catégorie</option>
                        <?php
                        
                            /*On récupère les catégories dans la db catégorie*/
                            $result = $db->prepare('SELECT * FROM categories WHERE parent_id = "parent"');
                            $result->execute();
                            
                            //On les affiche dans le menu déroulant.                       
                            while($row = $result->fetch(PDO::FETCH_BOTH)){
                                ?><option value="<?php echo $row['category'];?>"><?php echo $row['category'];?></option>
                                <?php
                            }
                            
                        ?>
                        
                        
                    </select>
                    
                    <label for="SUBCATEGORY">Sous-catégorie :</label>
                    <select id="sub-category-dropdown" name="souscategorie">
                        <option value="">Sélectionner une sous-catégorie</option>
                    </select>
                    
                    <button type="button" onclick="getValue();">Ajouter une sous-catégorie</button>
                    
                    <!--ancienne zone de saisie retirée, mais gardé en hidden car pas le temps de modifier la base de donnée-->
                    <input type="hidden" name="nom">
            
                    <label for="poids">Poids en <p class='gramme'>GRAMME (lecture balance * 1000)</p>: </label>
                    <input type="poids" name="poids">
                    
                     <label for="reparation">Objet à réparer ou à vérifier</label>
                    <input type="checkbox" name="reparation">
                
                </fieldset>
                
                <?php if(isset($message)){
                    echo '<p style="text-align:center; color:red; font-size:30px">'.$message.'</p>';
                }
                ?>
            
                <input type="submit" name="validate" value="Insérer">
                
                
                
        </form>
        
        </table>
        
       <h3 style="text-align: center;">Vos 3 dernières saisies. Si vous souhaitez les modifier ou les supprimer, cliquez sur le bouton adéquat.</h3>     
        <table>
            <tr>
                <th>Id</th>
                <th>flux</th>
                <th>Catégorie</th>
                <th>Sous-Catégorie</th>
                <th>Poids en gramme</th>
                <th>Date d'insertion</th>
                
            </tr>
        
        <?php foreach($getObjets as list($id, $nom, $type, $souscat, $poids, $date, $timestamp, $flux)){
                    
        
                        echo '<tr>
                        
                            <td>'.$id.'</td>
                            <td>'.$flux.'</td>
                            <td>'.$type.'</td>
                            <td>'.$souscat.'</td>
                            
                            <td>'.$poids.'</td>
                            <td>'.$date.'</td>
                            
                            <td><a href="modifObjet.php?id='.$id.'&from=depot">Modifier</a></td>
                            
                            <td><a href="actions/objets/supprObjetAction.php?id='.$id.'&from=depot">Supprimer</a></td>
                            
                          </tr>'  ;
        }
        ?>
        </table>
        
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
            document.location.href='ajoutsouscat.php?from=depot&cat=' + input;
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