<?php require('actions/db.php'); 
$moment = time();
?>
<?php if(isset($_POST['validate'])){
    
    if(!empty($_POST['type']) AND !empty($_POST['poids']) AND !empty($_POST['flux']) AND !empty($_POST['souscategorie'])){
        
        $objet_nom = $_POST['nom'];
        
        $objet_date_insertion = new DateTime('now', new DateTimeZone('Europe/paris'));
        $objet_date_insertion = $objet_date_insertion->format('Y/m/d G:i');
        
        $objet_type = $_POST['type'];
        $objet_souscat = $_POST['souscategorie'];
        $objet_poids = $_POST['poids'];
        $objet_flux = $_POST['flux'];
        $vendu = 0;
        $saisisseur = ''.$_SESSION['nom'].' '.$_SESSION['prenom'].'';
        
        $insertObjet = $db->prepare('INSERT INTO objets_collectes(nom, categorie, souscat, poids, date, timestamp, vendu, flux, saisipar)VALUES(?,?,?,?,?,?,?,?,?)');
        $insertObjet->execute(array($objet_nom, $objet_type, $objet_souscat, $objet_poids, $objet_date_insertion, $moment, $vendu, $objet_flux, $saisisseur));
        
        require('actions/objets/update_db_bilan.php');
        
        $message = 'L\'objet a bien été inséré dans la database';
        
        if(!empty($_POST['reparation'])){
            
            $sql='SELECT id FROM objets_collectes WHERE timestamp='.$moment.'';
            $sth = $db->query($sql);
            
            $result=$sth->fetch(PDO::FETCH_ASSOC);
            
            header('location:typereparation.php?id='.$result['id'].'');
            
        }
        
    }else{
        
        $message = 'Votre saisie n\'a pas été prise en compte, veuillez remplir tous les champs svp';
        
    }
    
}

?>