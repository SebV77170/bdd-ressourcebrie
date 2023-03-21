<?php
require('actions/users/securityAction.php');
require('actions/db.php');


if (isset ($_GET['tri'])){
    $tri=$_GET['tri'];
}else{
$tri = 'categorie';
}
//On récupère les inforamtions de l'objet depuis la db reparation
        
        $recupDataObjet = $db -> prepare('SELECT id_objet, categorie, souscat, reparation, saisipar, date, timestamp, reparepar, daterep, timestamprep, end FROM reparation ORDER BY '.$tri.'');
        $recupDataObjet -> execute();
        $dataObjet = $recupDataObjet->fetchAll(PDO::FETCH_BOTH);

?>


<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body class="corps">
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Réparations à effectuer';
            include("includes/header.php");
            $page = 4;
            include("includes/nav.php");
            ?>
            
            
            <?php
            if($_SESSION['admin'] >= 1){
            ?>
        
        
        <?php if(isset($message)){
            echo '<p style="text-align: center;">'.$message.'</p>';
        }
        ?>
        
        
        <form method="get">
                
                <fieldset class="jeuchamp">
            
                    <label class="champ" for="tri">Trier par : </label>
                    <select id="tri" name="tri">
                        <option value="nom">Nom</option>
                        <option value="categorie">Catégorie</option>
                        <option value="poids">Poids</option>
                        <option value="timestamp">Date d'ajout</option>
                    </select>
                
                </fieldset>
            
                <input type="submit" class="input inputsubmit" name="validate" value="Trier">
        </form>
        
        
        <table class="tableau">
            <tr class="ligne">
                <th class="cellule_tete">Id_objet</th>
                <th class="cellule_tete">Catégorie</th>
                <th class="cellule_tete">Sous-Catégorie</th>
                <th class="cellule_tete">Réparation</th>
                <th class="cellule_tete">Nom de celui qui a saisi</th>
                <th class="cellule_tete">Date</th>
                <th class="cellule_tete">Nom de ceux qui reparent</th>
                <th class="cellule_tete">Date de dernière modif</th>
                
            </tr>
        
        <?php foreach($dataObjet as list($id, $categorie, $souscat, $reparation, $saisipar, $date, $timestamp, $reparepar, $daterep, $timestamprep, $end)){
                    
                    if($end==0){
        
                        echo '<tr class="ligne">
                        
                            <td class="colonne">'.$id.'</td>
                            <td class="colonne">'.$categorie.'</td>
                            <td class="colonne">'.$souscat.'</td>
                            <td class="colonne">'.$reparation.'</td>
                            <td class="colonne">'.$saisipar.'</td>
                            <td class="colonne">'.$date.'</td>
                            <td class="colonne">'.$reparepar.'</td>
                            <td class="colonne">'.$daterep.'</td>
                            
                            <td class="colonne"><a href="modifObjetRep.php?id='.$id.'">Modifier</a></td>
                            
                            <td class="colonne"><a href="endObjetRep.php?id='.$id.'">Terminer</a></td>
                            
                          </tr>'  ;
                    }
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