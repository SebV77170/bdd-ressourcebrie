<?php
if(isset($_POST['modifierDate'])):
    if(!empty($_POST['date'])):
        $format_fr = $_POST['date'];
        $format_us = implode('-',array_reverse  (explode('-',$format_fr)));

        date_default_timezone_set('Europe/Paris');
        $date_achat = new DateTime(''.$format_us.'', new DateTimeZone('Europe/paris'));
        $date_achat1 = $date_achat->format('Y-m-d G:i');

        //on update la date du ticket de caisse modifié
        
        $sql11='UPDATE modifticketdecaisse 
            SET date_achat_dt = "'.$date_achat1.'"
            WHERE id_modif = '.$_GET['id_modif'].'';
        $sth11=$db->query($sql11);

        //on update la date de la db vente pour faire en sorte que l'onglet soit à jour

        $date_achat2 = $date_achat->format('d-m G:i');
        $sql12='UPDATE vente 
            SET dateheure = "'.$date_achat2.'"
            WHERE id_modif = '.$_GET['id_modif'].'';
        $sth12=$db->query($sql12);

        //On update les objets dans objets_vendus_modif

        $date_achat3 = $date_achat->format('d/m/Y G:i');
        $sql13='UPDATE objets_vendus_modif
            SET date_achat = "'.$date_achat3.'"
            WHERE id_modif = '.$_GET['id_modif'].'';
        $sth13=$db->query($sql13);

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
        //On rééecrit le ticket de caisse avec la bonne date

        $nouveaulien='tickets/archives_tickets/Ticket'.$ticket['id_ticket'].'.txt';
        $lien=''.$ticket['lien'].'';

        $res = file_get_contents($nouveaulien, 'c+b');

        $tab = explode("\r",$res);

        $tab[7]=' Date et heure : '.$date_achat3.'';

        $res1=implode("\r",$tab);

        $file = file_put_contents($nouveaulien,$res1);

        //On déplace le ticket dans le bon répertoire.

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

        //Il faut s'occuper de remettre les données dans la table paiement_mixte s'il s'agissait d'un paiement mixte

        if($ticket['moyen_paiement']=='mixte'):
            $sql8='SELECT * FROM paiement_mixte_modif WHERE id_ticket ='.$ticket['id_ticket'].'';
            $sth8=$db->query($sql8);
            $vente=$sth8->fetch();

            $sql9='INSERT INTO paiement_mixte (id_paiement_mixte, id_ticket, espece, carte, cheque) VALUES (?,?,?,?,?)';
            $sth9=$db->prepare($sql9);
            $sth9->execute(array(
                $vente['id_paiement_mixte'],
                $vente['id_ticket'],
                $vente['espece'],
                $vente['carte'],
                $vente['cheque'],
            ));

            $sql10='DELETE FROM paiement_mixte_modif WHERE id_ticket ='.$ticket['id_ticket'].'';
            $sth10=$db->query($sql10);
        endif;


    header('location:bilanticketDeCaisse.php');
        
         
    else:
        $message='Veuillez remplir la date svp.';
    endif;
endif;
?>