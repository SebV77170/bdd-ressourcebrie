<?php

require('actions/users/securityAction.php');
require('actions/objets/getDBVenteTemp.php');
$limitation='LIMIT 3';
$where3="WHERE id_vendeur='".$_SESSION['id']."'";
$order='date_achat_dt DESC';
require('actions/objets/bilanticketDeCaisseAction.php');

?>

<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body>
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Accueil Vente';
            include("includes/header.php");
            $page = 2;
            include("includes/nav.php");
            include("includes/nav_vente.php");
            ?>
            
            
            <?php
            if($_SESSION['admin'] >= 1){
            ?>
          
          
          <p style="text-align: center;">Cliquez sur + dans le menu pour créer votre première vente.</br></br>
          Lors d'une vente encours, si vous souhaitez mettre le client en attente, cliquez de nouveau sur +.
          </p>
          
          <h3 style="text-align: center;">Vos 3 dernières ventes. Si vous souhaitez les modifier ou les supprimer, cliquez sur le bouton adéquat.</h3>     
        
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
        
        <?php foreach($getObjets as list($id, $nom, $idvendeur, $date, $nbr, $moyen, $numcheque, $banque, $transac, $prix, $lien)){
            
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