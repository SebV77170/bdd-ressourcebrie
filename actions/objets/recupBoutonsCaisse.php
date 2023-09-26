<?php

//On récupère toutes les différentes catégories présente dans la db boutons_ventes
$sql='SELECT DISTINCT id_cat, id_souscat FROM boutons_ventes';
$sth=$db->query($sql);
$id_cat=$sth->fetchAll(PDO::FETCH_ASSOC);

//On récupère les informations des boutons en joignant les tables boutons_ventes (id_souscat) et catégories (id)
foreach($id_cat as $k=>$v):
    $sql='SELECT * FROM boutons_ventes
            INNER JOIN categories ON boutons_ventes.id_souscat=categories.id
            WHERE boutons_ventes.id_cat=?
            ORDER BY boutons_ventes.nom ASC';
    $sth=$db->prepare($sql);
    $sth->execute(array($v['id_cat']));
    //On obtient un tableau indexé par id_cat, dans lequel il y a tous les boutons mélangés par rapport aux souscat
    $boutons[$v['id_cat']]=$sth->fetchAll(PDO::FETCH_ASSOC);
endforeach;

//On réarrange le tableau $boutons en indexant d'abord par catégorie, puis dans chaque catégorie on trie les sous catégories
//de sorte qu'on puisse avoir un tableau $newbouton[categories][souscat] et tous les boutons de cette souscat.
foreach($boutons as $k=>$v):
    foreach($v as $v1=>$v2):
        $newboutons[$k][$v2['category']][]=$v2;
    endforeach;
endforeach;


//On récupère les catégories pour pouvoir afficher les noms en titres dans objetsVendus.php
foreach($id_cat as $k=>$v):
    $sql='SELECT DISTINCT category, color FROM categories
            INNER JOIN boutons_ventes ON boutons_ventes.id_cat=categories.id
            WHERE boutons_ventes.id_cat=?';
    $sth=$db->prepare($sql);
    $sth->execute(array($v['id_cat']));
    $category[$v['id_cat']]=$sth->fetchAll(PDO::FETCH_ASSOC);
endforeach;





        

