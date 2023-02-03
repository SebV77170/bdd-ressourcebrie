<?php require('actions/db.php');
require('actions/objets/currencyToDecimalFct.php');



//Validation du formulaire
if(isset($_POST['validate'])){
    
    //Vérifier si les champs sont remplis
    if(!empty($_POST['prix'])){
        
        //Les données à faire passer dans la requête
        $new_nom = htmlspecialchars($_POST['nom']);
        $new_cat = $_POST['type'];
        $idOfObjet = $_GET['id_depot'];
        $new_prix = currencyToDecimal($_POST['prix'])*100;
        
        //Modifier les informations de la question qui possède l'id rentré en paramètre dans l'URL
        $editObjetOnDB = $db->prepare('UPDATE objets_vendus SET nom = ?, categorie = ?, prix = ? WHERE id_depot = ?');
        $editObjetOnDB->execute(array($new_nom, $new_cat, $new_prix, $idOfObjet));
        
        //Redirection vers la page des questions de l'utilisateur
        header('location: objetsVendus.php');
        
    }else{
        $errorMsg = 'Veuillez compléter tous les champs...';
    }
}

?>