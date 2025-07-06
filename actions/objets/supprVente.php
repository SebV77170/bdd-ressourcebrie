<?php

require('../db.php');

if(isset($_GET['id_ticket'])):


    //    On récupère le lien du fichier du TC ainsi que la date de la vente

    $id_ticket = $_GET['id_ticket'];
    $sql = 'SELECT * FROM ticketdecaisse WHERE id_ticket = '.$id_ticket.'';
    $sth = $db->query($sql);
    $result = $sth->fetch();
    
    
    $lien='../../'.$result['lien'].'';
    $date=$result['date_achat_dt'];
    
    //  On supprime de la db objets_vendus les objets liés au TC.
    
    $sql1 = 'DELETE FROM objets_vendus WHERE uuid_ticket = ?';
    $sth1 = $db->prepare($sql1);
    $sth1->execute(array($id_ticket));
    
    //  On effectue et vérifie la suppression du ticket de caisse.
    
    if(unlink($lien)):
        $message1='Le ticket de caisse a bien été supprimé.';
    else:
        $message1='Une erreur s\'est produite lors de la suppression du ticket de caisse.';
    endif;
    
    //  On supprime la vente de la bdd ticketdecaisse
    
    $sql2 = 'DELETE FROM ticketdecaisse WHERE id_ticket='.$id_ticket.'';
    $sth2 = $db->query($sql2);
    
    require('update_db_bilan_apres_suppr.php');
    
    header('location:../../accueil_vente.php');
    
else:
    $message = 'Aucune vente trouvée avec cette id.';
    
endif;

?>