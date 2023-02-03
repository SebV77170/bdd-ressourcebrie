<?php
require('actions/users/securityAction.php');
require('actions/objets/insertObjetDsDb.php');
require('actions/objets/recupDb.php');
require('actions/objets/getSommePoids.php');
require('actions/objets/miseAJourDb.php');

?>


<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body>
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Bilans';
            include("includes/header.php");
            $page = 3;
            include("includes/nav.php");
            ?>
            
            
            <?php
            if($_SESSION['admin'] >= 1){
            ?>
        
        
        <div class="doc">
            <ul>
                <a href="BilanObjetsCollectes.php"><li id="bleu">Objets Collectés</li></a>
                <a href="BilanObjetsVendus.php"><li id="bleu">Objets Vendus</li></a>
                <a href="bilanticketDeCaisse.php"><li id="bleu">Tickets de Caisse</li></a>
                <a href="bilanJournalier.php"><li id="bleu">Bilan Journalier</li></a>
                
            </ul>
        </div>
        
        <?php
            }else{
                echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
            }
            include('includes/footer.php');
            ?>
    </body>
</html>