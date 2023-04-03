 <?php require('actions/db.php');
 
            $ObjetDeTC = $db -> prepare('SELECT SUM(nbr) AS nbr_total FROM ticketdecaissetemp WHERE id_temp_vente = ?');;
            $ObjetDeTC -> execute(array($_GET['id_temp_vente']));
            $NbrObjetDeTC = $ObjetDeTC -> fetch();
            
            $NbrObjetDeTC=$NbrObjetDeTC['nbr_total'];
?>