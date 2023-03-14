<?php
require('../db.php');

function dateexplode($datetime){
    $date = explode(' ', $datetime);
    return($date[0]);
}

$sth = $db->prepare('SELECT date_achat FROM ticketdecaisse GROUP BY date_achat');
$sth -> execute();

$touteslesdates = $sth -> fetchAll(PDO::FETCH_FUNC, 'dateexplode');

$touteslesdatessansredondance = array_unique($touteslesdates);

print_r($touteslesdatessansredondance);

    foreach($touteslesdatessansredondance as $k => $v){
    $date_actuelle = $v;
    // Format fr => format us
    $format_us = implode('/',array_reverse  (explode('/',$date_actuelle)));
    
    //transforme en timestamp
    $timestamp = strtotime($format_us);
    
    $where2 = 'WHERE date = "'.$date_actuelle.'"';
    require('getPoidsBilan.php');
    if(isset($poids_total_obj_collecte['poids_total'])){
        $poids=$poids_total_obj_collecte['poids_total'];
    }else{
        $poids = 0;
    }
    
    $paiement = '';
    require('getTotalTicket.php');
    if(isset($prix_total_ticket['prix_total'])){
        $prix_total_journee = $prix_total_ticket['prix_total'];
    }else{
        $prix_total_journee = 0;
    }
    
    $paiement = 'AND (moyen_paiement = "cheque" )';
    require('getTotalTicket.php');
    if(isset($prix_total_ticket['prix_total'])){
        $prix_total_journee_cheque = $prix_total_ticket['prix_total'];
    }else{
        $prix_total_journee_cheque = 0;
    }
    
    
    $paiement = 'AND (moyen_paiement = "espece" )';
    require('getTotalTicket.php');
    if(isset($prix_total_ticket['prix_total'])){
        $prix_total_journee_espece = $prix_total_ticket['prix_total'];
    }else{
        $prix_total_journee_espece = 0;
    }
    
    $paiement = 'AND (moyen_paiement = "carte" )';
    require('getTotalTicket.php');
    if(isset($prix_total_ticket['prix_total'])){
        $prix_total_journee_carte = $prix_total_ticket['prix_total'];
    }else{
        $prix_total_journee_carte = 0;
    }
    
    $sth2 = $db -> prepare('SELECT id_ticket FROM ticketdecaisse WHERE (date_achat LIKE "'.$date_actuelle.'%")');
    $sth2 -> execute();
    $toutelesventesdujour = $sth2 -> fetchAll(PDO::FETCH_ASSOC);
    $nombre_vente = count($toutelesventesdujour);
    
    $sth1 = $db -> prepare('INSERT into bilan (date, timestamp, nombre_vente, poids, prix_total, prix_total_espece, prix_total_cheque, prix_total_carte) VALUES(?,?,?,?,?,?,?,?)');
    $sth1 -> execute(array($date_actuelle, $timestamp, $nombre_vente, $poids, $prix_total_journee, $prix_total_journee_espece, $prix_total_journee_cheque, $prix_total_journee_carte));
}


?>