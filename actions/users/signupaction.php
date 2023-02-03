<?php

session_start();
require('actions/db.php');

//Validation du formulaire

    if(isset($_POST['validate'])){
        
        //Vérifier si l'user à bien compléter tous les champs
    
        if(!empty($_POST['prenom']) AND !empty($_POST['nom']) AND !empty($_POST['pseudo']) AND !empty($_POST['password'])){
        
            //Les données de l'user
    
            $user_prenom = htmlspecialchars($_POST['prenom']);
            $user_nom = htmlspecialchars($_POST['nom']);
            $user_pseudo = htmlspecialchars($_POST['pseudo']);
            $user_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $user_admin = 0;
            
            //Vérifier si l'utilisateur est déjà sur le site
            
            $checkIfUserAlreadyExists = $db->prepare('SELECT pseudo FROM users WHERE pseudo = ?');
            $checkIfUserAlreadyExists->execute(array($user_pseudo));
            
            if($checkIfUserAlreadyExists->rowCount() == 0){
                
                //Insérer l'utilisateur dans la BDD
                
                $insertUserOnWebsite = $db->prepare('INSERT INTO users(prenom, nom, pseudo, password, admin)VALUES(?,?,?,?,?)');
                $insertUserOnWebsite->execute(array($user_prenom, $user_nom, $user_pseudo, $user_password, $user_admin));
                
                //Récupérer les informations de l'utilisateur
                
                $GetInfoOfThisUserReq = $db->prepare('SELECT id, pseudo, nom, prenom FROM users WHERE nom = ? AND prenom = ? AND pseudo = ?');
                $GetInfoOfThisUserReq->execute(array($user_nom, $user_prenom, $user_pseudo));
                
                $usersInfos = $GetInfoOfThisUserReq->fetch();
                
                //Authentifier l'utilisateur sur le site et récupérer ses données dans des variables session.
                
                $_SESSION['auth'] = true;
                $_SESSION['id'] = $usersInfos['id'];
                $_SESSION['nom'] = $usersInfos['nom'];
                $_SESSION['prenom'] = $usersInfos['prenom'];
                $_SESSION['pseudo'] = $usersInfos['pseudo'];
                $_SESSION['admin'] = $user_admin;
                
                //Redirection vers la page d'accueil du forum
                
                header('location: index.php');
                
            }else{
                
                $errorMsg = "l'utilisateur est déjà inscrit sur ce site";
                
            }
            
        
        }else{
            $errorMsg = "Veuillez compléter tous les champs, svp.";
        }

    }


?>