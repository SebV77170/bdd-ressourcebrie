<?php require('actions/db.php');
require('actions/objets/currencyToDecimalFct.php');
?>

<?php

//Pour vérifier si le formulaire a bien été cliqué

if(isset($_POST['validate'])):

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

    if($somme == $_GET['prix']*100):
        if(empty($_POST['carte']) AND empty($_POST['cheque']) AND empty($_POST['espece'])):
            $message = 'Veuillez remplir au moins 2 moyens de paiments ou revenir en arrière et sélectionner le paiement adéquat, merci.';
        else:
            if((empty($_POST['carte']) AND empty($_POST['cheque'])) OR (empty($_POST['cheque']) AND empty($_POST['espece'])) OR (empty($_POST['carte']) AND empty($_POST['espece']))):
                $message = 'Veuillez revenir en arrière et sélectionner le paiement adéquat, merci.';
            else:
                if(!empty($_POST['carte']) AND !empty($_POST['cheque'])):
                    if(!empty($_POST['transaction'])):
                        require('meansOfPayment.php');
                    else:
                        $message='Veuillez remplir le numéro de transaction Sumup, svp.';
                    endif;
                elseif(!empty($_POST['cheque']) AND !empty($_POST['espece'])):
                        require('meansOfPayment.php');
                elseif(!empty($_POST['carte']) AND !empty($_POST['espece'])):
                    if(!empty($_POST['transaction'])):
                        require('meansOfPayment.php');
                    else:
                        $message='Veuillez remplir le numéro de transaction Sumup, svp.';
                    endif;
                endif;
            endif;
        endif;
    else:
        $message = 'Attention, la somme des paiments n\'est pas égal au prix total.';
    endif;
endif;

