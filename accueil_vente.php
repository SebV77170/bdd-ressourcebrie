<?php
require('actions/users/securityAction.php');
require('actions/objets/getDBVenteTemp.php');
$limitation='LIMIT 3';
$where3="WHERE id_vendeur='".$_SESSION['uuid_user']."'";
$order='date_achat_dt DESC';
require('actions/objets/bilanticketDeCaisseAction.php');

?>

<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body class="corps">
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

            <alerte-logiciel>
            Attention ! Partie vente à n'utiliser qu'en cas de dysfonctionnement du logiciel de caisse installé sur l'ordinateur de caisse. Toutes les fonctions ne sont pas actives ici.
            </alerte-logiciel>
          
          
          <p style="text-align: center;">Cliquez sur + dans le menu pour créer votre première vente.</br></br>
          Lors d'une vente encours, si vous souhaitez mettre le client en attente, cliquez de nouveau sur +.
          </p>
          
          <h3 style="text-align: center;">Vos 3 dernières ventes. Si vous souhaitez les modifier ou les supprimer, cliquez sur le bouton adéquat.</h3>     
        
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
        
       <?php foreach ($getObjets as $objet) {
    $prixeuro = $objet['prix_total'] / 100;

    echo '<tr class="ligne">
        <td class="colonne">' . $objet['id_ticket'] . '</td>
        <td class="colonne">' . htmlspecialchars($objet['nom_vendeur']) . '</td>
        <td class="colonne">' . $objet['date_achat_dt'] . '</td>
        <td class="colonne">' . $objet['nbr_objet'] . '</td>
        <td class="colonne">' . $objet['moyen_paiement'] . '</td>
        <td class="colonne">' . $objet['num_cheque'] . '</td>
        <td class="colonne">' . $objet['banque'] . '</td>
        <td class="colonne">' . $objet['num_transac'] . '</td>
        <td class="colonne">' . $prixeuro . '€</td>
        <td class="colonne"><a href="ticketdecaisseapresvente.php?uuid_ticket=' . $objet['uuid_ticket'] . '">Voir le ticket</a></td>';

    if ($_SESSION['admin'] > 1) {
        echo '<td class="colonne"><a href="confirmation.php?uuid_ticket=' . $objet['uuid_ticket'] . '">Supprimer</a></td>';
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