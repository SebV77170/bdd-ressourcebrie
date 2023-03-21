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
    <body class="corps">
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
            
        
        <table class="tableau">
            <tr class="ligne">
                <th class="cellule_tete">N° Ticket</th>
                <th class="cellule_tete">Nom du vendeur</th>
                <th class="cellule_tete">Date</th>
                <th class="cellule_tete">Nombre d'articles</th>
                <th class="cellule_tete">Moyen de Paiement</th>
                <th class="cellule_tete">Numéro Chèque</th>
                <th class="cellule_tete">Banque</th>
                <th class="cellule_tete">Transaction</th>
                <th class="cellule_tete">Prix</th>
                <th class="cellule_tete">Lien vers ticket</th>
                
            </tr>
        
        <?php foreach($getObjets as list($id, $nom, $id_vendeur, $date, $nbr, $moyen, $numcheque, $banque, $transac, $prix, $lien)){
            
                        $prixeuro = $prix/100;
        
                        echo '<tr class="ligne">
                        
                            
                            <td class="colonne">'.$id.'</td>
                            <td class="colonne">'.$nom.'</td>
                            <td class="colonne">'.$date.'</td>
                            <td class="colonne">'.$nbr.'</td>
                            <td class="colonne">'.$moyen.'</td>
                            <td class="colonne">'.$numcheque.'</td>
                            <td class="colonne">'.$banque.'</td>
                            <td class="colonne">'.$transac.'</td>
                            <td class="colonne">'.$prixeuro.'€</td>
                            <td class="colonne"><a href="ticketdecaisseapresvente.php?id_ticket='.$id.'">Voir le ticket</a></td>
                            ';
                            if($_SESSION['admin'] >1){
                            echo '<td class="colonne"><a href="confirmation.php?id_ticket='.$id.'">Supprimer</a></td>';
                            echo '<td class="colonne"><a href="actions/objets/modification.php?id_ticket='.$id.'">Modifier</a></td>';
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