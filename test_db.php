<?php
// 🔀 Mode sélectionné par URL
$mode = $_GET['mode'] ?? 'dev';

// 🎯 Définition des configurations
$configs = [
    'dev' => [
        'serveur' => 'localhost',
        'dbname' => 'objets',
        'login' => 'root',
        'pass' => '',
    ],
    'prod' => [
        'serveur' => 'sql01.ouvaton.coop',
        'dbname' => '09007_ressourceb',
        'login' => '09007_ressourceb',
        'pass' => 'LaRessourcerieDeBrie77170!',
    ],
    'migration' => [
        'serveur' => 'mysql-ressourcebrie.alwaysdata.net',
        'dbname' => 'ressourcebrie_bdd',
        'login' => '418153',
        'pass' => 'geMsos-wunxoc-1fucbu',
    ],
];

// Vérifier si le mode est valide
if (!isset($configs[$mode])) {
    die("<p style='color: red;'>❌ Mode inconnu : <strong>$mode</strong></p>");
}

// Récupérer la configuration
$conf = $configs[$mode];
extract($conf); // crée $serveur, $dbname, $login, $pass

echo "<h2>Test de connexion à la base ($mode)</h2>";

try {
    $db = new PDO("mysql:host=$serveur;dbname=$dbname;charset=utf8;", $login, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<p style='color: green;'>✅ Connexion réussie à <strong>$dbname</strong> sur <strong>$serveur</strong></p>";

    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($tables) {
        echo "<h3>Tables présentes :</h3><ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>⚠️ Aucune table trouvée dans cette base.</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erreur de connexion : " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
