<?php require('actions/db.php');?>

<?php
    if(isset($_GET['poids'])){
        
        if (preg_match("/[\D]/", $_GET['poids'])){
    
            $message = 'Le poids rentré n\'est pas au bon format, mettez le poids en gramme svp.';
            
            $_GET['poids'] = NULL;
            
        }
    }
?>