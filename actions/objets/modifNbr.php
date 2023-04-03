<?php
if(isset($_POST['modifnbr'])):
    //on récupère les infos de l'objet
    $sql1='SELECT * FROM ticketdecaissetemp WHERE id=?';
    $sth1=$db->prepare($sql1);
    $sth1->execute(array($_POST['idobjet']));
    $infoobjet=$sth1->fetch();

    //on calcule le nouveau prix total
    $nouveautotal=$_POST['nbr']*$infoobjet['prix'];

    //on update la db ticketdecaissetemp
    $sql='UPDATE ticketdecaissetemp SET nbr=?, prixt=? WHERE id=?';
    $sth=$db->prepare($sql);
    $sth->execute(array($_POST['nbr'], $nouveautotal, $_POST['idobjet']));
endif;