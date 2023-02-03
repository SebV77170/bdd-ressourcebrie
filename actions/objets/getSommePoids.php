<?php require('actions/db.php'); ?>

<?php

$touteLesSommes = $db -> prepare('SELECT categorie, SUM(poids) AS poids_total_par_cat FROM objets_collectes GROUP BY categorie');
$touteLesSommes -> execute();

$LesSommes = $touteLesSommes -> fetchAll();

if(!isset($where2)){
    $where2='';
}

$sommeTotale = $db -> prepare('SELECT SUM(poids) AS poids_total FROM objets_collectes '.$where2.'');
$sommeTotale->execute();

$poids_total_obj_collecte = $sommeTotale -> fetch();

?>