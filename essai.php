<?php

require 'app/bootstrap.php';

dd($_SERVER);


$explode1=explode('?',$_SERVER['REQUEST_URI']);
$explode2=explode('&', $explode1[1]);
$explode3=explode('=', $explode2[0]);
$newPrix = $explode3[1]-5;
$implode3=implode('=',[$explode3[0],$newPrix]);
$implode2=implode('&', [$implode3,$explode2[1],$explode2[2],$explode2[3],$explode2[4]]);
$implode1=implode('?',[$explode1[0],$implode2]);
$newURL = ''.$implode1.'&ra=true';

?>

<a href=<?=$newURL?> class="stdbouton">valider avec reduction</a>




?>