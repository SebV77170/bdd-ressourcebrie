<?php

require('actions/db.php');

//Vérifier si l'id de la question est bien passée en paramètre dans l'URL
if(isset($_GET['id_depot']) AND !empty($_GET['id_depot'])){
    
    $idOfObjet = $_GET['id_depot'];
    
    //Vérifier si la question existe
    $checkIfObjetExists = $db->prepare('SELECT * FROM objets_vendus WHERE id_depot = ?');
    $checkIfObjetExists->execute(array($idOfObjet));
    
    if($checkIfObjetExists->rowCount() > 0){
        
        //Récuperer les données de la question
        $objetInfos = $checkIfObjetExists->fetch();
        
            $objet = $objetInfos['nom'];
            $type = $objetInfos['categorie'];
            $poids = $objetInfos['poids'];
            $prix = $objetInfos['prix'];
        
    }else{
        $errorMsg = 'Aucune question n\'a été trouvée...';
    }
    
}else{
    $errorMsg = 'Aucune question n\'a été trouvée...';
}