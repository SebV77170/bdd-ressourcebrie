<?php


$sommeTotaleTicket = $db -> prepare('SELECT SUM(prix_total) AS prix_total FROM ticketdecaisse WHERE (date_achat_dt LIKE "'.$format_us.'%") '.$paiement.'');
$sommeTotaleTicket->execute();

$prix_total_ticket = $sommeTotaleTicket -> fetch();

?>