<?php


$format_us = (explode(' ',$date))[0];
// Format fr => format us
$date_actuelle = implode('/',array_reverse  (explode('-',$format_us)));
//transforme en timestamp
$timestamp = strtotime($format_us);
    
$where2 = 'WHERE date LIKE "'.$format_us.'%"';
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

$paiement = 'AND (moyen_paiement = "virement" )';
require('getTotalTicket.php');
if(isset($prix_total_ticket['prix_total'])){
    $prix_total_journee_virement = $prix_total_ticket['prix_total'];
}else{
    $prix_total_journee_virement = 0;
}

require('Bilan_paiement_mixte.php');

$prix_total_journee_carte = $prix_total_journee_carte+$carte;
$prix_total_journee_espece = $prix_total_journee_espece+$espece;
$prix_total_journee_cheque = $prix_total_journee_cheque+$cheque;
$prix_total_journee_virement = $prix_total_journee_virement+$virement;
    
$sth2 = $db -> prepare('SELECT id_ticket FROM ticketdecaisse WHERE (date_achat_dt LIKE "'.$format_us.'%")');
$sth2 -> execute();
$toutelesventesdujour = $sth2 -> fetchAll(PDO::FETCH_ASSOC);
$nombre_vente = count($toutelesventesdujour);

$sth3 = $db -> prepare('SELECT id FROM bilan WHERE date = "'.$date_actuelle.'"');
$sth3 -> execute();
$activitedujour = $sth3 -> fetchAll();
$activite = count($activitedujour);

if($activite == 0){
    $sth1 = $db -> prepare('INSERT into bilan (date, timestamp, nombre_vente, poids, prix_total, prix_total_espece, prix_total_cheque, prix_total_carte, prix_total_virement) VALUES(?,?,?,?,?,?,?,?,?)');
    $sth1 -> execute(array($date_actuelle, $timestamp, $nombre_vente, $poids, $prix_total_journee, $prix_total_journee_espece, $prix_total_journee_cheque, $prix_total_journee_carte, $prix_total_journee_virement));
}else{
    $sth1 = $db -> prepare("UPDATE bilan
                           SET nombre_vente = '$nombre_vente',
                            poids = '$poids',
                            prix_total = '$prix_total_journee',
                            prix_total_espece = '$prix_total_journee_espece',
                            prix_total_cheque = '$prix_total_journee_cheque',
                            prix_total_carte = '$prix_total_journee_carte'
                            prix_total_virement = '$prix_total_journee_virement'
                           WHERE date = '$date_actuelle'");
    
    $sth1 -> execute();
}
?>