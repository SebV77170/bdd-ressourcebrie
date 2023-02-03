<?php require('actions/db.php');
?>

<?php

$getSommeTotale = $db -> prepare('SELECT SUM(prix_total) AS prix_total FROM ticketdecaisse');
$getSommeTotale->execute();

$SommeTotal = $getSommeTotale->fetch();
$Somme = $SommeTotal['prix_total'];

$getNbrTotalTicket = $db -> query('SELECT COUNT(*) FROM ticketdecaisse');


$NbrTotalTicket = $getNbrTotalTicket->fetchColumn();

?>