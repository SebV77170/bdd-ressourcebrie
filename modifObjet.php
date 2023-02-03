<?php
require('actions/users/securityAction.php');
require('actions/objets/modifObjetAction.php');
require('actions/objets/getInfoOfEditObjet.php');
?>

<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body>
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Formulaire bdd';
            include("includes/header.php");
        ?>

<?php
            if($_SESSION['admin'] >= 1){
            ?>
        
        <?php if(isset($errorMsg)){
            echo $errorMsg;
        }
        ?>

        <form method="post">
                
                <fieldset>
                    
                    <label for="flux">Type d'apport: </label>
                    <select id="flux" name="flux">
                        <option value="<?=$flux?>"><?=$flux?></option>
                        <option value="Apport">Apport volontaire</option>
                        <option value="Collecte">Collecte à domicile</option>
                        <option value="Porte-a-porte">En porte-à-porte</option>
                        <option value="Déchèterie">En déchèterie</option>
                    </select>
            
            
                    <label for="type">Catégorie : </label>
                    <select id="type" name="type">
                        <option value="<?=$type?>"><?=$type?></option>
                        <?php
                            $result = $db->prepare('SELECT * FROM categories WHERE parent_id = "parent"');
                            $result->execute();
                            
                            while($row = $result->fetch(PDO::FETCH_BOTH)){
                                ?><option value="<?php echo $row['category'];?>"><?php echo $row['category'];?></option>
                                <?php
                            }
                            
                        ?>
                        
                    </select>
                    
                    <label for="SUBCATEGORY">Sous-catégorie :</label>
                    <select id="sub-category-dropdown" name="souscategorie">
                        <option value="<?=$souscat?>"><?=$souscat?></option>
                    </select>
                    
                    <label for="nom">Précision : </label>
                    <input type="text" name="nom" value="<?=$objet?>">
            
                    <label for="poids">Poids en gramme : </label>
                    <input type="poids" name="poids" value="<?=$poids?>">
                
                </fieldset>
            
                <input type="submit" name="validate" value="Insérer">
                
                
        </form>

        
        <!-- Script Jquery pour dérouler des sous catégories à partir des catégories-->
        
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
        <script>
            $(document).ready(function() {
                $('#type').on('change',function(){
                    var category_id = this.value;
                    $.ajax({
                        url:"actions/objets/get-subcat.php",
                        type:"POST",
                        data:{
                            category_id: category_id 
                        },
                        cache: false,
                        success: function(result){
                            $("#sub-category-dropdown").html(result);
                        }
                    });
                });
            });
        </script>
        <?php
            }else{
                echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
            }
            include('includes/footer.php');
            ?>
            
    </body>
</html>
