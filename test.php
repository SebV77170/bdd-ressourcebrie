<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require __DIR__ . '/actions/webdav-client.php';

try {
    $result = webdavListRoot();

    echo '<h1>Connexion WebDAV OK</h1>';
    echo '<p>Code HTTP : ' . htmlspecialchars((string)$result['status']) . '</p>';
    echo '<pre>' . htmlspecialchars($result['xml']) . '</pre>';
} catch (Throwable $e) {
    echo '<h1>Erreur WebDAV</h1>';
    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
}