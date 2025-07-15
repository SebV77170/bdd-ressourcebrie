<?php
require('actions/db.php');
require('actions/uuid.php'); // Inclut la fonction generate_uuidv4()

if (isset($_POST['inscription'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $pseudo = $_POST['pseudo'];
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT); // Sécurisation du mot de passe

    // Générer un UUID pour l'utilisateur
    $uuid_user = generate_uuidv4();

    // Insertion dans la table users
    $stmt = $db->prepare('INSERT INTO users(uuid_user, nom, prenom, email, pseudo, password) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$uuid_user, $nom, $prenom, $email, $pseudo, $mot_de_passe]);

    // Redirection après inscription
    header('Location: confirmation.php?uuid_user=' . $uuid_user);
}
?>