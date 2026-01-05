<?php
require('actions/users/securityAction.php');
require('actions/objets/insertObjetDsDb.php');
require('actions/objets/recupDb.php');
require('actions/objets/getSommePoids.php');
require('actions/objets/miseAJourDb.php');
$annee = date('Y');

?>


<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body class="corps">
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
            <ul class="doc_ul">
                
                <a class="doc_lien" href="dashboard_bilan.php"><li class="doc_li" id="bleu">Tableau de bord</li></a>
                <a class="doc_lien" href="bilanJournalier.php"><li class="doc_li" id="bleu">Bilan Journalier</li></a>
                <a class="doc_lien" href="bilanticketDeCaisse.php"><li class="doc_li" id="bleu">Tickets de Caisse</li></a>
                <a class="doc_lien" href="prixoeuvre.php?annee=<?php echo $annee ?>"><li class="doc_li" id="bleu">Oeuvres</li></a>
                <a class="doc_lien" href="BilanObjetsCollectes.php"><li class="doc_li" id="bleu">Objets Collectés</li></a>
                <a class="doc_lien" href="BilanObjetsVendus.php"><li class="doc_li" id="bleu">Objets Vendus</li></a>
                <a class="doc_lien" href="bilanDechetterie.php"><li class="doc_li" id="bleu">Bilan dechetterie</li></a>
                <a class="doc_lien" href="telechargementDB.php"><li class="doc_li" id="bleu">Télécharger la DB 2024</li></a>
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