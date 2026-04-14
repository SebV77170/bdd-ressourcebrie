<?php
require('../users/securityAction.php');
require('../db.php');
require('../webdav-client.php');
require dirname(__DIR__, 2) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->load();

if (!isset($_GET['uuid_ticket'])) {
    exit('uuid manquant');
}

$uuid_ticket = $_GET['uuid_ticket'];

// 🔽 récupère le chemin en base
$query = $db->prepare('SELECT lien FROM ticketdecaisse WHERE uuid_ticket = ?');
$query->execute([$uuid_ticket]);
$ticket = $query->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    exit('Ticket introuvable');
}

// 🔽 transforme en chemin WebDAV
function extractWebdavPath($path)
{
    $path = str_replace('\\', '/', $path);
    $pos = strpos($path, 'tickets/');
    if ($pos === false) {
        throw new Exception('Chemin invalide');
    }
    return substr($path, $pos);
}

$remotePath = extractWebdavPath($ticket['lien']);

// 🔽 téléchargement via ton client centralisé
$result = webdavDownloadFile($remotePath);

$filename = basename($remotePath);

// 🔽 renvoi navigateur
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Length: ' . strlen($result['body']));

echo $result['body'];
exit;