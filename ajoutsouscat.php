<?php
require('actions/db.php');
require('actions/users/securityAction.php');
?>

<?php if(isset($_POST['validate'])){
    
    if(!empty($_POST['type']) AND !empty($_POST['nom'])){
        
        $objet_nom = htmlspecialchars($_POST['nom']);
        $objet_type = $_POST['type'];
        
        $insertObjet = $db->prepare('INSERT INTO categories(parent_id, category)VALUES(?,?)');
        $insertObjet->execute(array($objet_type, $objet_nom ));
        
        if(isset($_GET['id_modif'])):
        
            if($_GET['from']=='depot'){
                header('location: depot.php');
            }else{
                header('location: objetsVendus.php?id_temp_vente='.$_GET['id_temp_vente'].'&modif='.$_GET['modif'].'$id_modif='.$_GET['id_modif'].'');
            }
        else:
            if($_GET['from']=='depot'){
                header('location: depot.php');
            }else{
                header('location: objetsVendus.php?id_temp_vente='.$_GET['id_temp_vente'].'&modif='.$_GET['modif'].'');
            }
        endif;

        
    }else{
        
        echo 'veuillez remplir tous les champs svp';
        
    }
    
}

if(isset($_POST['cancel'])){
    
    if(isset($_GET['id_modif'])):
        
        if($_GET['from']=='depot'){
            header('location: depot.php');
        }else{
            header('location: objetsVendus.php?id_temp_vente='.$_GET['id_temp_vente'].'&modif='.$_GET['modif'].'&id_modif='.$_GET['id_modif'].'');
        }
    else:
        if($_GET['from']=='depot'){
            header('location: depot.php');
        }else{
            header('location: objetsVendus.php?id_temp_vente='.$_GET['id_temp_vente'].'&modif='.$_GET['modif'].'');
        }
    endif;
        
}
    


?>

<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body class="corps">
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Ajout d\'une sous catégorie';
            include("includes/header.php");
            $page = 1;
            include("includes/nav.php");
            ?>
            
            
            <?php
            if($_SESSION['admin'] >= 1){
            ?>
    
        <form method="post">
                
                <fieldset class="jeuchamp">
            
            
                    <label class="champ" for="type">Dans quelle catégorie : </label>
                    <select id="type" name="type">
                        <option value="<?php echo($_GET['cat']);?>"><?php echo($_GET['cat']);?></option>
                        <?php
                            $result = $db->prepare('SELECT * FROM categories WHERE parent_id = "parent"');
                            $result->execute();
                            
                            while($row = $result->fetch(PDO::FETCH_BOTH)){
                                ?><option value="<?php echo $row['category'];?>"><?php echo $row['category'];?></option>
                                <?php
                            }
                            
                        ?>
                    </select>
                        
                    
                    <label class="champ" for="nom">Nouvelle sous-catégorie : </label>
                    <input class="input"type="text" name="nom">
            
                    
                </fieldset>
            
                <input type="submit" class="input inputsubmit" name="validate" value="Créer">
                <input type="submit" class="input inputsubmit" name="cancel" value="Annuler">
                
        </form>
    <?php
            }else{
                echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
            }
            include('includes/footer.php');
            ?>
    </body>
</html>
    