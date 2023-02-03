<?php

require('actions/db.php');

$sql = 'ALTER TABLE `ticketdecaisse` ADD `date_achat_dt` DATETIME NULL AFTER `date_achat`';
$sth = $db -> query($sql);

$sql1 = 'SELECT * FROM ticketdecaisse';
$sth1 = $db -> query($sql1);
$results = $sth1->fetchAll();

foreach($results as $v):
    $format_fr = explode(' ',$v['date_achat']);
    $format_fr_date = $format_fr[0];
    
    if(isset($format_fr[1])):
        $format_us_array = [implode('/',array_reverse  (explode('/',$format_fr_date))), $format_fr[1]];
        $format_us = implode(' ', $format_us_array);
        
    else:
        $format_us = implode('/',array_reverse  (explode('/',$format_fr_date)));
        
    endif;
    
    $dateheure=new DateTime($format_us, new DateTimeZone('Europe/Paris'));
    $dateheure=$dateheure->format('Y-m-d G:i');
    
    $sql2 = 'UPDATE ticketdecaisse SET date_achat_dt = "'.$dateheure.'" WHERE id_ticket = '.$v['id_ticket'].'';
    $sth2 = $db->query($sql2);

endforeach;

?>