<?php require("actions/users/signupaction.php");?>
<!DOCTYPE HTML>
<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body class="corps">
        <?php
            $lineheight = "uneligne";
            $titre = 'Signup';
            include("includes/header.php");
        ?>
        
        
        <a class="doc_lien"rticle class="doc">
            <h1 class="gros_titre">Formulaire d'inscription</h1>
            
            <form method="post">
                
                <?php if(isset($errorMsg)){echo '<p class="paraphdoc">'.$errorMsg.'</p>';}?>
                
                <fieldset class="jeuchamp">
                    
                    <label class="champ" for="prenom">Prénom : </label>
                    <input class="input"type="text" name="prenom">
            
                    <label class="champ" for="nom">Nom : </label>
                    <input class="input"type="text" name="nom">
            
                    <label class="champ" for="pseudo">Pseudo : </label>
                    <input class="input"type="text" name="pseudo">
            
                    <label class="champ" for="password">Mot de passe : </label>
                    <input class="input"type="password" name="password">
                
                </fieldset>
            
                <input type="submit" class="input inputsubmit" name="validate" value="S'inscrire">
                
            </form>
            
            <a class="doc_lien" href="login.php" style="color: black"><p class="paraphdoc">J'ai déjà un compte, je me connecte ici !</p></a>
            
        </article>
        
        <?php include('includes/footer.php');?>
    </body>
</html>