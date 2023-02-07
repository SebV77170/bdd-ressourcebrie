<?php require('actions/users/loginAction.php'); ?>
<!DOCTYPE HTML>
<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body>
        <?php
            $lineheight = "uneligne";
            $titre = 'Login';
            include("includes/header.php");
        ?>
        
        <article class="doc">
            <h1>Formulaire de connexion</h1>
            
            <form method="post">
                
                <?php if(isset($errorMsg)){echo '<p>'.$errorMsg.'<p>';}?>
                
                <fieldset>
            
                    <label for="pseudo">Pseudo : </label>
                    <input type="text" name="pseudo">
            
                    <label for="password">Mot de passe : </label>
                    <input type="password" name="password">
                
                </fieldset>
            
                <input type="submit" name="validate" value="Connexion">
                
                
            </form>
            
            <a href="signup.php" style="color: black"><p>Je n'ai pas encore de compte, je m'inscris ici !</p></a>
            
        </article>
        
        <?php include('includes/footer.php');?>
    </body>
</html>