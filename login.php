<?php require('actions/users/loginAction.php'); ?>
<!DOCTYPE HTML>
<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body class="corps">
        <?php
            $lineheight = "uneligne";
            $titre = 'Login';
            include("includes/header.php");
        ?>
        
        <article class="doc">
            <h1 class="gros_titre">Formulaire de connexion</h1>
            
            <form method="post">
                
                <?php if(isset($errorMsg)){echo '<p class="paraphdoc">'.$errorMsg.'</p>';}?>
                
                <fieldset class="jeuchamp">
            
                    <label class="champ" for="pseudo">Pseudo : </label>
                    <input class="input"type="text" name="pseudo">
            
                    <label class="champ" for="password">Mot de passe : </label>
                    <input class="input"type="password" name="password">
                
                </fieldset>
            
                <input type="submit" class="input inputsubmit" name="validate" value="Connexion">
                
                
            </form>
            
            <a class="doc_lien" href="signup.php" style="color: black"><p class="paraphdoc">Je n'ai pas encore de compte, je m'inscris ici !</p></a>
            
        </article>
        
        <?php include('includes/footer.php');?>
    </body>
</html>