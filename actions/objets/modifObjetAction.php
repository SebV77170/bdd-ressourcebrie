<?php require('actions/db.php'); 



//Validation du formulaire
if(isset($_POST['validate'])){
    
    //Vérifier si les champs sont remplis
    if(!empty($_POST['poids'])){
        
        //Les données à faire passer dans la requête
        $new_nom = htmlspecialchars($_POST['nom']);
        $new_poids = htmlspecialchars($_POST['poids']);
        $new_cat = $_POST['type'];
        $new_souscat = $_POST['souscategorie'];
        $new_flux = $_POST['flux'];
        $idOfObjet = $_GET['id'];
        
        //Modifier les informations de l'objet qui possède l'id rentré en paramètre dans l'URL
        $editObjetOnDB = $db->prepare('UPDATE objets_collectes SET flux = ?, nom = ?, categorie = ?,souscat = ?, poids = ? WHERE id = ?');
        $editObjetOnDB->execute(array($new_flux, $new_nom, $new_cat, $new_souscat, $new_poids, $idOfObjet));
        
        //Redirection vers la page des dépots ou vers les bilans, en fonction de la variable from passée en GET
        if($_GET['from']=='depot'){
            header('location: depot.php');
        }else{
            header('location: BilanObjetsCollectes.php');
        }
        
    }else{
        $errorMsg = 'Veuillez compléter tous les champs...';
    }
}

?>