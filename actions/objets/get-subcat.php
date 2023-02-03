<!-- Fichier lié au script Jquery pour dérouler des sous catégories à partir des catégories-->
<?php
require('../db.php');

$category_id = $_POST["category_id"];

$result = $db->prepare("SELECT * FROM categories where parent_id = '$category_id'");
$result->execute();

?>
<option value="">Sélectionner une sous-catégorie</option>
<?php                           
while($row = $result->fetch(PDO::FETCH_BOTH)){
    ?><option value="<?php echo $row['category'];?>"><?php echo $row['category'];?></option>
    <?php
     }

?>