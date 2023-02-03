<?php

require('../db.php');

$date='2023-01-07';

$sql ='SELECT id_ticket FROM ticketdecaisse WHERE date_achat_dt LIKE ? %';
$sth=$db->prepare($sql);
$sth->execute(array($date));

$results = $sth->fetchAll();

$fichier=fopen("../../compilation/resultat/compil.txt", 'c+b');

for($i = 0; $i < 125; ++$i):
    if(file_exists('../../compilation/ticket'.$i.'.txt')):
        
        $fichier1=file_get_contents('../../compilation/ticket'.$i.'.txt');

        $contenu1=''.$fichier1.'
        
        
        -------------------------------------------
        
        
        ';
        
        fwrite($fichier, $contenu1);
    else:
        continue;    
    endif;
endfor;

fclose($fichier);
?>