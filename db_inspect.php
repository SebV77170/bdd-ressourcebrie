<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test connexions BDD</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        form { display: inline-block; margin: 10px; }
        button {
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
        }
        .dev { background-color: #4CAF50; color: white; }
        .prod { background-color: #2196F3; color: white; }
        .migration { background-color: #FF9800; color: white; }
        iframe {
            width: 100%;
            height: 400px;
            border: 1px solid #ccc;
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <h1>Tester les connexions aux bases de données</h1>

    <form target="resultat" method="get" action="test_db.php">
        <input type="hidden" name="mode" value="dev">
        <button class="dev">Tester DEV</button>
    </form>

    <form target="resultat" method="get" action="test_db.php">
        <input type="hidden" name="mode" value="prod">
        <button class="prod">Tester PROD</button>
    </form>

    <form target="resultat" method="get" action="test_db.php">
        <input type="hidden" name="mode" value="migration">
        <button class="migration">Tester MIGRATION</button>
    </form>

    <iframe name="resultat"></iframe>

</body>
</html>
