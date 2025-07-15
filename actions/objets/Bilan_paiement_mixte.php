<?php

$sql4='SELECT ticketdecaisse.uuid_ticket, paiement_mixte.carte, paiement_mixte.espece, paiement_mixte.cheque, paiement_mixte.virement FROM ticketdecaisse
       INNER JOIN paiement_mixte ON paiement_mixte.uuid_ticket=ticketdecaisse.uuid_ticket 
       WHERE date_achat_dt LIKE "'.$format_us.'%"';
$sth4 = $db->query($sql4);
$results = $sth4->fetchAll();

$carte = 0;
$cheque = 0;
$espece = 0;
$virement = 0;

foreach($results as $v):

    $carte=$carte+$v['carte'];
    $cheque=$cheque+$v['cheque'];
    $espece=$espece+$v['espece'];
    $virement=$virement+$v['virement']; 

endforeach;



?>