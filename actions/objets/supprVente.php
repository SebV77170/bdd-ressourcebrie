<?php

require('../db.php');

if (isset($_GET['uuid_ticket'])) {
    $uuid_ticket = $_GET['uuid_ticket'];

    // On récupère les infos du ticket
    $sql = "SELECT * FROM ticketdecaisse WHERE uuid_ticket = ?";
    $sth = $db->prepare($sql);
    $sth->execute([$uuid_ticket]);
    $result = $sth->fetch();

    if ($result) {
        $lien = '../../' . $result['lien'];
        $date = $result['date_achat_dt'];

        // On supprime les objets liés
        $sql1 = 'DELETE FROM objets_vendus WHERE uuid_ticket = ?';
        $sth1 = $db->prepare($sql1);
        $sth1->execute([$uuid_ticket]);

        // On supprime le fichier du ticket PDF si présent
        if (file_exists($lien) && unlink($lien)) {
            $message1 = 'Le ticket de caisse a bien été supprimé.';
        } else {
            $message1 = 'Une erreur s\'est produite lors de la suppression du ticket de caisse.';
        }

        // Suppression du ticket dans la base
        $sql2 = 'DELETE FROM ticketdecaisse WHERE uuid_ticket = ?';
        $sth2 = $db->prepare($sql2);
        $sth2->execute([$uuid_ticket]);

        require('update_db_bilan_apres_suppr.php');

        header('Location: ../../accueil_vente.php');
        exit;
    } else {
        $message = 'Ticket non trouvé.';
    }
} else {
    $message = 'Aucune vente trouvée avec cette id.';
}
