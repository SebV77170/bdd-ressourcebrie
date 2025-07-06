<?php
session_start();
require('../db.php');

if (isset($_GET['id_ticket'])):

    $idTicket = $_GET['id_ticket'];
    $sql = 'SELECT * FROM ticketdecaisse WHERE id_ticket =' . $idTicket;
    $sth = $db->query($sql);
    $results = $sth->fetch();
    $count = count($results);

    if ($count > 0):

        // Insertion des données du ticket de caisse dans une table temporaire modifticketdecaisse
        $sql5 = 'INSERT INTO modifticketdecaisse (id_ticket, nom_vendeur, id_vendeur, date_achat_dt, nbr_objet, moyen_paiement, num_cheque, banque, num_transac, prix_total, lien, reducbene, reducclient, reducgrospanierclient, reducgrospanierbene) VALUE (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
        $sth5 = $db->prepare($sql5);
        $sth5->execute(array(
            $results['id_ticket'],
            $results['nom_vendeur'],
            $results['id_vendeur'],
            $results['date_achat_dt'],
            $results['nbr_objet'],
            $results['moyen_paiement'],
            $results['num_cheque'],
            $results['banque'],
            $results['num_transac'],
            $results['prix_total'],
            $results['lien'],
            $results['reducbene'],
            $results['reducclient'],
            $results['reducgrospanierclient'],
            $results['reducgrospanierbene'],
        ));

        // S'occuper du paiement mixte pour stocker temporairement les données de la table paiement_mixte s'il s'agissait d'un paiement mixte
        if ($results['moyen_paiement'] == 'mixte'):
            $sql8 = 'SELECT * FROM paiement_mixte WHERE id_ticket =' . $idTicket;
            $sth8 = $db->query($sql8);
            $vente = $sth8->fetch();

            $sql9 = 'INSERT INTO paiement_mixte_modif (id_paiement_mixte, id_ticket, espece, carte, cheque, virement) VALUES (?,?,?,?,?,?)';
            $sth9 = $db->prepare($sql9);
            $sth9->execute(array(
                $vente['id_paiement_mixte'],
                $vente['id_ticket'],
                $vente['espece'],
                $vente['carte'],
                $vente['cheque'],
                $vente['virement'],
            ));

            $sql10 = 'DELETE FROM paiement_mixte WHERE id_ticket =' . $idTicket;
            $sth10 = $db->query($sql10);
        endif;

        // Récupérer l'id de la modification
        $sql6 = 'SELECT id_modif FROM modifticketdecaisse WHERE id_ticket =' . $idTicket . ' ORDER BY id_modif DESC';
        $sth6 = $db->query($sql6);
        $id_modif = $sth6->fetch();
        $id_modif = $id_modif[0];

        // Sélection des objets de ce ticket de caisse à modifier
        $sql1 = 'SELECT * FROM objets_vendus WHERE uuid_ticket =' . $idTicket;
        $sth1 = $db->query($sql1);
        $objets = $sth1->fetchAll();

        // Obtenir un timestamp avec le fuseau horaire parisien
        try {
            $date_heure_debutvente = new DateTimeImmutable($results['date_achat_dt'], new DateTimeZone('europe/paris'));
        } catch (Exception $e) {
            echo $e->getMessage();
            exit(1);
        }

        $date_heure_debutvente_TS = $date_heure_debutvente->format('U');

        // Obtenir la date et l'heure correspondante
        $date_heure_debutvente_Date = $date_heure_debutvente->format('d-m G:i:s');

        // Obtenir le nom du vendeur
        $idvendeur = $_SESSION['id'];

        // Insérer la nouvelle vente dans la db vente
        $insertDate = $db->prepare('INSERT INTO vente(date, dateheure, id_vendeur, modif, id_modif) VALUE (?,?,?,1,?)');
        $insertDate->execute(array($date_heure_debutvente_TS, $date_heure_debutvente_Date, $idvendeur, $id_modif));

        // Pour rediriger vers la nouvelle vente en cours dès qu'on clique sur +
        $idvente = $db->prepare('SELECT id_temp_vente FROM vente WHERE date = ?');
        $idvente->execute(array($date_heure_debutvente_TS));
        $id = $idvente->fetch(PDO::FETCH_ASSOC);
        $id = $id['id_temp_vente'];

        foreach ($objets as $v):
            $prix_t = $v['nbr'] * $v['prix'];
            $sql2 = 'INSERT INTO ticketdecaissetemp (id_temp_vente, nom_vendeur, id_vendeur, nom, categorie, souscat, prix, nbr, prixt) VALUES (?,?,?,?,?,?,?,?,?)';
            $sth2 = $db->prepare($sql2);
            $sth2->execute(array($id, $_SESSION['nom'], $idvendeur, $v['nom'], $v['categorie'], $v['souscat'], $v['prix'], $v['nbr'], $prix_t));

            $sql7 = 'INSERT INTO objets_vendus_modif (id_modif, id_temp_vente, id_ticket, nom_vendeur, id_vendeur, nom, categorie, souscat, date_achat, timestamp, prix, nbr) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)';
            $sth7 = $db->prepare($sql7);
            $sth7->execute(array($id_modif, $id, $v['id_ticket'], $_SESSION['nom'], $idvendeur, $v['nom'], $v['categorie'], $v['souscat'], $v['date_achat'], $v['timestamp'], $v['prix'], $v['nbr']));

            $sql3 = 'DELETE FROM objets_vendus WHERE uuid_ticket=' . $idTicket;
            $sth3 = $db->query($sql3);
        endforeach;

        // Déplacer le fichier de ticket de caisse vers les archives
        $lien = '../../' . $results['lien'];
        $nouveaulien = '../../tickets/archives_tickets/Ticket' . $idTicket . '.txt';
        rename($lien, $nouveaulien);

        // Supprimer le ticket de caisse de la base de données
        $sql4 = 'DELETE FROM ticketdecaisse WHERE id_ticket =' . $idTicket;
        $sth4 = $db->query($sql4);

        // Rediriger vers la page objetsVendus.php
        header('location:../../objetsVendus.php?id_temp_vente=' . $id . '&id_modif=' . $id_modif . '&modif=1');

    else:
        $message = 'Il n\'y a pas de vente avec cet ID';
        header('location:../../erreur.php?message=' . $message);
    endif;

else:
    $message = 'l\'ID de la vente n\'a pas été rentrée.';
    header('location:../../erreur.php?message=' . $message);
endif;

?>