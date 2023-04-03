<?php
var_dump($_POST);

if(isset($_POST['modifprix'])):
    if(!empty($_POST['prix']) AND $_POST['prix']>=0):
        //on récupère les infos de l'objet
        $sql1='SELECT * FROM ticketdecaissetemp WHERE id=?';
        $sth1=$db->prepare($sql1);
        $sth1->execute(array($_POST['idobjet']));
        $infoobjet=$sth1->fetch();

        $prixOfObjet = currencyToDecimal($_POST['prix'])*100;

        //on calcule le nouveau prix total
        $nouveautotal=$infoobjet['nbr']*$prixOfObjet;

        //on insère dans la db ticketdecaissetemp
        $sql='UPDATE ticketdecaissetemp SET prix=?, prixt=? WHERE id=?';
        $sth=$db->prepare($sql);
        $sth->execute(array($prixOfObjet, $nouveautotal, $_POST['idobjet']));



    endif;
endif;