<?php require('../db.php');

if(isset($_GET['id']) AND !empty($_GET['id'])){
    
    //L'id de l'objet en paramètre
    $idOfTheObjet = $_GET['id'];
    
    //Vérifier si l'objet existe
    $checkIfObjetExists = $db->prepare('SELECT id FROM ticketdecaissetemp WHERE id = ?');
    $checkIfObjetExists->execute(array($idOfTheObjet));
    
    If($checkIfObjetExists->rowCount() > 0){
        
        $deleteThisObjet = $db->prepare('DELETE FROM ticketdecaissetemp WHERE id = ?');
        $deleteThisObjet->execute(array($idOfTheObjet));
            
        //Rediriger l'utilisateur vers la page objetsvendus
        if(isset($_GET['id_modif'])):
            header('location: ../../objetsVendus.php?id_temp_vente='.$_GET['id_temp_vente'].'&id_modif='.$_GET['id_modif'].'&modif='.$_GET['modif'].'#tc');
        else:
            header('location: ../../objetsVendus.php?id_temp_vente='.$_GET['id_temp_vente'].'&modif='.$_GET['modif'].'#tc');
        endif;
        
    }else{
        echo 'Aucun objet n\'a été trouvé';
    }
    
}else{
    echo 'Aucun objet n\'a été trouvé';
}

?>