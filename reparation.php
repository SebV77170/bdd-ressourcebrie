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
    <body>
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
                <th>Id_objet</th>
                <th>Catégorie</th>
                <th>Sous-Catégorie</th>
                <th>Réparation</th>
                <th>Nom de celui qui a saisi</th>
                <th>Date</th>
                <th>Nom de ceux qui reparent</th>
                <th>Date de dernière modif</th>
                
            </tr>
        
        <?php foreach($dataObjet as list($id, $categorie, $souscat, $reparation, $saisipar, $date, $timestamp, $reparepar, $daterep, $timestamprep, $end)){
                    
                    if($end==0){
        
                        echo '<tr>
                        
                            <td>'.$id.'</td>
                            <td>'.$categorie.'</td>
                            <td>'.$souscat.'</td>
                            <td>'.$reparation.'</td>
                            <td>'.$saisipar.'</td>
                            <td>'.$date.'</td>
                            <td>'.$reparepar.'</td>
                            <td>'.$daterep.'</td>
                            
                            <td><a href="modifObjetRep.php?id='.$id.'">Modifier</a></td>
                            
                            <td><a href="endObjetRep.php?id='.$id.'">Terminer</a></td>
                            
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