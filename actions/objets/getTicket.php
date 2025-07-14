<?php require('actions/db.php'); ?>

<?php

$getTicket = $db->prepare('SELECT lien FROM ticketdecaisse WHERE uuid_ticket = ?');
$getTicket->execute(array($_GET['id_ticket']));

$getFacture = $db->prepare('SELECT lien FROM facture WHERE uuid_ticket = ?');
$getFacture->execute(array($_GET['id_ticket']));

$ticket = $getTicket->fetch();
$facture = $getFacture->fetch();
if(!empty($facture[0])):
    $lien = $facture[0];
else:
    $lien = $ticket[0];
endif;

?>
