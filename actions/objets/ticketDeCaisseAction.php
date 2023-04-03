<?php require('actions/db.php'); ?>

<?php

$getAllObjets = $db->prepare('SELECT id, nom, categorie, souscat, prix, nbr, prixt FROM ticketdecaissetemp WHERE id_temp_vente = ?');
$getAllObjets->execute(array($_GET['id_temp_vente']));

$getObjets = $getAllObjets->fetchAll();

$getPrixTotal = $db->prepare('SELECT SUM(prixt) AS prix_total FROM ticketdecaissetemp WHERE id_temp_vente = ?');
$getPrixTotal -> execute(array($_GET['id_temp_vente']));

$getTotal = $getPrixTotal->fetch();

?>