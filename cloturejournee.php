<?php
require('actions/users/securityAction.php');

$date_actuelle = date('d/m/Y');
$where2 = 'WHERE date = "'.$date_actuelle.'"';
require('actions/objets/getSommePoids.php');

require('actions/objets/getSommePrixVendus.php');
$prix_total_journee = $prix_total_ticket['prix_total']/100;


$paiement = 'AND (moyen_paiement = "cheque" )';
require('actions/objets/getSommePrixVendus.php');
$prix_total_journee_cheque = $prix_total_ticket['prix_total']/100;


$paiement = 'AND (moyen_paiement = "espece" )';
require('actions/objets/getSommePrixVendus.php');
$prix_total_journee_espece = $prix_total_ticket['prix_total']/100;

$paiement = 'AND (moyen_paiement = "carte" )';
require('actions/objets/getSommePrixVendus.php');
$prix_total_journee_carte = $prix_total_ticket['prix_total']/100;

?>


<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body>
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'BDD 2.0';
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
        
        <p style="text-align: center;">Total des ventes = <?=$prix_total_journee?> €</p>
        <p style="text-align: center;">dont <?=$prix_total_journee_espece?> € en espèce</p>
        <p style="text-align: center;">, <?=$prix_total_journee_cheque?> € en chèque.</p>
        <p style="text-align: center;">et <?=$prix_total_journee_carte?> € en carte.</p>
        
        <?php
            }else{
                echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
            }
            
            include('includes/footer.php');
            ?>
            
    </body>
</html>