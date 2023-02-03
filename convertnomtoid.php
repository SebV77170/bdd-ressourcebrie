<?php
require('actions/db.php');

$sql='SELECT * FROM ticketdecaisse
      INNER JOIN users ON users.nom = ticketdecaisse.nom_vendeur';
$sth = $db->query($sql);
$results=$sth->fetchAll();

echo '<pre>';
var_dump($results);
echo '</pre>';

?>