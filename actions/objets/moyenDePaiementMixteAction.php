<?php 
require('actions/db.php');

?>

<?php

//Pour vérifier si le formulaire a bien été cliqué

if(isset($_POST['validatemixte'])):

    //on récupère les données du ticket de caisse modifié s'il existe
    if($_GET['modif']==1):
        $sql='SELECT * FROM modifticketdecaisse WHERE id_modif = '.$_GET['id_modif'].'';
        $sth=$db->query($sql);
        $ticketmodif=$sth->fetch();
    endif;

    $espece = currencyToDecimal($_POST['espece'])*100;
    $cheque = currencyToDecimal($_POST['cheque'])*100;
    $carte = currencyToDecimal($_POST['carte'])*100;

    $somme = $espece + $cheque + $carte;

    //pour palier à des problèmes d'arrondis de $_GET['prix]*100 (sur certaines valeurs), et par conséquent à des problèmes de comparaison dans le if ci-dessous.
    $number = round($_GET['prix']*100,0);

    if($somme == $number):
        if(empty($_POST['carte']) AND empty($_POST['cheque']) AND empty($_POST['espece'])):
            $message = 'Veuillez remplir au moins 2 moyens de paiments ou revenir en arrière et sélectionner le paiement adéquat, merci.';
        else:
            if((empty($_POST['carte']) AND empty($_POST['cheque'])) OR (empty($_POST['cheque']) AND empty($_POST['espece'])) OR (empty($_POST['carte']) AND empty($_POST['espece']))):
                $message = 'Veuillez revenir en arrière et sélectionner le paiement adéquat, merci.';
            else:
                if(!empty($_POST['carte']) AND !empty($_POST['cheque'])):
                        require('actions/objets/compte_transac.php');
                        require('meansOfPayment.php');   
                elseif(!empty($_POST['cheque']) AND !empty($_POST['espece'])):
                        require('meansOfPayment.php');
                elseif(!empty($_POST['carte']) AND !empty($_POST['espece'])):
                        require('actions/objets/compte_transac.php');
                        require('meansOfPayment.php');    
                endif;
            endif;
        endif;
    else:
        $message = 'Attention, la somme des paiments n\'est pas égal au prix total.';
    endif;
endif;

