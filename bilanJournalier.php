<?php
require('actions/users/securityAction.php');
require('actions/db.php');

$sql ='SELECT date, timestamp, nombre_vente, poids, prix_total, prix_total_espece, prix_total_cheque, prix_total_carte FROM bilan ORDER by timestamp DESC';
$sth = $db->query($sql);
$results=$sth->fetchAll();


?>

<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body>
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Bilan des Tickets de caisse';
            include("includes/header.php");
            $page = 3;
            include("includes/nav.php");
            ?>
            
            
            <?php
            if($_SESSION['admin'] >= 1){
            ?>
            
            
            
        
        <table>
            <tr>
                <th>Date</th>
                <th>Nombre de vente</th>
                <th>Poids</th>
                <th>Recette Totale</th>
                <th>Espèces</th>
                <th>Chèques</th>
                <th>Carte</th>
                
            </tr>
        
        <?php foreach($results as list($date, $timestamp, $nombre, $poids, $total, $espece, $cheque, $carte)){
            
                        $poids = $poids/1000;
                        $total = $total/100;
                        $espece = $espece/100;
                        $cheque = $cheque/100;
                        $carte = $carte/100;
                        $format_us = implode('-',array_reverse  (explode('/',$date)));
        
                        echo '<tr>
                        
                            
                            <td>'.$date.'</td>
                            <td>'.$nombre.'</td>
                            <td>'.$poids.' kg</td>
                            <td>'.$total.' €</td>
                            <td>'.$espece.' €</td>
                            <td>'.$cheque.' €</td>
                            <td>'.$carte.' €</td>
                            <td><a href="actions/objets/update_db_bilan_manuel.php?date='.$date.'">Mise à jour</a></td>
                            
                            <td><a href="actions/objets/compiltxt.php?date='.$format_us.'">Compiler les tickets du jour</a></td>
                            
                            </tr>';
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