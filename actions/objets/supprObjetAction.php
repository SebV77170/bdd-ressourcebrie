<?php require('../db.php');

if(isset($_GET['id']) AND !empty($_GET['id'])){
    
    //L'idée de la question en paramètre
    $idOfTheObjet = $_GET['id'];
    
    //Vérifier si la question existe
    $checkIfObjetExists = $db->prepare('SELECT id FROM objets_collectes WHERE id = ?');
    $checkIfObjetExists->execute(array($idOfTheObjet));
    
    If($checkIfObjetExists->rowCount() > 0){
        
        $deleteThisObjet = $db->prepare('DELETE FROM objets_collectes WHERE id = ?');
        $deleteThisObjet->execute(array($idOfTheObjet));
            
        //Rediriger l'utilisateur vers sa page initiale.
        if($_GET['from'] == 'depot'){
        header('location: ../../depot.php');
        }else{
            header('location: ../../BilanObjetsCollectes.php');
        }
        
    }else{
        echo 'Aucun objet n\'a été trouvé';
    }
    
}else{
    echo 'Aucun objet n\'a été trouvé';
}

?>