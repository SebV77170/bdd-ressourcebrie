

<?php

$recupDBvente = $db -> prepare('SELECT * FROM vente WHERE id_vendeur = ?');
$recupDBvente -> execute(array($_SESSION['id']));

$id_vente = $recupDBvente->fetchAll(PDO::FETCH_ASSOC);


?>



<nav class="navvente">
        <ul class="navvente_ul">
                <?php
                
                //On fait une boucle pour afficher les nouveaux onglets des nouvelles ventes.
                
                foreach($id_vente as $v):
                        if(isset($_GET['id_temp_vente'])):
                ?>
                        <li class="navvente_li <?php if($_GET['id_temp_vente'] == $v['id_temp_vente']){echo 'vert';}else{echo 'bleu';} ?>">
                                <a class="lien_li" href='objetsVendus.php?id_temp_vente=<?=$v['id_temp_vente']?><?php if($v['id_modif']>0): echo'&id_modif='.$v['id_modif'].'';endif;?>&modif=<?=$v['modif']?>'>
                                        <?php echo $v['dateheure'];
                                        if($v['modif']==1):
                                                echo ' Mod';
                                        endif;
                                        ?>
                                </a>
                        </li>
                <?php
                        else:
                        ?>
                        
                        <!--si l'id temp vente n'existe pas dans l'URL, on affichage avec la classe bleue-->
                        
                                <li class="navvente_li bleu">
                                        <a class="lien_li" href='objetsVendus.php?id_temp_vente=<?=$v['id_temp_vente']?><?php if($v['id_modif']>0): echo '&id_modif='.$v['id_modif'].'';endif;?>&modif=<?=$v['modif']?>'>
                                                <?php echo $v['dateheure'];
                                                if($v['modif']==1):
                                                        echo ' Mod';
                                                endif;
                                                ?>
                                        </a>
                                </li>
                        
                        <?php
                        endif;
                endforeach;
                ?>
                
                <!--Le formulaire du bouton ajoutvente est traité dans le fichier getDBVenteTemp.php-->
                <!--Affichage du + pour créer une nouvelle vente-->
                
                <li class="navvente_li <?php if($page == 1){echo 'vert';}else{echo 'bleu';} ?>"><form method=POST><input class="input inputsubmit" id = "ajoutvente" type="submit" name="ajout" value="+"></form></li>
         
        </ul>
</nav>