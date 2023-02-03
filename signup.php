<?php require("actions/users/signupaction.php");?>
<!DOCTYPE HTML>
<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body>
        <?php
            $lineheight = "uneligne";
            $titre = 'Signup';
            include("includes/header.php");
        ?>
        
        
        <article class="doc">
            <h1>Formulaire d'inscription</h1>
            
            <form method="post">
                
                <?php if(isset($errorMsg)){echo '<p>'.$errorMsg.'<p>';}?>
                
                <fieldset>
                    
                    <label for="prenom">Prénom : </label>
                    <input type="text" name="prenom">
            
                    <label for="nom">Nom : </label>
                    <input type="text" name="nom">
            
                    <label for="pseudo">Pseudo : </label>
                    <input type="text" name="pseudo">
            
                    <label for="password">Mot de passe : </label>
                    <input type="password" name="password">
                
                </fieldset>
            
                <input type="submit" name="validate" value="S'inscrire">
                
            </form>
            
            <a href="login.php" style="color: black"><p>J'ai déjà un compte, je me connecte ici !</p></a>
            
        </article>
        
        <?php include('includes/footer.php');?>
    </body>
</html>