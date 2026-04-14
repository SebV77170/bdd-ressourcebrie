<?php require('actions/db.php'); ?>

<?php

$getTicket = $db->prepare('SELECT lien FROM ticketdecaisse WHERE uuid_ticket = ?');
$getTicket->execute(array($_GET['uuid_ticket']));

$getFacture = $db->prepare('SELECT lien FROM facture WHERE uuid_ticket = ?');
$getFacture->execute(array($_GET['uuid_ticket']));

$ticket = $getTicket->fetch();
var_dump($ticket);
$facture = $getFacture->fetch();
if(!empty($facture['lien'])):
    $lien = $facture['lien'];
else:
    $lien = $ticket['lien'];
endif;

?>
