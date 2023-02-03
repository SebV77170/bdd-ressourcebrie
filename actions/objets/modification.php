<?php
session_start();
require('../db.php');

if(isset($_GET['id_ticket'])):

    $idTicket = $_GET['id_ticket'];
    $sql='SELECT * FROM ticketdecaisse WHERE id_ticket ='.$idTicket.'';
    $sth=$db->query($sql);
    $results=$sth->fetch();
    $count=count($results);
    
    if($count>0):
        $sql1='SELECT * FROM objets_vendus WHERE id_ticket ='.$idTicket.'';
        $sth1=$db->query($sql1);
        $objets=$sth1->fetchAll();
        
        //Obtenir un timestamp avec le fuseau horaire parisien    

        try {
            $date_heure_debutvente = new DateTimeImmutable($results['date_achat_dt'], new DateTimeZone('europe/paris'));
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
        
        foreach($objets as $v):
            $sql2='INSERT INTO ticketdecaissetemp (id_temp_vente,nom_vendeur,id_vendeur,nom,categorie,souscat,prix) VALUES (?,?,?,?,?,?,?)';
            $sth2=$db->prepare($sql2);
            $sth2->execute(array($id,$_SESSION['nom'],$idvendeur,$v['nom'],$v['categorie'],$v['souscat'],$v['prix']));
            
            $sql3='DELETE FROM objets_vendus WHERE id_ticket='.$idTicket.'';
            $sth3=$db->query($sql3);
        endforeach;
        
        $lien='../../'.$results['lien'].'';
        unlink($lien);
        
        $sql4='DELETE FROM ticketdecaisse WHERE id_ticket ='.$idTicket.'';
        $sth4=$db->query($sql4);
        
        header('location:../../objetsVendus.php?id_temp_vente='.$id.'');
        
    else:
    $message = 'Il n\'y a pas de vente avec cet ID';
    header('location:../../erreur.php?message='.$message.'');
    endif;

else:
    $message = 'l\ID de la vente n\'a pas été rentrée.';
    header('location:../../erreur.php?message='.$message.'');
endif;

?>