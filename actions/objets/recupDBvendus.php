<?php require('actions/db.php'); ?>

<?php

if (isset ($_GET['tri'])){
    $tri=$_GET['tri'];
}else{
$tri = 'categorie';
}

$getAllObjets = $db->prepare('SELECT nom, nom_vendeur, categorie, souscat, date_achat, timestamp, prix FROM objets_vendus ORDER BY '.$tri.'');
$getAllObjets->execute();

$getObjets = $getAllObjets->fetchAll();

?>