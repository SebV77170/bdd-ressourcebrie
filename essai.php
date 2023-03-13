<?php

$res = file_get_contents('tickets/Ticket171.txt', 'c+b');

$tab = explode("\r",$res);

$tab[7]=' Date et heure : 13/02/2023 00:00';

$res1=implode("\r",$tab);

$file = file_put_contents('tickets/Ticket171.txt',$res1);

//for($i=1; $i<=8; $i++):
//    echo 'appel '.$i.' : '.fgets($res).' </br>';
//endfor;



?>