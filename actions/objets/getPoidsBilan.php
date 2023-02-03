<?php

if(!isset($where2)){
    $where2='';
}

$sommeTotale = $db -> prepare('SELECT SUM(poids) AS poids_total FROM objets_collectes '.$where2.'');
$sommeTotale->execute();

$poids_total_obj_collecte = $sommeTotale -> fetch();

?>