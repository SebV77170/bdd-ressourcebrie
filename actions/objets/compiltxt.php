<?php

require '../../app/bootstrap.php';

$pdo = get_pdo();

$compil = new app\compil_tickets($_GET['date']);

$file = $compil->compilFile();


?>