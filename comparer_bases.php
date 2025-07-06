<?php
// Configuration des deux bases
$configs = [
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
    ]
];

// Connexion aux deux bases
function connect($conf) {
    try {
        $pdo = new PDO("mysql:host={$conf['serveur']};dbname={$conf['dbname']};charset=utf8;", $conf['login'], $conf['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Erreur de connexion à {$conf['dbname']} : " . $e->getMessage());
    }
}

$dbProd = connect($configs['prod']);
$dbMig = connect($configs['migration']);

// Fonction pour récupérer la structure des tables
function getTables(PDO $db) {
    $stmt = $db->query("SHOW TABLES");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getColumns(PDO $db, $table) {
    $stmt = $db->query("SHOW COLUMNS FROM `$table`");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Comparaison des noms de tables
$tablesProd = getTables($dbProd);
$tablesMig = getTables($dbMig);

echo "<h2>Comparaison des bases de données : PROD (Ouvaton) vs MIGRATION (AlwaysData)</h2>";

$diff = false;

// Tables manquantes ou supplémentaires
$missingInMig = array_diff($tablesProd, $tablesMig);
$extraInMig = array_diff($tablesMig, $tablesProd);

if ($missingInMig) {
    echo "<p style='color:red;'>❌ Tables manquantes dans MIGRATION :</p><ul>";
    foreach ($missingInMig as $table) echo "<li>$table</li>";
    echo "</ul>";
    $diff = true;
}

if ($extraInMig) {
    echo "<p style='color:orange;'>⚠️ Tables supplémentaires dans MIGRATION :</p><ul>";
    foreach ($extraInMig as $table) echo "<li>$table</li>";
    echo "</ul>";
    $diff = true;
}

// Comparaison des structures
$commonTables = array_intersect($tablesProd, $tablesMig);
foreach ($commonTables as $table) {
    $colsProd = getColumns($dbProd, $table);
    $colsMig = getColumns($dbMig, $table);

    $mapProd = [];
    foreach ($colsProd as $col) {
        $mapProd[$col['Field']] = $col;
    }

    $mapMig = [];
    foreach ($colsMig as $col) {
        $mapMig[$col['Field']] = $col;
    }

    $allColumns = array_unique(array_merge(array_keys($mapProd), array_keys($mapMig)));

    foreach ($allColumns as $colName) {
        if (!isset($mapProd[$colName])) {
            echo "<p style='color:orange;'>🟠 Colonne <strong>$colName</strong> présente dans <strong>MIGRATION.$table</strong> mais pas dans PROD</p>";
            $diff = true;
        } elseif (!isset($mapMig[$colName])) {
            echo "<p style='color:red;'>🔴 Colonne <strong>$colName</strong> manquante dans MIGRATION.$table</p>";
            $diff = true;
        } else {
            $colProd = $mapProd[$colName];
            $colMig = $mapMig[$colName];

            $attrs = ['Type', 'Null', 'Key', 'Default', 'Extra'];
            foreach ($attrs as $attr) {
                if ($colProd[$attr] != $colMig[$attr]) {
                    echo "<p style='color:red;'>❗ Différence sur <strong>$table.$colName</strong> [$attr] : PROD='{$colProd[$attr]}' ≠ MIGRATION='{$colMig[$attr]}'</p>";
                    $diff = true;
                }
            }
        }
    }
}

if (!$diff) {
    echo "<p style='color:green;'>✅ Les bases de données sont structurellement identiques.</p>";
}
?>
