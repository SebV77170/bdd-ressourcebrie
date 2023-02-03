<?php
require('actions/users/securityAction.php');
require('actions/db.php');

//On récupère les inforamtions de l'objet depuis la db reparation
        $id_objet = $_GET['id'];
        
        $recupDataObjet = $db -> prepare('SELECT * FROM reparation WHERE id_objet = ?');
        $recupDataObjet -> execute(array($id_objet));
        $dataObjet = $recupDataObjet->fetch(PDO::FETCH_ASSOC);
        
        $texterep = $dataObjet['reparation'];
        $reparateur = $dataObjet['reparepar'];

if(isset($_POST['validate'])){
    
    if(!empty($_POST['rep']) AND !empty('reparateur')){
        
        
        $rep = $_POST['rep'];
        $reparepar = $_POST['reparateur'];
        //Obtenir un timestamp avec le fuseau horaire parisien    

        try {
        $date_heure_reparation = new DateTimeImmutable('now', new DateTimeZone('europe/paris'));
        } catch (Exception $e) {
        echo $e->getMessage();
        exit(1);
        }

        $date_heure_reparation_TS = $date_heure_reparation->format('U');

        //Obtenir la date et l'heure correspondante.

        $date_heure_reparation_Date = $date_heure_reparation->format('d-m G:i:s');
        
        //On insère dans la db réparation.
        
        $insertIntoDBRep = $db -> prepare('UPDATE reparation SET reparation = ?, reparepar = ?, daterep = ?, timestamprep = ? WHERE id_objet = ?');
        $insertIntoDBRep -> execute(array($rep, $reparepar, $date_heure_reparation_Date, $date_heure_reparation_TS, $id_objet));
        
        header('location:reparation.php');
    
    }else{
        $message = 'Veuillez écrire ce qu\'il faut faire sur l\'objet svp.';
    }
}
    


?>

<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body>
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Quel type de réparation ?';
            include("includes/header.php");
            $page = 4;
            include("includes/nav.php");
            ?>
            
            
            <?php
            if($_SESSION['admin'] >= 1){
            ?>

        <form method="post">
                
                <fieldset>
            
                    <label for="rep">Rajoutez au texte actuel en quelques mots ce que vous avez fait sur l'objet : </label>
                    <textarea name="rep"><?=$texterep?></textarea>
                    
                    <label for="reparateur">Nom du réparateur</label>
                    <input type="text" name="reparateur" value="<?=$reparateur?>">
                
                </fieldset>
                
                <?php if(isset($_GET['id'])){
                ?>
                <p style='text-align: center; color: red;'>Notez bien l'ID de l'objet : <?=$_GET['id']?></p>
                <?php
                }
                ?>
            
                <input type="submit" name="validate" value="Valider">
                <?php if(isset($message)){
                    echo $message;
                }
                ?>
                <a href="reparation.php" class="stdbouton">Retour</a>
                
        </form>
        
       
        
        
        <?php
            }else{
                echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
            }
            include('includes/footer.php');
            ?>
    </body>
</html>