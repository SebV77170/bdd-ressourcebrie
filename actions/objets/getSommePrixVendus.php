<?php require('actions/db.php'); ?>

<?php

$touteLesSommes = $db -> prepare('SELECT categorie, SUM(prix) AS prix_total_par_cat FROM objets_vendus GROUP BY categorie');
$touteLesSommes -> execute();

$LesSommes = $touteLesSommes -> fetchAll();

$sommeTotale = $db -> prepare('SELECT SUM(prix) AS prix_total FROM objets_vendus');
$sommeTotale->execute();

$prix_total_obj_collecte = $sommeTotale -> fetch();



?>