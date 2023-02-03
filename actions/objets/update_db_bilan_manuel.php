<?php


require('../db.php');

if(isset($_GET['date'])):

$date_actuelle = $_GET['date'];
// Format fr => format us
$format_us = implode('-',array_reverse  (explode('/',$date_actuelle)));
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

require('Bilan_paiement_mixte.php');

$prix_total_journee_carte = $prix_total_journee_carte+$carte;
$prix_total_journee_espece = $prix_total_journee_espece+$espece;
$prix_total_journee_cheque = $prix_total_journee_cheque+$cheque;
    
$sth2 = $db -> prepare('SELECT id_ticket FROM ticketdecaisse WHERE (date_achat_dt LIKE "'.$format_us.'%")');
$sth2 -> execute();
$toutelesventesdujour = $sth2 -> fetchAll(PDO::FETCH_ASSOC);
$nombre_vente = count($toutelesventesdujour);

$sth3 = $db -> prepare('SELECT id FROM bilan WHERE date = "'.$date_actuelle.'"');
$sth3 -> execute();
$activitedujour = $sth3 -> fetchAll();
$activite = count($activitedujour);

if($activite == 0){
    $sth1 = $db -> prepare('INSERT into bilan (date, timestamp, nombre_vente, poids, prix_total, prix_total_espece, prix_total_cheque, prix_total_carte) VALUES(?,?,?,?,?,?,?,?)');
    $sth1 -> execute(array($date_actuelle, $timestamp, $nombre_vente, $poids, $prix_total_journee, $prix_total_journee_espece, $prix_total_journee_cheque, $prix_total_journee_carte));
}else{
    $sth1 = $db -> prepare("UPDATE bilan
                           SET nombre_vente = '$nombre_vente',
                            poids = '$poids',
                            prix_total = '$prix_total_journee',
                            prix_total_espece = '$prix_total_journee_espece',
                            prix_total_cheque = '$prix_total_journee_cheque',
                            prix_total_carte = '$prix_total_journee_carte'
                           WHERE date = '$date_actuelle'");
    
    $sth1 -> execute();
}
header('location:../../bilanJournalier.php');
endif;
?>