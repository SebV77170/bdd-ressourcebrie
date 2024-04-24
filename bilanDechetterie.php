<?php
require('actions/users/securityAction.php');
require('actions/db.php');
require('app/bootstrap.php');

if(isset($_POST['validate'])):
    if(!empty($_POST['date']) AND !empty($_POST['poids'])):
        $poids=$_POST['poids'];
        $date=convertDateFRenDateUS($_POST['date']);
        $insertObjet = $db->prepare('INSERT INTO dechet(date, poids)VALUES(?,?)');
        $insertObjet->execute(array($date,$poids));
    else:
        $message="Veuillez remplir tous les champs svp, merci.";
    endif;
endif;

$sql ='SELECT date, poids FROM dechet';
$sth = $db->query($sql);
$results=$sth->fetchAll();



?>

<!DOCTYPE HTML>

<html lang="fr-FR">
    <?php include("includes/head.php");?>
    <body class="corps">
        <?php
            $lineheight = "uneligne";
            $src = 'image/PictoFete.gif';
            $alt = 'un oiseau qui fait la fête.';
            $titre = 'Bilan des apports en dechetterie';
            include("includes/header.php");
            $page = 3;
            include("includes/nav.php");
            ?>
            
            
            <?php
            if($_SESSION['admin'] >= 1){
            ?>

            <form method="POST">
                
                <fieldset class="jeuchamp">
                    <h2>Ajouter un apport</h2>

                    <label class="champ" for="date">Date</label>
                    <input type="text" name="date" id="date" placeholder="dd-mm-YYYY">

                    <label class="champ" for="poids">Poids en kilogramme</label>
                    <input type="text" name="poids" id="poids">

                    <input type="submit" class="input inputsubmit" name="validate" value="Insérer">

                </fieldset>

            </form>

            <?php if(isset($message)):
            echo "<h1 style='text-align:center;'>".$message."</h1>";
            endif;
            ?>
            
        
        <table class="tableau">
            <tr class="ligne">
                <th class="cellule_tete">Date</th>
                <th class="cellule_tete">Poids</th>
                
            </tr>
        
        <?php foreach($results as list($date, $poids)){
            
                        $format_us = implode('-',array_reverse  (explode('/',$date)));
        
                        echo '<tr class="ligne">
                        
                            
                            <td class="colonne">'.$format_us.'</td>
                            <td class="colonne">'.$poids.' kg</td>
                
                            </tr>';
        }
        ?>
        </table>

      
    
        
        <?php
            }else{
                echo 'Vous n\'êtes pas administrateur, veuillez contacter le webmaster svp';
            }
            include('includes/footer.php');
            ?>
    </body>
</html>