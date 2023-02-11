<?php

session_start();
require('../db.php');

if(isset($_GET['id_modif'])):
    //On récupère les objets de la table objets_vendus_modif
    $sql='SELECT * FROM objets_vendus_modif WHERE id_modif='.$_GET['id_modif'].'';
    $sth=$db->query($sql);
    $objets=$sth->fetchAll();
    
    //On réinsert les objets dans la db objets_vendus et on supprime de la table objets_vendus_modif
    foreach($objets as $v):
        $sql1='INSERT INTO objets_vendus (id_ticket,nom_vendeur,id_vendeur,nom,categorie,souscat,date_achat,timestamp,prix) VALUES (?,?,?,?,?,?,?,?,?)';
        $sth1=$db->prepare($sql1);
        $sth1->execute(array($v['id_ticket'],$_SESSION['nom'],$v['id_vendeur'],$v['nom'],$v['categorie'],$v['souscat'],$v['date_achat'],$v['timestamp'],$v['prix']));

        $sql2='DELETE FROM objets_vendus_modif WHERE id_modif='.$_GET['id_modif'].'';
        $sth2=$db->query($sql2);
    endforeach;

    //On récupère les données de modifticketdecaisse et on réinsert les données dans ticketdecaisse

    $sql3='SELECT * FROM modifticketdecaisse WHERE id_modif='.$_GET['id_modif'].'';
    $sth3=$db->query($sql3);
    $ticket=$sth3->fetch();

    $sql4='INSERT INTO ticketdecaisse (id_ticket, nom_vendeur, id_vendeur, date_achat_dt, nbr_objet, moyen_paiement, num_cheque, banque, num_transac, prix_total, lien) VALUE (?,?,?,?,?,?,?,?,?,?,?)';
    $sth4=$db->prepare($sql4);
    $sth4->execute(array(
        $ticket['id_ticket'],
        $ticket['nom_vendeur'],
        $ticket['id_vendeur'],
        $ticket['date_achat_dt'],
        $ticket['nbr_objet'],
        $ticket['moyen_paiement'],
        $ticket['num_cheque'],
        $ticket['banque'],
        $ticket['num_transac'],
        $ticket['prix_total'],
        $ticket['lien'],
    ));

    //On déplace le ticket dans le bon répertoire.
    $lien='../../'.$results['lien'].'';
    $nouveaulien='../../tickets/archives_tickets/Ticket'.$ticket['idTicket'].'.txt';
    rename($nouveaulien,$lien);

    //On supprime les données de la table modifticketdecaisse

    $sql7='DELETE FROM modifticketdecaisse WHERE id_modif='.$_GET['id_modif'].'';
    $sth7=$db->query($sql7);

    //On supprime les objets de ticketdecaissetemp

    if(isset($_GET['id_temp_vente'])):
        $sql5='DELETE FROM ticketdecaissetemp WHERE id_temp_vente='.$_GET['id_temp_vente'].'';
        $sth5=$db->query($sql5);

        //On supprime la vente (table vente)

        $sql6='DELETE FROM vente WHERE id_temp_vente='.$_GET['id_temp_vente'].'';
        $sth6=$db->query($sql6);

    else:
        $message="Un problème est survenu avec le numéro de vente.";
    endif;

    header('location:../../bilanticketDeCaisse.php');
    
else:
    $message="Il n'y a pas de modification en cours.";
endif;
?>