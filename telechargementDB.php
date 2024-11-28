<?php
require('actions/users/securityAction.php');
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exporter Table en CSV</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <a href="actions/objets/export_table.php?type=OV" class="btn btn-primary">Exporter la Table Objets Vendus</a>
        <a href="actions/objets/export_table.php?type=OC" class="btn btn-primary">Exporter la Table Objets Collectés</a>
    </div>
</body>
</html>
