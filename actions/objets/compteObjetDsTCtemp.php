 <?php require('actions/db.php');
 
            $ObjetDeTC = $db -> prepare('SELECT * FROM ticketdecaissetemp WHERE id_temp_vente = ?');
            $ObjetDeTC -> execute(array($_GET['id_temp_vente']));
            $TabObjetDeTC = $ObjetDeTC -> fetchAll(PDO::FETCH_COLUMN);
            $NbrObjetDeTC = count($TabObjetDeTC);
?>