<?php require('actions/objets/modifObjetsVendusAction.php');
        require('actions/objets/getInfoOfEditObjetVendu.php');
?>

<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body>
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Modification objets vendus';
            include("includes/header.php");
        ?>
        
        <?php
            if($_SESSION['admin'] >= 1){
            ?>

        <form method="post">
                
                <fieldset>
            
                    <label for="nom">Nom : </label>
                    <input type="text" name="nom" value="<?=$objet?>">
            
                    <label for="type">Type : </label>
                    <select id="type" name="type">
                        <option value="<?=$type?>"><?=$type?></option>
                        <option value="EA">Ameublement</option>
                        <option value="D3E">Electronique</option>
                        <option value="TLC">Textiles, Linges de maison, Chaussures</option>
                        <option value="LDC">Livres, DVD, CD</option>
                        <option value="Jeux">Jeux, jouets</option>
                        <option value="Puericulture">Matériel puériculture</option>
                        <option value="Autres">Autres</option>
                    </select>
            
                    <label for="poids">Poids en gramme: </label>
                    <input type="poids" name="poids" value="<?=$poids?>">
                    
                    <label for="prix">Prix en euro: </label>
                    <input type="text" name="prix" value="<?=$prix/100?>">
                
                </fieldset>
            
                <input type="submit" name="validate" value="Modifier">
                
                
        </form>
        <?php
            }else{
                echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
            }
            include('includes/footer.php');
            ?>
    </body>
</html>