<?php

require_once __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

// Charger le .env
$dotenv = Dotenv::createImmutable(dirname(__DIR__));

$dotenv->load();

$serveur = $_ENV['DB_HOST'] ?? 'localhost';
$dbname  = $_ENV['DB_NAME'] ?? '';
$login   = $_ENV['DB_USER'] ?? '';
$pass    = $_ENV['DB_PASS'] ?? '';
$charset = $_ENV['DB_CHARSET'] ?? 'utf8';

try {
    $dsn = "mysql:host=$serveur;dbname=$dbname;charset=$charset";

    $db = new PDO($dsn, $login, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

} catch (Exception $e) {
    die('Une erreur a été trouvée : ' . $e->getMessage());
}
