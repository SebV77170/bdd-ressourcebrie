<?php

require('actions/db.php');

if(isset($_POST['ajout'])){

//Obtenir un timestamp avec le fuseau horaire parisien    

try {
    $date_heure_debutvente = new DateTimeImmutable('now', new DateTimeZone('europe/paris'));
} catch (Exception $e) {
    echo $e->getMessage();
    exit(1);
}

$date_heure_debutvente_TS = $date_heure_debutvente->format('U');

//Obtenir la date et l'heure correspondante.

$date_heure_debutvente_Date = $date_heure_debutvente->format('d-m G:i:s');

//Obtenir le nom du vendeur

$idvendeur = $_SESSION['id'];

//On insère la nouvelle vente dans la db vente.

$insertDate = $db -> prepare('INSERT INTO vente(date, dateheure, id_vendeur) VALUE (?,?,?)');
$insertDate->execute(array($date_heure_debutvente_TS,$date_heure_debutvente_Date, $idvendeur));

// Pour rediriger vers la nouvelle vente en cours dès qu'on clique sur +

$idvente = $db -> prepare('SELECT id_temp_vente FROM vente WHERE date = ?');
$idvente -> execute(array($date_heure_debutvente_TS));
$id = $idvente -> fetch(PDO::FETCH_ASSOC);
$id = $id['id_temp_vente'];

header('location:objetsVendus.php?id_temp_vente='.$id.'');

}

?>