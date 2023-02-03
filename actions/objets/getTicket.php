<?php require('actions/db.php'); ?>

<?php

$getTicket = $db->prepare('SELECT lien FROM ticketdecaisse WHERE id_ticket = ?');
$getTicket->execute(array($_GET['id_ticket']));

$ticket = $getTicket->fetch();
$lien = $ticket[0];

?>