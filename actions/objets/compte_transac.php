<?php

//On récupère les valeurs de la db compte_transac si elles existent
$sql1="SELECT * FROM compte_transac";
$sth1=$db->query($sql1);
$compte=$sth1->fetch();

//On regarde par rapport à la date du jour
$date=new DateTime("now");
$date=$date->format("Y-m-d");

//On effectue une boucle pour savoir s'il s'agit bien de la bonne date, et si ce n'est pas le cas, on reset le compteur.
//si la db est vide :
if($compte==FALSE):
    $value=1;
    
    $sql2="INSERT INTO compte_transac VALUES (?,?)";
    $sth2=$db->prepare($sql2);
    $sth2->execute(array($date,$value));
//on teste ensuite s'il s'agit de la bonne date
else:
    if($compte['date_transac']==$date):
        $value=$compte['compte'];
        $new_value=$value+1;
        $sql3="UPDATE compte_transac SET compte=?";
        $sth3=$db->prepare($sql3);
        $sth3->execute(array($new_value));
    else:
        $value=1;
        $sql3="UPDATE compte_transac SET date_transac=?, compte=?";
        $sth3=$db->prepare($sql3);
        $sth3->execute(array($date,$value));
    endif;
endif;

//On récupère les nouvelles valeurs de la db compte_transac
$sql4="SELECT * FROM compte_transac";
$sth4=$db->query($sql4);
$nouveau_compte=$sth4->fetch();
?>