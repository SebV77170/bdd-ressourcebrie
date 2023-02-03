<?php
require('actions/db.php');

$sth1 = $db -> prepare('SELECT * FROM objets_vendus');
$sth1 -> execute();

$result = $sth1->fetchAll(PDO::FETCH_ASSOC);

foreach($result as $v){
    
    $format_fr = $v['date_achat'];
    $date = explode(' ', $format_fr);
    if (isset($date['1'])){
        $heure = $date['1'];
        $format_us = implode('-',array_reverse  (explode('/',$date['0'])));
        $datetime_us = "$format_us $heure";
        $newdatetimestamp = strtotime($datetime_us);
    }else{
        $format_us = implode('/',array_reverse  (explode('/',$format_fr)));
        $newdatetimestamp = strtotime($format_us);
    }
    
    
    //Met à jour la DB
    $sth2 = $db ->  prepare('UPDATE objets_vendus
                    SET timestamp = ?
                    WHERE id_achat = ?');
    $sth2 -> execute(array(
                            $newdatetimestamp,
                            $v['id_achat'],
                           ));
}
?>