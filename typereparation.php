<?php
require('actions/users/securityAction.php');
require('actions/db.php');

if(isset($_POST['validate'])){
    
    if(!empty($_POST['rep'])){
        
        $id_objet = $_GET['id'];
        $rep = $_POST['rep'];
        $saisipar = $_SESSION['nom'];
        $reparepar = '';
        $timestamprep = 0;
        $finrep = 0;
        $daterep = '';
        //Obtenir un timestamp avec le fuseau horaire parisien    

        try {
        $date_heure_insertion = new DateTimeImmutable('now', new DateTimeZone('europe/paris'));
        } catch (Exception $e) {
        echo $e->getMessage();
        exit(1);
        }

        $date_heure_insertion_TS = $date_heure_insertion->format('U');

        //Obtenir la date et l'heure correspondante.

        $date_heure_insertion_Date = $date_heure_insertion->format('d-m G:i:s');
        
        //On récupère les inforamtions de l'objet depuis la db Objets_collectés
        
        $recupDataObjet = $db -> prepare('SELECT * FROM objets_collectes WHERE id = ?');
        $recupDataObjet -> execute(array($id_objet));
        $dataObjet = $recupDataObjet->fetch(PDO::FETCH_ASSOC);
        
        $cat = $dataObjet['categorie'];
        $souscat = $dataObjet['souscat'];
        
        //On insère dans la db réparation.
        
        $insertIntoDBRep = $db -> prepare('INSERT INTO reparation(id_objet, categorie, souscat, reparation, saisipar, reparepar, date, timestamp, daterep, timestamprep, end)VALUES (?,?,?,?,?,?,?,?,?,?,?)');
        $insertIntoDBRep -> execute(array($id_objet, $cat, $souscat, $rep, $saisipar, $reparepar, $date_heure_insertion_Date, $date_heure_insertion_TS,$daterep, $timestamprep, $finrep));
        
        header('location:depot.php');
    
    }else{
        $message = 'Veuillez écrire ce qu\'il faut faire sur l\'objet svp.';
    }
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
            $titre = 'Quel type de réparation ?';
            include("includes/header.php");
            $page = 1;
            include("includes/nav.php");
            ?>
            
            
            <?php
            if($_SESSION['admin'] >= 1){
            ?>

        <form method="post">
                
                <fieldset class="jeuchamp">
            
                    <label class="champ" for="rep">Précisez en  quelques mots ce qu'il faut faire sur l'objet : </label>
                    <textarea class="champtexte" name="rep"></textarea>
                
                </fieldset>
                
                <?php if(isset($_GET['id'])){
                ?>
                <p style='text-align: center; color: red;'>Notez bien l'ID de l'objet : <?=$_GET['id']?></p>
                <?php
                }
                ?>
            
                <input type="submit" class="input inputsubmit" name="validate" value="Valider">
                <?php if(isset($message)){
                    echo $message;
                }
                ?>
                <a href="depot.php" class="stdbouton">Retour</a>
                
        </form>
        
       
        
        
        <?php
            }else{
                echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
            }
            include('includes/footer.php');
            ?>
    </body>
</html>