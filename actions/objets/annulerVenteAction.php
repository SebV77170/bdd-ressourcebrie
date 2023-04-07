<?php require('../db.php');
require('../users/securityAction.php');

$supprFromTCTemp = $db -> prepare('DELETE FROM ticketdecaissetemp WHERE id_temp_vente = ?');
$supprFromTCTemp -> execute(array($_GET['id_temp_vente']));

$supprFromDbVente = $db -> prepare('DELETE FROM vente WHERE id_temp_vente = ?');
$supprFromDbVente -> execute(array($_GET['id_temp_vente']));

$getDBvente = $db -> prepare('SELECT * FROM vente WHERE id_vendeur = ?');
$getDBvente -> execute(array($_SESSION['id']));

$DBvente = $getDBvente -> fetch(PDO::FETCH_ASSOC);

if(!empty($DBvente)){
header('location:../../objetsVendus.php?id_temp_vente='.$DBvente['id_temp_vente'].'&modif='.$DBvente['modif'].'#tc');
}else{
header('location:../../accueil_vente.php');

}

?>