<?php 
require('actions/db.php');

?>

<?php

    //on récupère les données du ticket de caisse modifié s'il existe
    if($_GET['modif']==1):
        $sql='SELECT * FROM modifticketdecaisse WHERE id_modif = '.$_GET['id_modif'].'';
        $sth=$db->query($sql);
        $ticketmodif=$sth->fetch();
    endif;

    //Pour vérifier si le formulaire a bien été cliqué

$prix=$_GET['prix'];

if(isset($_POST['validatecheque'])):
    
    if($_GET['modif']==0):

        date_default_timezone_set('Europe/Paris');
        $date_achat = new DateTime('now', new DateTimeZone('Europe/paris'));
        $date_achat = $date_achat->format('Y/m/d G:i');

    elseif($_GET['modif']==1):
        $date_achat = $ticketmodif['date_achat_dt'];
    
    endif;
        
    //On remplit la bdd ticketdecaisse
    
    $moyenDePaiement = "chèque";
    $nbrObjet = $_GET['nbrObjet'];
    $nomVendeur = $_SESSION['nom'];
    $idVendeur = $_SESSION['id'];
    $prenomVendeur = $_SESSION['prenom'];

    
    
    //pour cela on récupère le prix total
    $getPrixTotal = $db->prepare('SELECT SUM(prixt) AS prix_total FROM ticketdecaissetemp WHERE id_temp_vente = ?');
    $getPrixTotal -> execute(array($_GET['id_temp_vente']));

    $getTotal = $getPrixTotal->fetch();
    if($getTotal['prix_total']>=0):
        $getTotalEnEuros = $getTotal['prix_total'];
    else:
        //Pour adapter le coût de la réduction si elle est d'une valeur inférieure à celle prévue.
        $getTotalEnEuros=0;
        if($_GET['ra']=='trueClient'):
            $reduc=-($getTotal['prix_total']+500);
            $sql='UPDATE ticketdecaissetemp SET prix=? WHERE nom="reduction fid client" AND id_temp_vente=?';
        elseif($_GET['ra']=='trueBene'):
            $reduc=-($getTotal['prix_total']+1000);
            $sql='UPDATE ticketdecaissetemp SET prix=? WHERE nom="reduction fid bénévole" AND id_temp_vente=?';
        endif;
        $sth=$db->prepare($sql);
        $sth->execute(array($reduc,$_GET['id_temp_vente']));
endif;   

$lien = '...';
    
//On insère en fonction de s'il s'agit d'une modification ou pas
if($_GET['modif']==0):
    //On regarde aussi s'il y a une réduction à appliquer ou pas.
    if(isset($_GET['ra'])):
        if($_GET['ra']=='trueClient'):
            $insertDataDansTicketCaisse = $db->prepare('INSERT INTO ticketdecaisse(nom_vendeur, id_vendeur, date_achat_dt, nbr_objet, moyen_paiement, prix_total, lien, reducbene, reducclient, reducgrospanierclient, reducgrospanierbene) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $insertDataDansTicketCaisse->execute(array($nomVendeur, $idVendeur, $date_achat, $nbrObjet, $moyenDePaiement, $getTotalEnEuros, $lien, 0, 1, 0, 0));
        elseif($_GET['ra']=='trueBene'):
            $insertDataDansTicketCaisse = $db->prepare('INSERT INTO ticketdecaisse(nom_vendeur, id_vendeur, date_achat_dt, nbr_objet, moyen_paiement, prix_total, lien, reducbene, reducclient, reducgrospanierclient, reducgrospanierbene) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $insertDataDansTicketCaisse->execute(array($nomVendeur, $idVendeur, $date_achat, $nbrObjet, $moyenDePaiement, $getTotalEnEuros, $lien, 1, 0, 0, 0));
        elseif($_GET['ra']=='trueGrosPanierClient'):
            $insertDataDansTicketCaisse = $db->prepare('INSERT INTO ticketdecaisse(nom_vendeur, id_vendeur, date_achat_dt, nbr_objet, moyen_paiement, prix_total, lien, reducbene, reducclient, reducgrospanierclient, reducgrospanierbene) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $insertDataDansTicketCaisse->execute(array($nomVendeur, $idVendeur, $date_achat, $nbrObjet, $moyenDePaiement, $getTotalEnEuros, $lien, 0, 0, 1, 0));
        elseif($_GET['ra']=='trueGrosPanierBene'):
            $insertDataDansTicketCaisse = $db->prepare('INSERT INTO ticketdecaisse(nom_vendeur, id_vendeur, date_achat_dt, nbr_objet, moyen_paiement, prix_total, lien, reducbene, reducclient, reducgrospanierclient, reducgrospanierbene) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $insertDataDansTicketCaisse->execute(array($nomVendeur, $idVendeur, $date_achat, $nbrObjet, $moyenDePaiement, $getTotalEnEuros, $lien, 0, 0, 0, 1));
        else:
            $insertDataDansTicketCaisse = $db->prepare('INSERT INTO ticketdecaisse(nom_vendeur, id_vendeur, date_achat_dt, nbr_objet, moyen_paiement, prix_total, lien, reducbene, reducclient, reducgrospanierclient, reducgrospanierbene) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $insertDataDansTicketCaisse->execute(array($nomVendeur, $idVendeur, $date_achat, $nbrObjet, $moyenDePaiement, $getTotalEnEuros, $lien, 0, 0, 0, 0));
        endif;
    else:
        $insertDataDansTicketCaisse = $db->prepare('INSERT INTO ticketdecaisse(nom_vendeur, id_vendeur, date_achat_dt, nbr_objet, moyen_paiement, prix_total, lien, reducbene, reducclient, reducgrospanierclient, reducgrospanierbene) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
        $insertDataDansTicketCaisse->execute(array($nomVendeur, $idVendeur, $date_achat, $nbrObjet, $moyenDePaiement, $getTotalEnEuros, $lien, 0, 0, 0, 0));
    endif;
elseif($_GET['modif']==1):
    if(isset($_GET['ra'])):
        if($_GET['ra']=='trueClient'):
            $insertDataDansTicketCaisse = $db->prepare('INSERT INTO ticketdecaisse(id_ticket, nom_vendeur, id_vendeur, date_achat_dt, nbr_objet, moyen_paiement, prix_total, lien, reducbene, reducclient, reducgrospanierclient, reducgrospanierbene) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
            $insertDataDansTicketCaisse->execute(array($ticketmodif['id_ticket'], $nomVendeur, $idVendeur, $date_achat, $nbrObjet, $moyenDePaiement, $getTotalEnEuros, $lien, 0, 1, 0, 0));
        elseif($_GET['ra']=='trueBene'):
            $insertDataDansTicketCaisse = $db->prepare('INSERT INTO ticketdecaisse(id_ticket, nom_vendeur, id_vendeur, date_achat_dt, nbr_objet, moyen_paiement, prix_total, lien, reducbene, reducclient, reducgrospanierclient, reducgrospanierbene) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
            $insertDataDansTicketCaisse->execute(array($ticketmodif['id_ticket'], $nomVendeur, $idVendeur, $date_achat, $nbrObjet, $moyenDePaiement, $getTotalEnEuros, $lien, 1, 0, 0, 0));
        elseif($_GET['ra']=='trueGrosPanierClient'):
            $insertDataDansTicketCaisse = $db->prepare('INSERT INTO ticketdecaisse(id_ticket, nom_vendeur, id_vendeur, date_achat_dt, nbr_objet, moyen_paiement, prix_total, lien, reducbene, reducclient, reducgrospanierclient, reducgrospanierbene) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
            $insertDataDansTicketCaisse->execute(array($ticketmodif['id_ticket'], $nomVendeur, $idVendeur, $date_achat, $nbrObjet, $moyenDePaiement, $getTotalEnEuros, $lien, 0, 0, 1, 0));
        elseif($_GET['ra']=='trueGrosPanierBene'):
            $insertDataDansTicketCaisse = $db->prepare('INSERT INTO ticketdecaisse(id_ticket, nom_vendeur, id_vendeur, date_achat_dt, nbr_objet, moyen_paiement, prix_total, lien, reducbene, reducclient, reducgrospanierclient, reducgrospanierbene) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
            $insertDataDansTicketCaisse->execute(array($ticketmodif['id_ticket'], $nomVendeur, $idVendeur, $date_achat, $nbrObjet, $moyenDePaiement, $getTotalEnEuros, $lien, 0, 0, 0, 1));
        else:
            $insertDataDansTicketCaisse = $db->prepare('INSERT INTO ticketdecaisse(id_ticket, nom_vendeur, id_vendeur, date_achat_dt, nbr_objet, moyen_paiement, prix_total, lien, reducbene, reducclient, reducgrospanierclient, reducgrospanierbene) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
            $insertDataDansTicketCaisse->execute(array($ticketmodif['id_ticket'], $nomVendeur, $idVendeur, $date_achat, $nbrObjet, $moyenDePaiement, $getTotalEnEuros, $lien, 0, 0, 0, 0));
        endif;
    else:
        $insertDataDansTicketCaisse = $db->prepare('INSERT INTO ticketdecaisse(id_ticket, nom_vendeur, id_vendeur, date_achat_dt, nbr_objet, moyen_paiement, prix_total, lien, reducbene, reducclient, reducgrospanierclient, reducgrospanierbene) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
        $insertDataDansTicketCaisse->execute(array($ticketmodif['id_ticket'], $nomVendeur, $idVendeur, $date_achat, $nbrObjet, $moyenDePaiement, $getTotalEnEuros, $lien, 0, 0, 0, 0));
    endif;
endif;

        //On récupère l'id du dernier ticket de caisse du vendeur en question.
                
    $recupInfoTc = $db-> prepare('SELECT id_ticket, prix_total FROM ticketdecaisse WHERE id_vendeur = ? ORDER BY id_ticket DESC');
    $recupInfoTc -> execute(array($idVendeur));
    
    $infoOfTicket = $recupInfoTc->fetch();
    
    if($_GET['modif']==0):
        $idOfThisTicket = $infoOfTicket[0];
        $prixOfThisTicket = $infoOfTicket[1]/100;

    elseif($_GET['modif']==1):
        $idOfThisTicket = $ticketmodif['id_ticket'];
        $prixOfThisTicket = $getTotalEnEuros/100;
    endif;
    
        //On update le lien de la db ticketdecaisse.
    $updatelien = $db->prepare('UPDATE ticketdecaisse SET lien = "tickets/Ticket'.$idOfThisTicket.'.txt" WHERE id_ticket = ?');
    $updatelien -> execute(array($idOfThisTicket));
    
    //On ouvre un fichier texte

    $fichier = fopen("tickets/Ticket$idOfThisTicket.txt", 'c+b');
    $entete = "\t RESSOURCE'BRIE\r\t Association loi 1901\r\t RNA : W772010160\r\t Siret : 91221719700017 \r\r Ticket de caisse $idOfThisTicket\r Vendeur : $prenomVendeur \r date et heure : $date_achat \r\r";
    fwrite($fichier, $entete);        

    $getAllObjetOfTicket = $db -> prepare('SELECT * FROM ticketdecaissetemp WHERE id_temp_vente = ?');
    $getAllObjetOfTicket -> execute(array($_GET['id_temp_vente']));
    
    //FETCH_ASSOC retourne un tableau multidimensionnel avec des clefs associatives
    $getObjets = $getAllObjetOfTicket -> fetchAll(PDO::FETCH_ASSOC);

    
    //On fait une boucle pour chaque élément du ticket de caisse afin de les mettre dans la bdd vendus.
        foreach($getObjets as $v):
        
        $id_objet = $v['id'];
        $nom_vendeur = $v['nom_vendeur'];
        $id_vendeur = $v['id_vendeur'];
        $nom_objet = $v['nom'];
        $categorie_objet = $v['categorie'];
        $souscat_objet = $v['souscat'];
        $prix_objet = $v['prix'];
        $nbr=$v['nbr'];
        $timestamp = time();
    


        //On insère l'objet dans la db objets vendus
    
            $insertObjetInDB = $db -> prepare('INSERT INTO objets_vendus(uuid_ticket, nom, nom_vendeur, id_vendeur, categorie, souscat, date_achat, timestamp, prix,nbr) VALUES (?,?,?,?,?,?,?,?,?,?)');
            $insertObjetInDB -> execute(array($idOfThisTicket, $nom_objet, $nom_vendeur, $id_vendeur, $categorie_objet, $souscat_objet, $date_achat, $timestamp, $prix_objet, $nbr));
        
        //On insère dans le fichier texte.
        
        $prix_objet_euros = $prix_objet/100;
            $prix_t = $prix_objet_euros*$nbr;
            $contenu = "$nbr $nom_objet ... $categorie_objet ... $prix_t € \r\r";
        fwrite($fichier, $contenu);
        
            
            //On vide le ticket de caisse temporaire.
        
        $deleteFromTicketDeCaisse = $db -> prepare('DELETE FROM ticketdecaissetemp WHERE id = ?');
        $deleteFromTicketDeCaisse -> execute(array($id_objet));
    
        //On vide la db vente de la vente en cours.
        
        $supprFromDbVente = $db -> prepare('DELETE FROM vente WHERE id_temp_vente = ?');
        $supprFromDbVente -> execute(array($_GET['id_temp_vente']));
        
        endforeach;

    //On vide les objets de la table objets_vendus_modif s'il s'agissait d'une modification de vente

    if(isset($_GET['id_modif'])):
        $sql2='DELETE FROM objets_vendus_modif WHERE id_modif='.$_GET['id_modif'].'';
        $sth2=$db->query($sql2);
    endif;

    //On écrit la fin du ticket.
    
        $fin = "\r Montant total = $prixOfThisTicket € \r Moyen de paiement = $moyenDePaiement \r\r TVA non applicable, article 293B du Code général des impôts. \r\rMerci de votre visite et à bientôt :-)";    
    fwrite($fichier, $fin);
    fclose($fichier);
    
    require('actions/objets/update_db_bilan.php');
    
    //On redirige vers la page objets collectés.
    header("location: ticketdecaisseapresvente.php?id_ticket=$idOfThisTicket");

            
   
    endif;
    
