

<?php


    
    //Pour vérifier si le formulaire a bien été cliqué
    
    if(isset($_POST['validate'])){
        
        
        if(!empty($_POST['prix']) AND $_POST['prix']>=0){
            
            if(!empty($_GET['id_temp_vente'])){
                
                $id_temp_vente = $_GET['id_temp_vente'];
            
                // On récupère les données du formulaire de prix
            
                $prixOfObjet = currencyToDecimal($_POST['prix'])*100;
                
                //On récupère les données de l'objet
                
                $nom_objet = $_POST['nom'];
                if(isset($_POST['type1'])):
                    $categorie_objet = $_POST['type1'];
                else:
                    $categorie_objet = $_POST['type2'];
                endif;
                $souscat = $_POST['souscategorie'];
                $nbr=1;
                $prixt=$nbr*$prixOfObjet;
                
                //On récupère les données du vendeur
                
                $nomVendeur = $_SESSION['nom'];
                $idVendeur = $_SESSION['uuid_user'];
                
                $date_achat = date('d/m/Y');
                
                
                //On insère l'objet dans la db ticketdecaissetemp
                
                $insertObjetInTicket = $db -> prepare('INSERT INTO ticketdecaissetemp(id_temp_vente, nom_vendeur, id_vendeur, nom, categorie, souscat, prix, nbr, prixt) VALUES(?,?,?,?,?,?,?,?,?)');
                $insertObjetInTicket -> execute(array($id_temp_vente, $nomVendeur, $idVendeur, $nom_objet, $categorie_objet, $souscat, $prixOfObjet, $nbr, $prixt));
                
                
                //On redirige vers la page objets vendus.

                if(isset($_POST['id_modif'])):
                    header('location:objetsVendus.php?nbrobjet='.$NbrObjetDeTC.'&id_temp_vente='.$id_temp_vente.'&id_modif='.$_POST['id_modif'].'&modif='.$_POST['modif'].'#tc');
                else:
                    header('location:objetsVendus.php?nbrobjet='.$NbrObjetDeTC.'&id_temp_vente='.$id_temp_vente.'&modif='.$_POST['modif'].'#tc');
                endif;
            }
            else{
                $message = 'Un problème est survenu concernant l\'id de la vente';
            }
        }else{
            $message = 'Veuillez remplir le champ NOM et/ou le champ PRIX ou mettre 0 dans PRIX si vous donnez l\'objet, svp';   
        }
    }
        
