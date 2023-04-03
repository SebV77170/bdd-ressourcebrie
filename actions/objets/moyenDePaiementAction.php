<?php 
require('actions/db.php');
require('actions/objets/currencyToDecimalFct.php');
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

if(isset($_POST['validate'])){
    
    
    if($_POST['paiement']=='espece'){
        
        $getAllObjetOfTicket = $db -> prepare('SELECT * FROM ticketdecaissetemp WHERE id_temp_vente = ?');
        $getAllObjetOfTicket -> execute(array($_GET['id_temp_vente']));
        
        //FETCH_ASSOC retourne un tableau multidimensionnel avec des clefs associatives
        $getObjets = $getAllObjetOfTicket -> fetchAll(PDO::FETCH_ASSOC);

        if($_GET['modif']==0):

            date_default_timezone_set('Europe/Paris');
            $date_achat = new DateTime('now', new DateTimeZone('Europe/paris'));
            $date_achat = $date_achat->format('Y/m/d G:i');

        elseif($_GET['modif']==1):
            $date_achat = $ticketmodif['date_achat_dt'];
        
        endif;
         
            //On remplit la bdd ticketdecaisse
            
        $moyenDePaiement = $_POST['paiement'];
        $nbrObjet = $_GET['nbrObjet'];
        $nomVendeur = $_SESSION['nom'];
        $idVendeur = $_SESSION['id'];
        $prenomVendeur = $_SESSION['prenom'];
        $numcheque = 0;
        $banque = 0;
            
            
        //pour cela on récupère le prix total
        $getPrixTotal = $db->prepare('SELECT SUM(prixt) AS prix_total FROM ticketdecaissetemp WHERE id_temp_vente = ?');
        $getPrixTotal -> execute(array($_GET['id_temp_vente']));

        $getTotal = $getPrixTotal->fetch();
        $getTotalEnEuros = $getTotal['prix_total'];
        $lien = '...';
        
        //On insère.
        if($_GET['modif']==0):
            $insertDataDansTicketCaisse = $db-> prepare('INSERT INTO ticketdecaisse(nom_vendeur, id_vendeur, date_achat_dt, nbr_objet, moyen_paiement, num_cheque, banque, prix_total, lien) VALUES (?,?,?,?,?,?,?,?,?)');
            $insertDataDansTicketCaisse -> execute(array($nomVendeur, $idVendeur, $date_achat, $nbrObjet, $moyenDePaiement, $numcheque, $banque, $getTotalEnEuros, $lien));
        elseif($_GET['modif']==1):
            $insertDataDansTicketCaisse = $db-> prepare('INSERT INTO ticketdecaisse(id_ticket, nom_vendeur, id_vendeur, date_achat_dt, nbr_objet, moyen_paiement, num_cheque, banque, prix_total, lien) VALUES (?,?,?,?,?,?,?,?,?,?)');
            $insertDataDansTicketCaisse -> execute(array($ticketmodif['id_ticket'], $nomVendeur, $idVendeur, $date_achat, $nbrObjet, $moyenDePaiement, $numcheque, $banque, $getTotalEnEuros, $lien));
        endif;

        //On récupère l'id du dernier ticket de caisse du vendeur en question.
        
        $recupInfoTc = $db-> prepare('SELECT id_ticket, prix_total FROM ticketdecaisse WHERE id_vendeur = ? ORDER BY id_ticket DESC');
        $recupInfoTc -> execute(array($idVendeur));
        
        $infoOfTicket = $recupInfoTc->fetch();

        if($_GET['modif']==0):
            $idOfThisTicket = $infoOfTicket[0];
        elseif($_GET['modif']==1):
            $idOfThisTicket = $ticketmodif['id_ticket'];
        endif;

        $prixOfThisTicket = $infoOfTicket[1]/100;
        
        //On update le lien de la db ticketdecaisse.
        $updatelien = $db->prepare('UPDATE ticketdecaisse SET lien = "tickets/Ticket'.$idOfThisTicket.'.txt" WHERE id_ticket = ?');
        $updatelien -> execute(array($idOfThisTicket));
                
        //On ouvre un fichier texte
        
        $fichier = fopen("tickets/Ticket$idOfThisTicket.txt", 'c+b');
        $entete = "\t RESSOURCE'BRIE\r\t Association loi 1901\r\t RNA : W772010160\r\t Siret : 91221719700017 \r\r Ticket de caisse $idOfThisTicket\r Vendeur : $prenomVendeur \r date et heure : $date_achat \r\r";
        fwrite($fichier, $entete);
            
    
        //On fait une boucle pour chaque élément du ticket de caisse afin de les retirer de la bdd collectée et de les mettre dans la bdd vendus.
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
        
            $insertObjetInDB = $db -> prepare('INSERT INTO objets_vendus(id_ticket, nom, nom_vendeur, id_vendeur, categorie, souscat, date_achat, timestamp, prix,nbr) VALUES (?,?,?,?,?,?,?,?,?,?)');
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
            
        
        
        require('actions/objets/update_db_bilan.php');
        
        
        //On redirige vers la page somme à rendre.
        
        if(!empty($_POST['client'])){

            $montant_client = currencyToDecimal($_POST['client']);
            $somme_a_rendre = $montant_client - $prix;
            
            $fin = "\r Montant total = $prixOfThisTicket € \r Moyen de paiement = $moyenDePaiement \r Somme donnée = $montant_client € \r Somme rendue = $somme_a_rendre € \r\r TVA non applicable, article 293B du Code général des impôts.\r\rMerci de votre visite et à bientôt :-)";
            fwrite($fichier, $fin);
            fclose($fichier);
        
            header('location: sommearendre.php?prix='.$somme_a_rendre.'&id_ticket='.$idOfThisTicket.'');
            
            }else{
            $fin = "\r Montant total = $prixOfThisTicket € \r Moyen de paiement = $moyenDePaiement \r\r TVA non applicable, article 293B du Code général des impôts.\r\rMerci de votre visite et à bientôt :-)";
            fwrite($fichier, $fin);
            fclose($fichier);
        
            header("location: ticketdecaisseapresvente.php?id_ticket=$idOfThisTicket");
            }
            
    }elseif($_POST['paiement']=='cheque'){
        $nbrObjet = $_GET['nbrObjet'];
        if(isset($_GET['id_modif'])):
            header('location: moyenDePaiementCheque.php?prix='.$prix.'&nbrObjet='.$nbrObjet.'&id_temp_vente='.$_GET['id_temp_vente'].'&modif='.$_GET['modif'].'&id_modif='.$_GET['id_modif'].'');
        else:
            header('location: moyenDePaiementCheque.php?prix='.$prix.'&nbrObjet='.$nbrObjet.'&id_temp_vente='.$_GET['id_temp_vente'].'&modif='.$_GET['modif'].'');
        endif;
    }elseif($_POST['paiement']=='carte'){
        $nbrObjet = $_GET['nbrObjet'];
        if(isset($_GET['id_modif'])):
        header('location: moyenDePaiementCarte.php?prix='.$prix.'&nbrObjet='.$nbrObjet.'&id_temp_vente='.$_GET['id_temp_vente'].'&modif='.$_GET['modif'].'&id_modif='.$_GET['id_modif'].'');   
        else:
        header('location: moyenDePaiementCarte.php?prix='.$prix.'&nbrObjet='.$nbrObjet.'&id_temp_vente='.$_GET['id_temp_vente'].'&modif='.$_GET['modif'].'');   
        endif;
    }else{
        $nbrObjet = $_GET['nbrObjet'];
        if(isset($_GET['id_modif'])):
        header('location: moyenDePaiementMixte.php?prix='.$prix.'&nbrObjet='.$nbrObjet.'&id_temp_vente='.$_GET['id_temp_vente'].'&modif='.$_GET['modif'].'&id_modif='.$_GET['id_modif'].'');
        else:
        header('location: moyenDePaiementMixte.php?prix='.$prix.'&nbrObjet='.$nbrObjet.'&id_temp_vente='.$_GET['id_temp_vente'].'&modif='.$_GET['modif'].'');
        endif;
    }
}
        
