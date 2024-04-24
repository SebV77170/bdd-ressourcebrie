<?php
if(isset($_POST['modifnom'])):
    //on récupère les infos de l'objet
    $sql1='SELECT * FROM ticketdecaissetemp WHERE id=?';
    $sth1=$db->prepare($sql1);
    $sth1->execute(array($_POST['idobjet']));
    $infoobjet=$sth1->fetch();

    //on update la db ticketdecaissetemp
    $sql='UPDATE ticketdecaissetemp SET nom=? WHERE id=?';
    $sth=$db->prepare($sql);
    $sth->execute(array($_POST['nom'], $_POST['idobjet']));
endif;