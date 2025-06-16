<?php
$host = 'mysql-ressourcebrie.alwaysdata.net';
$dbname = 'ressourcebrie_bdd';
$user = '418153';
$pass = 'geMsos-wunxoc-1fucbu';
$port = 3306; // Port par défaut pour MySQL

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    echo "<p style='color:green;'>✅ Connexion réussie à la base de données MySQL distante.</p>";

    // Optionnel : test simple de requête
    $stmt = $pdo->query('SHOW TABLES');
    echo "<p>Tables disponibles :</p><ul>";
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "<li>{$row[0]}</li>";
    }
    echo "</ul>";

} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Erreur de connexion : " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
