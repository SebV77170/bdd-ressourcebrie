<?php
require('actions/users/securityAction.php');
$limitation='';
$order = 'date_achat_dt DESC';
$where3 ='';
require('actions/objets/bilanticketDeCaisseAction.php');
require('actions/objets/getPanierMoyenAction.php');


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
            
            <p style="text-align: center;"> Panier moyen : <?php
            if($NbrTotalTicket>0){
            $paniermoyen = round((($Somme/$NbrTotalTicket)/100),1);
            echo $paniermoyen.'€';
            }else{
                echo '0€';
            }
            ?> </p>
            
            <p style="text-align: center;"> Prix Total : <?php
            $getTotalEnEuros = $getTotal['prix_total']/100;
            echo $getTotalEnEuros.'€';
            ?> </p>
            
        
        <table>
            <tr>
                <th>N° Ticket</th>
                <th>Nom du vendeur</th>
                <th>Date</th>
                <th>Nombre d'articles</th>
                <th>Moyen de Paiement</th>
                <th>Numéro Chèque</th>
                <th>Banque</th>
                <th>Transaction</th>
                <th>Prix</th>
                <th>Lien vers ticket</th>
                
            </tr>
        
        <?php foreach($getObjets as list($id, $nom, $id_vendeur, $date, $nbr, $moyen, $numcheque, $banque, $transac, $prix, $lien)){
            
                        $prixeuro = $prix/100;
        
                        echo '<tr>
                        
                            
                            <td>'.$id.'</td>
                            <td>'.$nom.'</td>
                            <td>'.$date.'</td>
                            <td>'.$nbr.'</td>
                            <td>'.$moyen.'</td>
                            <td>'.$numcheque.'</td>
                            <td>'.$banque.'</td>
                            <td>'.$transac.'</td>
                            <td>'.$prixeuro.'€</td>
                            <td><a href="ticketdecaisseapresvente.php?id_ticket='.$id.'">Voir le ticket</a></td>
                            ';
                            if($_SESSION['admin'] >1){
                            echo '<td><a href="confirmation.php?id_ticket='.$id.'">Supprimer</a></td>';
                            echo '<td><a href="actions/objets/modification.php?id_ticket='.$id.'">Modifier</a></td>';
                            }
                            echo '</tr>';
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