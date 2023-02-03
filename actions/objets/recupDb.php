<?php require('actions/db.php'); ?>

<?php

if (isset ($_GET['tri'])){
    $tri=$_GET['tri'];
}else{
    if(isset($tridepot)){
        $tri = $tridepot;
    }else{
        $tri = 'categorie';
    }
}

if(!isset($limit)){
    $limit='';
}

if(!isset($where1)){
    $where1='';
}

$getAllObjets = $db->prepare('SELECT id, nom, categorie, souscat, poids, date, timestamp, flux, saisipar FROM objets_collectes '.$where1.' ORDER BY '.$tri.''.$limit.'');
$getAllObjets->execute();

$getObjets = $getAllObjets->fetchAll();

?>
