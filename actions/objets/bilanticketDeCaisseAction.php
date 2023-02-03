<?php require('actions/db.php'); ?>

<?php

$getAllObjets = $db->prepare('SELECT * FROM ticketdecaisse '.$where3.' ORDER BY '.$order.' '.$limitation.'');
$getAllObjets->execute();

$getObjets = $getAllObjets->fetchAll();

$getPrixTotal = $db->prepare('SELECT SUM(prix_total) AS prix_total FROM ticketdecaisse');
$getPrixTotal -> execute();

$getTotal = $getPrixTotal->fetch();

?>