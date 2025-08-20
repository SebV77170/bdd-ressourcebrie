<?php

session_start();
require('actions/db.php');

//Validation du formulaire

    if(isset($_POST['validate'])){
        
        //Vérifier si l'user à bien compléter tous les champs
    
        if(!empty($_POST['pseudo']) AND !empty($_POST['password'])){
        
            //Les données de l'user
    
            $user_pseudo = htmlspecialchars($_POST['pseudo']);
            $user_password = htmlspecialchars($_POST['password']);
            
            //Vérifier si l'utilisateur existe (si le pseudo existe)
            
            $checkIfUserExists = $db->prepare('SELECT * FROM users WHERE pseudo = ?');
            $checkIfUserExists->execute(array($user_pseudo));
            
            if($checkIfUserExists->rowCount() > 0){
                
                //Récupérer les données de l'utilisateur
                
                $usersInfos = $checkIfUserExists->fetch();
                
                //Vérifier si le mot de passe est correct.
                
                if(password_verify($user_password, $usersInfos['password'])){
                    
                    //Authentifier l'utilisateur sur le site et récupérer ses données dans des variables session.
                
                    $_SESSION['auth'] = true;
                    $_SESSION['admin']=$usersInfos['admin'];
                    $_SESSION['uuid_user'] = $usersInfos['uuid_user'];
                    $_SESSION['nom'] = $usersInfos['nom'];
                    $_SESSION['prenom'] = $usersInfos['prenom'];
                    $_SESSION['ucprenom'] = ucwords($usersInfos['prenom']);
                    $_SESSION['pseudo'] = $usersInfos['pseudo'];
                    
                    //Rediriger l'utilisateur vers la page d'accueil du forum
                    
                    header('location: index.php');
                    
                }else{
                    $errorMsg = "Votre mot de passe est incorrect...";
                }
                
            }else{
                $errorMsg = "Le pseudo est incorrect...";
            }
            
        
        }else{
            $errorMsg = "Veuillez compléter tous les champs, svp.";
        }

    }


?>