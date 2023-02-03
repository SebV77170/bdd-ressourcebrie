<?php

require('actions/db.php');

//Vérifier si l'id de la question est bien passée en paramètre dans l'URL
if(isset($_GET['id']) AND !empty($_GET['id'])){
    
    $idOfObjet = $_GET['id'];
    
    //Vérifier si la question existe
    $checkIfObjetExists = $db->prepare('SELECT * FROM objets_collectes WHERE id = ?');
    $checkIfObjetExists->execute(array($idOfObjet));
    
    if($checkIfObjetExists->rowCount() > 0){
        
        //Récuperer les données de la question
        $objetInfos = $checkIfObjetExists->fetch();
            
            $flux = $objetInfos['flux'];
            $objet = $objetInfos['nom'];
            $type = $objetInfos['categorie'];
            $souscat = $objetInfos['souscat'];
            $poids = $objetInfos['poids'];
        
    }else{
        $errorMsg = 'Aucune question n\'a été trouvée...';
    }
    
}else{
    $errorMsg = 'Aucune question n\'a été trouvée...';
}