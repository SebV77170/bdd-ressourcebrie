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

$insertDate = $db -> prepare('INSERT INTO vente(date, dateheure, id_vendeur,modif) VALUE (?,?,?,0)');
$insertDate->execute(array($date_heure_debutvente_TS,$date_heure_debutvente_Date, $idvendeur));

// Pour rediriger vers la nouvelle vente en cours dès qu'on clique sur +

$sth = $db -> prepare('SELECT * FROM vente WHERE date = ?');
$sth -> execute(array($date_heure_debutvente_TS));
$result = $sth -> fetch(PDO::FETCH_ASSOC);
$id = $result['id_temp_vente'];
$modif = $result['modif'];

header('location:objetsVendus.php?id_temp_vente='.$id.'&modif='.$modif.'#tc');

}

?>