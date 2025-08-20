<?php


require('actions/db.php');

if (isset($_POST['ajout'])) {

    try {
        $date_heure_debutvente = new DateTimeImmutable('now', new DateTimeZone('Europe/Paris'));
    } catch (Exception $e) {
        echo $e->getMessage();
        exit(1);
    }

    // Format compatible MySQL DATETIME
    $date_heure_debutvente_Date = $date_heure_debutvente->format('Y-m-d H:i:s');
    $idvendeur = $_SESSION['uuid_user'];

    $insertDate = $db->prepare('INSERT INTO vente(dateheure, id_vendeur, modif) VALUES (?, ?, 0)');
    $insertDate->execute([$date_heure_debutvente_Date, $idvendeur]);

    $id = $db->lastInsertId();
    $modif = 0;

    header('location:objetsVendus.php?id_temp_vente=' . $id . '&modif=' . $modif . '#tc');
}
?>
