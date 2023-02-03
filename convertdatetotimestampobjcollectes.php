<?php
require('actions/db.php');

$sth1 = $db -> prepare('SELECT * FROM objets_collectes');
$sth1 -> execute();

$result = $sth1->fetchAll(PDO::FETCH_ASSOC);

foreach($result as $v){
    
    // Format fr => format us
    $format_fr = $v['date'];
    $format_us = implode('/',array_reverse  (explode('/',$format_fr)));
    
    //transforme en timestamp
    $newdatetimestamp = strtotime($format_us);
    
    
    //Met à jour la DB
    $sth2 = $db ->  prepare('UPDATE objets_collectes
                    SET timestamp = ?
                    WHERE id = ?');
    $sth2 -> execute(array(
                            $newdatetimestamp,
                            $v['id'],
                           ));
}
?>