<?php

$date_actuelle = new DateTime('now', new DateTimeZone('Europe/paris'));
$date_actuelle = $date_actuelle->format('Y/m/d G:i');

$format_us = new DateTime('now', new DateTimeZone('Europe/paris'));
$format_us = $format_us->format('Y-m-d');
$timestamp = strtotime($date_actuelle);

$date_actuelle2 = new DateTime('now', new DateTimeZone('Europe/paris'));
$date_actuelledmY = $date_actuelle2->format('d/m/Y');


    
$where2 = 'WHERE date LIKE "'.$format_us.'%"';
require('actions/objets/getPoidsBilan.php');
if(isset($poids_total_obj_collecte['poids_total'])){
    $poids=$poids_total_obj_collecte['poids_total'];
}else{
    $poids = 0;
}

$paiement = '';
require('actions/objets/getTotalTicket.php');
if(isset($prix_total_ticket['prix_total'])){
    $prix_total_journee = $prix_total_ticket['prix_total'];
}else{
    $prix_total_journee = 0;
}

$paiement = 'AND (moyen_paiement = "cheque" )';
require('actions/objets/getTotalTicket.php');
if(isset($prix_total_ticket['prix_total'])){
    $prix_total_journee_cheque = $prix_total_ticket['prix_total'];
}else{
    $prix_total_journee_cheque = 0;
}


$paiement = 'AND (moyen_paiement = "espèces" )';
require('actions/objets/getTotalTicket.php');
if(isset($prix_total_ticket['prix_total'])){
    $prix_total_journee_espece = $prix_total_ticket['prix_total'];
}else{
    $prix_total_journee_espece = 0;
}

$paiement = 'AND (moyen_paiement = "carte" )';
require('actions/objets/getTotalTicket.php');
if(isset($prix_total_ticket['prix_total'])){
    $prix_total_journee_carte = $prix_total_ticket['prix_total'];
}else{
    $prix_total_journee_carte = 0;
}

$paiement = 'AND (moyen_paiement = "virement" )';
require('actions/objets/getTotalTicket.php');
if(isset($prix_total_ticket['prix_total'])){
    $prix_total_journee_virement = $prix_total_ticket['prix_total'];
}else{
    $prix_total_journee_virement = 0;
}

require('actions/objets/Bilan_paiement_mixte.php');

$prix_total_journee_carte = $prix_total_journee_carte+$carte;
$prix_total_journee_virement = $prix_total_journee_virement+$virement;
$prix_total_journee_espece = $prix_total_journee_espece+$espece;
$prix_total_journee_cheque = $prix_total_journee_cheque+$cheque;
    
$sth2 = $db -> prepare('SELECT id_ticket FROM ticketdecaisse WHERE (date_achat_dt LIKE "'.$format_us.'%")');
$sth2 -> execute();
$toutelesventesdujour = $sth2 -> fetchAll(PDO::FETCH_ASSOC);
$nombre_vente = count($toutelesventesdujour);

$sth3 = $db -> prepare('SELECT id FROM bilan WHERE date = "'.$date_actuelledmY.'"');
$sth3 -> execute();
$activitedujour = $sth3 -> fetchAll();
$activite = count($activitedujour);

if($activite == 0){
    $sth1 = $db -> prepare('INSERT into bilan (date, timestamp, nombre_vente, poids, prix_total, prix_total_espece, prix_total_cheque, prix_total_carte, prix_total_virement) VALUES(?,?,?,?,?,?,?,?,?)');
    $sth1 -> execute(array($date_actuelledmY, $timestamp, $nombre_vente, $poids, $prix_total_journee, $prix_total_journee_espece, $prix_total_journee_cheque, $prix_total_journee_carte, $prix_total_journee_virement));
}else{
    $sth1 = $db -> prepare("UPDATE bilan
                           SET nombre_vente = '$nombre_vente',
                            poids = '$poids',
                            prix_total = '$prix_total_journee',
                            prix_total_espece = '$prix_total_journee_espece',
                            prix_total_cheque = '$prix_total_journee_cheque',
                            prix_total_carte = '$prix_total_journee_carte',
                            prix_total_virement = '$prix_total_journee_virement'
                           WHERE date = '$date_actuelledmY'");
    
    $sth1 -> execute();
}
?>