<?php

$sql='SELECT DISTINCT id_cat FROM boutons_ventes';
$sth=$db->query($sql);
$id_cat=$sth->fetchAll(PDO::FETCH_ASSOC);

foreach($id_cat as $k=>$v):

    $sql='SELECT * FROM boutons_ventes
            INNER JOIN categories ON boutons_ventes.id_cat=categories.id
            WHERE boutons_ventes.id_cat=?
            ORDER BY boutons_ventes.nom ASC';
    $sth=$db->prepare($sql);
    $sth->execute(array($v['id_cat']));
    $boutons[$k]=$sth->fetchAll(PDO::FETCH_ASSOC);
    

endforeach;

foreach($id_cat as $k=>$v):

    $sql='SELECT DISTINCT category, color FROM categories
            INNER JOIN boutons_ventes ON boutons_ventes.id_cat=categories.id
            WHERE boutons_ventes.id_cat=?';
    $sth=$db->prepare($sql);
    $sth->execute(array($v['id_cat']));
    $category[$k]=$sth->fetchAll(PDO::FETCH_ASSOC);

endforeach;





        

